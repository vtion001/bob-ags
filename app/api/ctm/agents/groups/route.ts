import { NextRequest, NextResponse } from 'next/server'
import { AgentsService } from '@/lib/ctm/services/agents'

export async function GET(request: NextRequest) {
  try {
    const agentsService = new AgentsService()
    const groups = await agentsService.getUserGroups()

    return NextResponse.json({
      success: true,
      data: groups,
      user_groups: groups
    })
  } catch (error) {
    console.error('Error fetching CTM agent groups:', error)
    return NextResponse.json(
      { error: 'Failed to fetch agent groups from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
