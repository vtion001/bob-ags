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
    const userId = searchParams.get('userId')
    const dateFrom = searchParams.get('dateFrom')
    const dateTo = searchParams.get('dateTo')

    let query = supabase
      .from('qa_overrides')
      .select(`
        id,
        call_id,
        ctm_call_id,
        user_id,
        overrides,
        manual_score,
        ai_score,
        score_change,
        override_count,
        created_at,
        auth_user:user_id (email)
      `)
      .order('created_at', { ascending: false })
      .range(offset, offset + limit - 1)

    if (userId) {
      query = query.eq('user_id', userId)
    }

    if (dateFrom) {
      query = query.gte('created_at', dateFrom)
    }

    if (dateTo) {
      query = query.lte('created_at', dateTo)
    }

    const { data, error, count } = await query

    if (error) {
      console.error('Failed to fetch QA overrides:', error)
      return NextResponse.json({ error: 'Failed to fetch overrides' }, { status: 500 })
    }

    const formattedData = data?.map(override => ({
      ...override,
      override_user_email: (override as any).auth_user?.email || 'Unknown',
    })) || []

    return NextResponse.json({ 
      overrides: formattedData,
      total: count || formattedData.length,
      limit,
      offset
    })
  } catch (err) {
    console.error('QA overrides API error:', err)
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 })
  }
}