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
    const callId = searchParams.get('callId')

    if (!callId) {
      return NextResponse.json({ error: 'callId is required' }, { status: 400 })
    }

    // Get notes from Supabase
    const { data, error } = await supabase
      .from('call_notes')
      .select('*')
      .eq('call_id', callId)
      .order('created_at', { ascending: true })

    if (error) {
      console.error('Error fetching call notes:', error)
      return NextResponse.json({
        success: true,
        notes: []
      })
    }

    return NextResponse.json({
      success: true,
      notes: data || []
    })
  } catch (error) {
    console.error('Error fetching call notes:', error)
    return NextResponse.json({
      success: true,
      notes: []
    })
  }
}

export async function PATCH(request: NextRequest) {
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
    const { callId, notes } = body || {}

    if (!callId) {
      return NextResponse.json({ error: 'callId is required' }, { status: 400 })
    }

    // Update or insert notes in Supabase
    const { data, error } = await supabase
      .from('call_notes')
      .upsert({
        call_id: callId,
        notes: notes || '',
        updated_at: new Date().toISOString()
      })
      .select()
      .single()

    if (error) {
      console.error('Error updating call notes:', error)
      return NextResponse.json(
        { error: 'Failed to update notes' },
        { status: 500 }
      )
    }

    return NextResponse.json({
      success: true,
      notes: data
    })
  } catch (error) {
    console.error('Error updating call notes:', error)
    return NextResponse.json(
      { error: 'Failed to update notes' },
      { status: 500 }
    )
  }
}
