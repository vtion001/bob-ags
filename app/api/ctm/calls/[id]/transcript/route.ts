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
    const transcript = await ctmClient.getCallTranscript(id)

    return NextResponse.json({ transcript })
  } catch (error) {
    console.error('CTM transcript error:', error)
    return NextResponse.json(
      { error: 'Failed to fetch transcript from CTM' },
      { status: 500 }
    )
  }
}