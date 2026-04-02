import { NextRequest, NextResponse } from 'next/server'
import { AccountsService } from '@/lib/ctm/services/accounts'

export async function GET(request: NextRequest) {
  try {
    const accountsService = new AccountsService()
    const data = await accountsService.getAccounts()

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error fetching CTM accounts:', error)
    return NextResponse.json(
      { error: 'Failed to fetch accounts from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const accountsService = new AccountsService()
    const data = await accountsService.createAccount(body.name, body.timezoneHint)

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error creating CTM account:', error)
    return NextResponse.json(
      { error: 'Failed to create account in CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
