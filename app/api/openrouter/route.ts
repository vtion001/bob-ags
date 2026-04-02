import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

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

    // OpenRouter API requires external key - return mock response
    return NextResponse.json({
      success: true,
      message: 'OpenRouter analysis requires API key configuration',
      // In standalone mode, return empty analysis
      analysis: null
    })
  } catch (error) {
    console.error('OpenRouter error:', error)
    return NextResponse.json(
      { error: 'Failed to process OpenRouter request' },
      { status: 500 }
    )
  }
}
