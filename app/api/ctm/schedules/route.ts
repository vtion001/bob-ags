import { NextRequest, NextResponse } from 'next/server'
import { SchedulesService } from '@/lib/ctm/services/schedules'

export async function GET(request: NextRequest) {
  try {
    const schedulesService = new SchedulesService()
    const data = await schedulesService.getSchedules()

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error fetching CTM schedules:', error)
    return NextResponse.json(
      { error: 'Failed to fetch schedules from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const schedulesService = new SchedulesService()
    const data = await schedulesService.createSchedule(body)

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error creating CTM schedule:', error)
    return NextResponse.json(
      { error: 'Failed to create schedule in CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
