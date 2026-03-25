import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const limit = parseInt(searchParams.get('limit') || '100')
    const offset = parseInt(searchParams.get('offset') || '0')

    // Get total count (RLS doesn't affect aggregate queries the same way)
    const { count } = await supabase
      .from('calls')
      .select('*', { count: 'exact', head: true })
      .not('rubric_results', 'is', null)

    // Use SECURITY DEFINER function to bypass RLS and join agent_profiles
    const { data, error } = await supabase
      .rpc('get_analyzed_calls', { p_limit: limit, p_offset: offset })

    if (error) {
      console.error('Failed to fetch analyzed calls:', error)
      return NextResponse.json({ error: 'Failed to fetch calls' }, { status: 500 })
    }

    return NextResponse.json({ 
      calls: data || [],
      total: count || 0,
      limit,
      offset
    })
  } catch (err) {
    console.error('QA analysis API error:', err)
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 })
  }
}