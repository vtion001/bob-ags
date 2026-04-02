import { NextRequest, NextResponse } from 'next/server'
import { AgentsService } from '@/lib/ctm/services/agents'

export async function GET(request: NextRequest) {
  try {
    const agentsService = new AgentsService()
    const agents = await agentsService.getAgents()

    return NextResponse.json({
      success: true,
      data: agents,
      agents
    })
  } catch (error) {
    console.error('Error fetching CTM agents:', error)
    return NextResponse.json(
      { error: 'Failed to fetch agents from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
