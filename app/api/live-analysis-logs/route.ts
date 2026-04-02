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

    const searchParams = request.nextUrl.searchParams
    const limit = parseInt(searchParams.get('limit') || '50', 10)

    const { data: logs, error } = await supabase
      .from('live_analysis_logs')
      .select('*')
      .eq('user_id', user.id)
      .order('created_at', { ascending: false })
      .limit(limit)

    if (error) {
      console.error('Error fetching live analysis logs:', error)
      return NextResponse.json(
        { error: 'Failed to fetch live analysis logs' },
        { status: 500 }
      )
    }

    return NextResponse.json({
      success: true,
      data: logs || [],
      logs: logs || []
    })
  } catch (error) {
    console.error('Error fetching live analysis logs:', error)
    return NextResponse.json(
      { error: 'Failed to fetch live analysis logs' },
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
    const { call_id, call_phone, call_direction, call_timestamp, suggested_disposition, insights, transcript_preview } = body

    const { data: log, error } = await supabase
      .from('live_analysis_logs')
      .insert({
        user_id: user.id,
        call_id,
        call_phone,
        call_direction,
        call_timestamp,
        suggested_disposition,
        insights,
        transcript_preview
      })
      .select()
      .single()

    if (error) {
      console.error('Error creating live analysis log:', error)
      return NextResponse.json(
        { error: 'Failed to create live analysis log' },
        { status: 500 }
      )
    }

    return NextResponse.json({
      success: true,
      data: log
    })
  } catch (error) {
    console.error('Error creating live analysis log:', error)
    return NextResponse.json(
      { error: 'Failed to create live analysis log' },
      { status: 500 }
    )
  }
}

export async function DELETE(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()

    if (!user) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 }
      )
    }

    // Delete all logs for this user
    const { error } = await supabase
      .from('live_analysis_logs')
      .delete()
      .eq('user_id', user.id)

    if (error) {
      console.error('Error deleting live analysis logs:', error)
      return NextResponse.json(
        { error: 'Failed to delete live analysis logs' },
        { status: 500 }
      )
    }

    return NextResponse.json({
      success: true,
      message: 'All logs deleted successfully'
    })
  } catch (error) {
    console.error('Error deleting live analysis logs:', error)
    return NextResponse.json(
      { error: 'Failed to delete live analysis logs' },
      { status: 500 }
    )
  }
}
