import { NextRequest, NextResponse } from 'next/server'
import { NumbersService } from '@/lib/ctm/services/numbers'

export async function GET(request: NextRequest) {
  try {
    const numbersService = new NumbersService()
    const data = await numbersService.getNumbers()

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error fetching CTM numbers:', error)
    return NextResponse.json(
      { error: 'Failed to fetch numbers from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
