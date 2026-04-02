import { NextRequest, NextResponse } from 'next/server'
import { NumbersService } from '@/lib/ctm/services/numbers'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { phone_number, test = true } = body

    if (!phone_number) {
      return NextResponse.json(
        { error: 'phone_number is required' },
        { status: 400 }
      )
    }

    const numbersService = new NumbersService()
    const data = await numbersService.purchaseNumber(phone_number, test)

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error purchasing CTM number:', error)
    return NextResponse.json(
      { error: 'Failed to purchase number in CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
