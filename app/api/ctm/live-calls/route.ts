import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(request: NextRequest) {
  try {
    const callsService = new CallsService()
    const calls = await callsService.getRecentCalls(5)

    return NextResponse.json({
      success: true,
      calls
    })
  } catch (error) {
    console.error('Error fetching live calls:', error)
    return NextResponse.json(
      { error: 'Failed to fetch live calls from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
