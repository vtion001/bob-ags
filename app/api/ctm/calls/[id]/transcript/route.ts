import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const callsService = new CallsService()
    const transcript = await callsService.getCallTranscript(id)

    return NextResponse.json({
      success: true,
      transcript
    })
  } catch (error) {
    console.error('Error fetching call transcript:', error)
    return NextResponse.json(
      { error: 'Failed to fetch transcript from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
