import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()

    if (!user) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 }
      )
    }

    // Call analysis requires external AI service - return empty in standalone mode
    return NextResponse.json({
      success: true,
      calls: []
    })
  } catch (error) {
    console.error('Error in call analyze:', error)
    return NextResponse.json(
      { error: 'Failed to analyze calls' },
      { status: 500 }
    )
  }
}

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

    // Call analysis requires external AI service - return mock result
    return NextResponse.json({
      success: true,
      message: 'Analysis request received (requires OpenRouter API in standalone mode)',
      call_id: body.call_id
    })
  } catch (error) {
    console.error('Error in call analyze:', error)
    return NextResponse.json(
      { error: 'Failed to analyze call' },
      { status: 500 }
    )
  }
}
