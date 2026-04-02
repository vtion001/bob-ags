import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const hours = parseInt(searchParams.get('hours') || '24', 10)
    const sourceId = searchParams.get('source_id')

    const callsService = new CallsService()
    const calls = await callsService.getCalls({
      hours,
      limit: 500,
      sourceId: sourceId || undefined
    })

    // Calculate stats from calls
    const totalCalls = calls.length
    const analyzed = calls.filter(c => c.analysis).length
    const hotLeads = calls.filter(c => {
      if (!c.analysis) return false
      const score = c.analysis.score || 0
      return score >= 80
    }).length

    const scores = calls.filter(c => c.score).map(c => c.score!)
    const avgScore = scores.length > 0
      ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1)
      : '0.0'

    return NextResponse.json({
      success: true,
      stats: {
        totalCalls,
        analyzed,
        hotLeads,
        avgScore
      }
    })
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
    return NextResponse.json(
      { error: 'Failed to fetch dashboard stats from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
