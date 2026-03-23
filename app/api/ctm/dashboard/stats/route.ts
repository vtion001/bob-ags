import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'
import { getCachedCalls } from '@/lib/calls/cache'
import { transformDBRowToAPIResponse } from '@/lib/calls/transformer'

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const cacheOnly = searchParams.get('cacheOnly') === 'true'
    const hours = parseInt(searchParams.get('hours') || '168')
    const agentId = searchParams.get('agentId')

    try {
      const cached = await getCachedCalls(supabase, {
        userId: user.id,
        hours,
        agentId: agentId || undefined,
        limit: 500,
        ctmCallId: undefined,
      })

      if (cached && cached.calls.length > 0) {
        const inboundCalls = cached.calls.filter((c: any) => c.direction === 'inbound')
        const totalCalls = inboundCalls.length
        const analyzed = inboundCalls.filter((c: any) => c.score !== undefined || c.analysis).length
        const hotLeads = inboundCalls.filter((c: any) => (c.score ?? 0) >= 80).length
        const scoredCalls = inboundCalls.filter((c: any) => c.score && c.score > 0)
        const avgScore = scoredCalls.length > 0
          ? Math.round(scoredCalls.reduce((sum: number, c: any) => sum + (c.score ?? 0), 0) / scoredCalls.length)
          : 0

        return NextResponse.json({
          stats: { totalCalls, analyzed, hotLeads, avgScore: avgScore.toString() },
          recentCalls: inboundCalls.slice(0, 10),
          fromCache: true,
        })
      }
    } catch {
      // Cache unavailable, fall through to CTM
    }

    if (cacheOnly) {
      return NextResponse.json({
        stats: { totalCalls: 0, analyzed: 0, hotLeads: 0, avgScore: '0' },
        recentCalls: [],
        fromCache: true,
      })
    }

    const ctmClient = new CTMClient()
    const calls = await ctmClient.calls.getCalls({ limit: 500, hours, agentId: agentId || undefined })
    
    const inboundCalls = calls.filter(c => c.direction === 'inbound')
    const totalCalls = inboundCalls.length
    const analyzed = inboundCalls.filter(c => c.score !== undefined || c.analysis).length
    const hotLeads = inboundCalls.filter(c => (c.score ?? 0) >= 80).length
    const avgScore = totalCalls > 0
      ? Math.round(inboundCalls.reduce((sum, c) => sum + (c.score ?? 0), 0) / totalCalls)
      : 0

    return NextResponse.json({
      stats: { totalCalls, analyzed, hotLeads, avgScore: avgScore.toString() },
      recentCalls: inboundCalls.slice(0, 10),
      fromCache: false,
    })
  } catch (error) {
    console.error('CTM dashboard stats error:', error)
    return NextResponse.json(
      { error: 'Failed to fetch dashboard stats from CTM' },
      { status: 500 }
    )
  }
}
