import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const minutes = parseInt(searchParams.get('minutes') || '5', 10)

    const callsService = new CallsService()
    const calls = await callsService.getRecentCalls(minutes)

    return NextResponse.json({
      success: true,
      calls
    })
  } catch (error) {
    console.error('Error fetching active calls:', error)
    return NextResponse.json(
      { error: 'Failed to fetch active calls from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
