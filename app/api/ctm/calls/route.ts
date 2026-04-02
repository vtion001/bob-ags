import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const limit = parseInt(searchParams.get('limit') || '100', 10)
    const hours = parseInt(searchParams.get('hours') || '24', 10)
    const status = searchParams.get('status')
    const sourceId = searchParams.get('source_id')
    const agentId = searchParams.get('agent_id')

    const callsService = new CallsService()
    const calls = await callsService.getCalls({
      limit,
      hours,
      status: status || undefined,
      sourceId: sourceId || undefined,
      agentId: agentId || undefined
    })

    return NextResponse.json({
      success: true,
      calls
    })
  } catch (error) {
    console.error('Error fetching CTM calls:', error)
    return NextResponse.json(
      { error: 'Failed to fetch calls from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
