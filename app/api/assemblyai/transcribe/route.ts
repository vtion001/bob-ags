import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

const DEV_BYPASS_UID = '00000000-0000-0000-0000-000000000001'

function isDevUser(request: NextRequest): boolean {
  const devSessionCookie = request.cookies.get('sb-dev-session')
  if (!devSessionCookie) return false
  try {
    const devSession = JSON.parse(devSessionCookie.value)
    if (devSession.dev && devSession.user?.id === DEV_BYPASS_UID) {
      return true
    }
  } catch {}
  return false
}

export async function POST(request: NextRequest) {
  try {
    if (!isDevUser(request)) {
      const supabase = await createServerSupabase(request)
      const { data: { user } } = await supabase.auth.getUser()
      if (!user) {
        return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
      }
    }

    const body = await request.json()
    const { audioUrl, callId } = body

    if (!audioUrl) {
      return NextResponse.json({ error: 'audioUrl is required' }, { status: 400 })
    }

    const apiKey = process.env.ASSEMBLYAI_API_KEY || process.env.NEXT_PUBLIC_ASSEMBLYAI_API_KEY

    if (!apiKey) {
      return NextResponse.json({ error: 'AssemblyAI API key not configured' }, { status: 500 })
    }

    // Submit the audio for transcription
    const submitResponse = await fetch('https://api.assemblyai.com/v2/transcript', {
      method: 'POST',
      headers: {
        'Authorization': apiKey,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        audio_url: audioUrl,
        speech_models: ['universal-2'],
        punctuate: true,
        format_text: true,
      }),
    })

    if (!submitResponse.ok) {
      const errorText = await submitResponse.text()
      console.error('AssemblyAI submit error:', errorText)
      return NextResponse.json(
        { error: `AssemblyAI transcription failed: ${errorText}` },
        { status: 502 }
      )
    }

    const submitData = await submitResponse.json()
    const transcriptId = submitData.id

    // Poll for completion
    const maxAttempts = 60
    let attempts = 0

    while (attempts < maxAttempts) {
      await new Promise(resolve => setTimeout(resolve, 5000))

      const statusResponse = await fetch(`https://api.assemblyai.com/v2/transcript/${transcriptId}`, {
        headers: {
          'Authorization': apiKey,
        },
      })

      if (!statusResponse.ok) {
        console.error('AssemblyAI status error:', await statusResponse.text())
        attempts++
        continue
      }

      const statusData = await statusResponse.json()

      if (statusData.status === 'completed') {
        return NextResponse.json({
          success: true,
          transcript: statusData.text || '',
          id: transcriptId,
        })
      } else if (statusData.status === 'error') {
        return NextResponse.json({
          error: `Transcription error: ${statusData.error}`,
        }, { status: 502 })
      }

      attempts++
    }

    return NextResponse.json({
      error: 'Transcription timed out',
    }, { status: 504 })

  } catch (error) {
    console.error('AssemblyAI transcription error:', error)
    return NextResponse.json(
      { error: 'Failed to transcribe audio' },
      { status: 500 }
    )
  }
}