import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const callsService = new CallsService()
    const call = await callsService.getCall(id)

    if (!call) {
      return NextResponse.json(
        { error: 'Call not found' },
        { status: 404 }
      )
    }

    return NextResponse.json({
      success: true,
      call
    })
  } catch (error) {
    console.error('Error fetching CTM call:', error)
    return NextResponse.json(
      { error: 'Failed to fetch call from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
