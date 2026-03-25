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

    // Query calls with rubric_results (analyzed calls)
    const { data, error, count } = await supabase
      .from('calls')
      .select('*', { count: 'exact' })
      .not('rubric_results', 'is', null)
      .order('created_at', { ascending: false })
      .range(offset, offset + limit - 1)

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