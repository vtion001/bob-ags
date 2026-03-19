import { NextRequest, NextResponse } from 'next/server'
import { getSession } from '@/lib/auth'
import { CTMClient } from '@/lib/ctm'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await getSession()
    if (!session) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const { id } = await params
    const ctmClient = new CTMClient()
    const call = await ctmClient.getCall(id)

    if (!call) {
      return NextResponse.json({ error: 'Call not found' }, { status: 404 })
    }

    return NextResponse.json({ call })
  } catch (error) {
    console.error('CTM call error:', error)
    return NextResponse.json(
      { error: 'Failed to fetch call from CTM' },
      { status: 500 }
    )
  }
}