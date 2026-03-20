import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'

const SYNC_INTERVAL_MS = 5 * 60 * 1000 // 5 minutes

async function fetchFromCTM(hours: number, agentId?: string | null, limit: number = 500) {
  const ctmClient = new CTMClient()
  const calls = await ctmClient.calls.getCalls({ limit, hours, agentId: agentId || undefined })
  const inbound = calls.filter(c => c.direction === 'inbound')
  return inbound.map((c: any) => ({
    id: c.id,
    phone: c.phone,
    direction: c.direction,
    duration: c.duration,
    status: c.status,
    timestamp: c.timestamp,
    callerNumber: c.callerNumber,
    trackingNumber: c.trackingNumber,
    trackingLabel: c.trackingLabel,
    source: c.source,
    sourceId: c.sourceId,
    agentId: c.agent?.id,
    agentName: c.agent?.name,
    recordingUrl: c.recordingUrl,
    transcript: c.transcript,
    city: c.city,
    state: c.state,
    postalCode: c.postalCode,
    notes: c.notes,
    talkTime: c.talkTime,
    waitTime: c.waitTime,
    ringTime: c.ringTime,
    score: c.score,
    sentiment: (c as any).sentiment,
    summary: (c as any).summary,
    tags: (c as any).tags,
    disposition: (c as any).disposition,
    syncedAt: (c as any).syncedAt,
    rubricResults: (c as any).rubricResults,
    rubricBreakdown: (c as any).rubricBreakdown,
  }))
}

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const hours = parseInt(searchParams.get('hours') || '2160') // Default 3 months
    const agentId = searchParams.get('agentId')
    const limit = parseInt(searchParams.get('limit') || '500')
    const skipSync = searchParams.get('skipSync') === 'true'
    const ctmCallId = searchParams.get('ctmCallId')

    // Try Supabase cache first
    let cachedData: any[] = []
    let cacheSource = false
    try {
      const since = new Date(Date.now() - hours * 60 * 60 * 1000).toISOString()
      let query = supabase
        .from('calls')
        .select('id, ctm_call_id, phone, direction, duration, status, timestamp, caller_number, tracking_number, tracking_label, source, source_id, agent_id, agent_name, recording_url, transcript, city, state, postal_code, notes, talk_time, wait_time, ring_time, score, sentiment, summary, tags, disposition, synced_at, rubric_results, rubric_breakdown')
        .eq('user_id', user.id)
        .gte('timestamp', since)
        .order('timestamp', { ascending: false })
        .limit(limit)

      if (agentId) query = query.eq('agent_id', agentId)
      if (ctmCallId) query = query.eq('ctm_call_id', ctmCallId)

      const { data, error } = await query
      if (!error && data && data.length > 0) {
        cachedData = data
        cacheSource = true
      }
    } catch (cacheErr) {
      console.warn('[calls] Supabase cache unavailable, falling back to CTM:', cacheErr)
    }

    // If we have cached data, return it
    if (cachedData.length > 0) {
      const transformedCalls = cachedData.map(call => ({
        id: call.ctm_call_id,
        phone: call.phone,
        direction: call.direction,
        duration: call.duration,
        status: call.status,
        timestamp: call.timestamp,
        callerNumber: call.caller_number,
        trackingNumber: call.tracking_number,
        trackingLabel: call.tracking_label,
        source: call.source,
        sourceId: call.source_id,
        agentId: call.agent_id,
        agentName: call.agent_name,
        recordingUrl: call.recording_url,
        transcript: call.transcript,
        city: call.city,
        state: call.state,
        postalCode: call.postal_code,
        notes: call.notes,
        talkTime: call.talk_time,
        waitTime: call.wait_time,
        ringTime: call.ring_time,
        score: call.score,
        sentiment: call.sentiment,
        summary: call.summary,
        tags: call.tags,
        disposition: call.disposition,
        syncedAt: call.synced_at,
        rubricResults: call.rubric_results,
        rubricBreakdown: call.rubric_breakdown,
      }))

      const mostRecentSync = cachedData[0]?.synced_at
      const cacheAge = mostRecentSync ? Date.now() - new Date(mostRecentSync).getTime() : Infinity
      const isCacheStale = cacheAge > SYNC_INTERVAL_MS

      const response: any = {
        calls: transformedCalls,
        source: 'cache',
        count: transformedCalls.length,
        cacheAgeMs: cacheAge,
      }
      if (!skipSync && isCacheStale) response.needsSync = true
      return NextResponse.json(response)
    }

    // Cache miss or empty — fetch directly from CTM
    const calls = await fetchFromCTM(hours, agentId, limit)
    return NextResponse.json({ calls, source: 'ctm', count: calls.length })
  } catch (error) {
    console.error('Calls fetch error:', error)
    return NextResponse.json({ error: 'Failed to fetch calls' }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const hours = parseInt(searchParams.get('hours') || '2160')
    const agentId = searchParams.get('agentId')

    // Find the most recent call timestamp in cache
    let lastCallTimestamp: number | null = null
    try {
      const { data: latestCall } = await supabase
        .from('calls')
        .select('timestamp')
        .eq('user_id', user.id)
        .order('timestamp', { ascending: false })
        .limit(1)
      if (latestCall && latestCall.length > 0 && latestCall[0]?.timestamp) {
        lastCallTimestamp = new Date(latestCall[0].timestamp).getTime()
      }
    } catch { /* cache not ready */ }

    const fetchHours = lastCallTimestamp ? 24 : hours
    const ctmClient = new CTMClient()
    const calls = await ctmClient.calls.getCalls({ limit: 500, hours: fetchHours, agentId: agentId || undefined })
    const inboundCalls = calls.filter(c => c.direction === 'inbound')

    let newCalls = inboundCalls
    if (lastCallTimestamp) {
      newCalls = inboundCalls.filter(c => new Date(c.timestamp).getTime() > lastCallTimestamp)
    }

    if (newCalls.length === 0) {
      return NextResponse.json({ calls: [], source: 'ctm', count: 0, isIncremental: true, message: 'No new calls' })
    }

    const callsToUpsert = newCalls.map(call => ({
      ctm_call_id: call.id,
      user_id: user.id,
      phone: call.phone,
      direction: call.direction,
      duration: call.duration,
      status: call.status,
      timestamp: call.timestamp,
      caller_number: call.callerNumber,
      tracking_number: call.trackingNumber,
      tracking_label: call.trackingLabel,
      source: call.source,
      source_id: call.sourceId,
      agent_id: call.agent?.id,
      agent_name: call.agent?.name,
      recording_url: call.recordingUrl,
      transcript: call.transcript,
      city: call.city,
      state: call.state,
      postal_code: call.postalCode,
      notes: call.notes,
      talk_time: call.talkTime,
      wait_time: call.waitTime,
      ring_time: call.ringTime,
      score: call.score,
      sentiment: call.analysis?.sentiment,
      summary: call.analysis?.summary,
      tags: call.analysis?.tags,
      disposition: call.analysis?.disposition,
      synced_at: new Date().toISOString(),
    }))

    // Try to write to Supabase, but don't fail if table doesn't exist
    try {
      if (callsToUpsert.length > 0) {
        const { error: upsertError } = await supabase
          .from('calls')
          .upsert(callsToUpsert, { onConflict: 'ctm_call_id' })
        if (upsertError) console.warn('[calls] Supabase write failed:', upsertError)
      }
      await supabase.from('calls_sync_log').insert({
        user_id: user.id,
        last_sync_at: new Date().toISOString(),
        calls_synced: callsToUpsert.length,
        status: 'completed',
      })
    } catch (e) {
      console.warn('[calls] Supabase sync write skipped (table may not exist):', e)
    }

    return NextResponse.json({ calls: callsToUpsert, source: 'ctm', count: callsToUpsert.length, isIncremental: true })
  } catch (error) {
    console.error('Calls sync error:', error)
    return NextResponse.json({ error: 'Failed to sync calls' }, { status: 500 })
  }
}