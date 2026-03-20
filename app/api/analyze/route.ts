import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { analyzeTranscript } from '@/lib/ai'

export async function POST(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 }
      )
    }

    const body = await request.json()
    const { transcription, phone, client } = body

    if (!transcription) {
      return NextResponse.json(
        { error: 'Transcription is required' },
        { status: 400 }
      )
    }

    const analysis = await analyzeTranscript(transcription, phone, client)

    return NextResponse.json({
      success: true,
      analysis,
    })
  } catch (error) {
    console.error('Analysis error:', error)
    return NextResponse.json(
      { error: 'An error occurred during analysis' },
      { status: 500 }
    )
  }
}
