import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

export async function PATCH(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const body = await request.json().catch(() => null)
    const { callId, notes } = body || {}

    if (!callId) {
      return NextResponse.json({ error: 'callId is required' }, { status: 400 })
    }

    const { data, error } = await supabase
      .from('calls')
      .update({ notes })
      .eq('ctm_call_id', callId)
      .select()
      .single()

    if (error) {
      console.error('Failed to update notes:', error)
      return NextResponse.json({ error: 'Failed to update notes' }, { status: 500 })
    }

    return NextResponse.json({ success: true, notes: data.notes })
  } catch (error) {
    console.error('Notes update error:', error)
    return NextResponse.json({ error: 'Failed to update notes' }, { status: 500 })
  }
}
