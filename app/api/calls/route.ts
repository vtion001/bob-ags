import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'
import { createServerSupabase } from '@/lib/supabase/server'

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const limit = parseInt(searchParams.get('limit') || '100', 10)
    const hours = parseInt(searchParams.get('hours') || '24', 10)
    const status = searchParams.get('status')
    const sourceId = searchParams.get('source_id')
    const agentId = searchParams.get('agent_id')

    // Fetch registered agent IDs from agent_profiles table
    const { supabase, response } = await createServerSupabase(request)
    const { data: agentProfiles } = await supabase
      .from('agent_profiles')
      .select('agent_id')

    const registeredAgentIds = new Set(agentProfiles?.map(ap => ap.agent_id) || [])

    const callsService = new CallsService()
    const calls = await callsService.getCalls({
      limit,
      hours,
      status: status || undefined,
      sourceId: sourceId || undefined,
      agentId: agentId || undefined
    })

    // Filter calls to only include those from registered agents
    const filteredCalls = calls.filter(call => {
      // If no registered agents, return all calls (backward compatibility)
      if (registeredAgentIds.size === 0) return true
      // If agentId filter is specified, still apply it (more specific filter)
      if (agentId) return call.agent?.id === agentId
      // Otherwise, only show calls from registered agents
      return call.agent?.id && registeredAgentIds.has(call.agent.id)
    })

    // Create response with cookies from Supabase auth
    const jsonResponse = NextResponse.json({
      success: true,
      calls: filteredCalls
    })

    // Copy cookies from Supabase response to our response
    response.cookies.getAll().forEach((cookie) => {
      jsonResponse.cookies.set(cookie.name, cookie.value)
    })

    return jsonResponse
  } catch (error) {
    console.error('Error fetching calls:', error)
    return NextResponse.json(
      { error: 'Failed to fetch calls from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
