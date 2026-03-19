import { NextRequest, NextResponse } from 'next/server'
import { getSession } from '@/lib/auth'
import { CTMClient } from '@/lib/ctm'
import { analyzeTranscript } from '@/lib/ai'

async function transcribeAudio(call: { recordingUrl?: string; id: string }): Promise<string> {
  const openrouterKey = process.env.OPENROUTER_API_KEY
  if (!openrouterKey || openrouterKey === 'your-openrouter-api-key-here') {
    throw new Error('OpenRouter API key not configured')
  }

  if (!call.recordingUrl) {
    throw new Error('No recording URL available')
  }

  const audioResponse = await fetch(call.recordingUrl, {
    headers: {
      'Authorization': `Basic ${Buffer.from(process.env.CTM_ACCESS_KEY + ':' + process.env.CTM_SECRET_KEY).toString('base64')}`,
    },
  })

  if (!audioResponse.ok) {
    throw new Error('Failed to download audio recording')
  }

  const audioBuffer = await audioResponse.arrayBuffer()
  const base64Audio = Buffer.from(audioBuffer).toString('base64')
  const mimeType = audioResponse.headers.get('content-type') || 'audio/mpeg'

  const transcriptResponse = await fetch('https://openrouter.ai/api/v1/audio/transcriptions', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${openrouterKey}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      model: 'openai/whisper-large-v3',
      audio: `data:${mimeType};base64,${base64Audio}`,
      response_format: 'json',
    }),
  })

  if (!transcriptResponse.ok) {
    const errorText = await transcriptResponse.text()
    throw new Error(`Transcription failed: ${errorText}`)
  }

  const result = await transcriptResponse.json()
  return result.text || ''
}

export async function POST(request: NextRequest) {
  try {
    const session = await getSession()
    if (!session) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const { callIds } = await request.json()

    if (!callIds || !Array.isArray(callIds) || callIds.length === 0) {
      return NextResponse.json(
        { error: 'callIds array is required' },
        { status: 400 }
      )
    }

    const ctmClient = new CTMClient()
    const results = []

    for (const callId of callIds) {
      try {
        const call = await ctmClient.getCall(callId)
        
        if (!call) {
          results.push({ callId, success: false, error: 'Call not found' })
          continue
        }

        let transcript = call.transcript
        
        if (!transcript && call.recordingUrl) {
          try {
            transcript = await transcribeAudio(call)
          } catch (transcribeErr) {
            console.error(`Transcription error for call ${callId}:`, transcribeErr)
            results.push({ 
              callId, 
              success: false, 
              error: transcribeErr instanceof Error ? transcribeErr.message : 'Transcription failed' 
            })
            continue
          }
        }
        
        if (!transcript) {
          const transcriptData = await ctmClient.getCallTranscript(callId)
          transcript = transcriptData || undefined
        }

        if (!transcript) {
          results.push({ callId, success: false, error: 'No transcript available' })
          continue
        }

        const analysis = await analyzeTranscript(transcript, call.phone)
        
        results.push({
          callId,
          success: true,
          analysis: {
            score: analysis.qualification_score,
            sentiment: analysis.sentiment,
            summary: analysis.summary,
            tags: analysis.tags,
            disposition: analysis.suggested_disposition,
            followUp: analysis.follow_up_required,
          },
        })
      } catch (err) {
        console.error(`Error analyzing call ${callId}:`, err)
        results.push({ callId, success: false, error: 'Analysis failed' })
      }
    }

    return NextResponse.json({
      success: true,
      results,
      analyzed: results.filter(r => r.success).length,
    })
  } catch (error) {
    console.error('Bulk analysis error:', error)
    return NextResponse.json(
      { error: 'Failed to analyze calls' },
      { status: 500 }
    )
  }
}