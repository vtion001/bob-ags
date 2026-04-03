import { NextRequest, NextResponse } from 'next/server'
import { CallsService } from '@/lib/ctm/services/calls'
import { createServerSupabase } from '@/lib/supabase/server'
import { createCallsService } from '@/lib/ctm/services/calls'

export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams
    const limit = parseInt(searchParams.get('limit') || '100', 10)
    const hours = parseInt(searchParams.get('hours') || '24', 10)
    const status = searchParams.get('status')
    const sourceId = searchParams.get('source_id')
    const agentId = searchParams.get('agent_id')
    const agentProfileId = searchParams.get('agentProfileId') // Optional: specific agent profile to filter by

    // Fetch registered agent profiles with their CTM agent IDs
    const { supabase, response } = await createServerSupabase(request)
    const { data: agentProfiles } = await supabase
      .from('agent_profiles')
      .select('id, agent_id, name')

    // Build a map of CTM agent IDs to profile info
    const ctmAgentIdToProfile = new Map<string, { id: string; agent_id: string; name: string }>()
    for (const profile of agentProfiles || []) {
      if (profile.agent_id) {
        ctmAgentIdToProfile.set(profile.agent_id, profile)
        // Also normalize for comparison (remove non-digits)
        const normalizedId = profile.agent_id.replace(/\D/g, '')
        if (normalizedId !== profile.agent_id) {
          ctmAgentIdToProfile.set(normalizedId, profile)
        }
      }
    }

    const callsService = createCallsService()
    const calls = await callsService.getCalls({
      limit,
      hours,
      status: status || undefined,
      sourceId: sourceId || undefined,
      agentId: agentId || undefined
    })

    // Filter calls to only include those from registered agents
    // Use phone-based matching since agent IDs may be in different formats (UUID vs numeric)
    const filteredCalls = calls.filter(call => {
      // If no registered agents, return all calls
      if (ctmAgentIdToProfile.size === 0) return true

      // Match by agent ID (try both exact and normalized comparison)
      const callAgentId = call.agent?.id
      if (callAgentId) {
        // Check exact match
        if (ctmAgentIdToProfile.has(callAgentId)) return true
        // Check normalized match (digits only)
        const normalizedCallAgentId = callAgentId.replace(/\D/g, '')
        if (normalizedCallAgentId && ctmAgentIdToProfile.has(normalizedCallAgentId)) return true
      }

      // Also check by agent phone number if available
      if (call.agent?.phone && ctmAgentIdToProfile.size > 0) {
        // Check if any registered agent has this phone
        for (const [, profile] of ctmAgentIdToProfile) {
          // We can't directly match phone here since profiles don't store phone
          // But if we have agentProfiles with phone info, we could use it
        }
      }

      return false
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
