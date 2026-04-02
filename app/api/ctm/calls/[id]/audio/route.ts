import { NextRequest, NextResponse } from 'next/server'
import { CTMClient } from '@/lib/ctm/client'

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params
    const ctmClient = new CTMClient()
    const accountId = ctmClient.getAccountId()

    // Fetch the recording audio URL from CTM
    const data = await ctmClient.makeRequest<{ recording_url?: string }>(
      `/accounts/${accountId}/calls/${id}/recording`
    )

    if (!data.recording_url) {
      return NextResponse.json(
        { error: 'Recording not found' },
        { status: 404 }
      )
    }

    return NextResponse.json({
      success: true,
      recording_url: data.recording_url
    })
  } catch (error) {
    console.error('Error fetching call audio:', error)
    return NextResponse.json(
      { error: 'Failed to fetch call audio from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
