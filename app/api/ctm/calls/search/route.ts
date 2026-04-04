import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const phone = searchParams.get('phone')
    const hours = parseInt(searchParams.get('hours') || '8760', 10)
    const direction = searchParams.get('direction') as 'inbound' | 'outbound' | null

    if (!phone) {
      return NextResponse.json(
        { error: 'phone parameter is required' },
        { status: 400 }
      )
    }

    const callsService = new CallsService()
    // Phone search has no limit - fetch all matching calls
    const calls = await callsService.searchCallsByPhone(phone, hours, undefined, direction || undefined)

    return NextResponse.json({
      success: true,
      calls
    })
  } catch (error) {
    console.error('Error searching CTM calls:', error)
    return NextResponse.json(
      { error: 'Failed to search calls in CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
