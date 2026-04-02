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

    // AssemblyAI token requires external API - return empty in standalone mode
    return NextResponse.json({
      success: true,
      token: ''
    })
  } catch (error) {
    console.error('AssemblyAI token error:', error)
    return NextResponse.json(
      { error: 'Failed to get AssemblyAI token' },
      { status: 500 }
    )
  }
}
