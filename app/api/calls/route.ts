import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'

const SYNC_INTERVAL_MS = 5 * 60 * 1000 // 5 minutes

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const hours = parseInt(searchParams.get('hours') || '2160') // Default 3 months (2160 hours)
    const agentId = searchParams.get('agentId')
    const limit = parseInt(searchParams.get('limit') || '500')
    const skipSync = searchParams.get('skipSync') === 'true'

    // Get cached calls from Supabase - optimized query with filters
    const since = new Date(Date.now() - hours * 60 * 60 * 1000).toISOString()
    
    let query = supabase
      .from('calls')
      .select('id, ctm_call_id, phone, direction, duration, status, timestamp, caller_number, tracking_number, tracking_label, source, source_id, agent_id, agent_name, recording_url, transcript, city, state, postal_code, notes, talk_time, wait_time, ring_time, score, sentiment, summary, tags, disposition, synced_at')
      .eq('user_id', user.id)
      .gte('timestamp', since)
      .order('timestamp', { ascending: false })
      .limit(limit)

    if (agentId) {
      query = query.eq('agent_id', agentId)
    }

    const { data: cachedCalls, error: cacheError } = await query

    if (cacheError) {
      console.error('Cache read error:', cacheError)
    }

    const cachedData = cachedCalls || []
    
    // Transform cached data to use ctm_call_id as id for CTM API compatibility
    const transformedCalls = cachedData.map(call => ({
      id: call.ctm_call_id, // Use CTM call ID as id for frontend compatibility
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
    }))
    
    // Check if cache is stale (older than 5 minutes)
    const mostRecentSync = cachedData.length > 0 ? cachedData[0].synced_at : null
    const cacheAge = mostRecentSync ? Date.now() - new Date(mostRecentSync).getTime() : Infinity
    const isCacheStale = cacheAge > SYNC_INTERVAL_MS

    // Return cached data immediately
    const response: any = { 
      calls: transformedCalls,
      source: 'cache',
      count: transformedCalls.length,
      cacheAgeMs: cacheAge,
    }

    // If cache is empty or stale and we haven't been told to skip sync, trigger background sync
    // Empty cache = always sync; Stale cache with data = sync too
    if (!skipSync && (cachedData.length === 0 || isCacheStale)) {
      response.needsSync = true
    }

    return NextResponse.json(response)
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
    const hours = parseInt(searchParams.get('hours') || '2160') // Default 3 months (2160 hours)
    const agentId = searchParams.get('agentId')

    // Find the most recent call timestamp in cache
    const { data: latestCall } = await supabase
      .from('calls')
      .select('timestamp')
      .eq('user_id', user.id)
      .order('timestamp', { ascending: false })
      .limit(1)

    const lastCallTimestamp = latestCall && latestCall.length > 0 && latestCall[0]?.timestamp 
      ? new Date(latestCall[0].timestamp).getTime() 
      : null

    // Fetch calls from CTM - use 24 hours for regular sync to get recent calls
    // If no cached calls, fetch full 3 months
    const fetchHours = lastCallTimestamp ? 24 : hours
    
    const ctmClient = new CTMClient()
    const calls = await ctmClient.calls.getCalls({ 
      limit: 500, 
      hours: fetchHours,
      agentId: agentId || undefined
    })

    const inboundCalls = calls.filter(c => c.direction === 'inbound')

    // Filter to only include calls newer than our latest cached call
    let newCalls = inboundCalls
    if (lastCallTimestamp) {
      newCalls = inboundCalls.filter(c => {
        const callTime = new Date(c.timestamp).getTime()
        return callTime > lastCallTimestamp
      })
    }

    // If no new calls, return early
    if (newCalls.length === 0) {
      return NextResponse.json({ 
        calls: [],
        source: 'ctm',
        count: 0,
        isIncremental: true,
        message: 'No new calls'
      })
    }

    // Store new calls in Supabase
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

    // Upsert calls (insert or update if exists)
    if (callsToUpsert.length > 0) {
      const { error: upsertError } = await supabase
        .from('calls')
        .upsert(callsToUpsert, { onConflict: 'ctm_call_id' })

      if (upsertError) {
        console.error('Cache write error:', upsertError)
      }
    }

    // Log sync
    await supabase
      .from('calls_sync_log')
      .insert({
        user_id: user.id,
        last_sync_at: new Date().toISOString(),
        calls_synced: callsToUpsert.length,
        status: 'completed',
      })

    return NextResponse.json({ 
      calls: callsToUpsert,
      source: 'ctm',
      count: callsToUpsert.length,
      isIncremental: true
    })
  } catch (error) {
    console.error('Calls sync error:', error)
    return NextResponse.json({ error: 'Failed to sync calls' }, { status: 500 })
  }
}