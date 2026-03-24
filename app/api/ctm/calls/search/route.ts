import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'
import { transformCTMCallToAPIResponse } from '@/lib/calls/transformer'

function transformCallToAPIResponse(c: any) {
  return {
    id: c.ctm_call_id || c.id,
    phone: c.phone,
    direction: c.direction,
    duration: c.duration,
    status: c.status,
    timestamp: c.timestamp,
    callerNumber: c.caller_number,
    trackingNumber: c.tracking_number,
    trackingLabel: c.tracking_label,
    source: c.source,
    sourceId: c.source_id,
    agentId: c.agent_id,
    agentName: c.agent_name,
    recordingUrl: c.recording_url,
    transcript: c.transcript,
    city: c.city,
    state: c.state,
    postalCode: c.postal_code,
    notes: c.notes,
    talkTime: c.talk_time,
    waitTime: c.wait_time,
    ringTime: c.ring_time,
    score: c.score,
    sentiment: c.sentiment,
    summary: c.summary,
    tags: c.tags,
    disposition: c.disposition,
    syncedAt: c.synced_at,
    analysis: {
      score: c.score,
      sentiment: c.sentiment,
      summary: c.summary,
      tags: c.tags,
      disposition: c.disposition,
      rubric_results: c.rubric_results,
      rubric_breakdown: c.rubric_breakdown,
    },
  }
}

function filterCallsByPhone(calls: any[], normalizedPhone: string) {
  return calls.filter(call => {
    const phoneFields = [
      call.phone,
      call.caller_number,
      call.callerNumber,
      call.tracking_number,
      call.trackingNumber,
    ]
    return phoneFields.some(field => {
      if (!field) return false
      const normalizedField = String(field).replace(/\D/g, '')
      if (normalizedField.length >= 10 && normalizedPhone.length >= 10) {
        return normalizedField.slice(-10) === normalizedPhone.slice(-10)
      }
      return normalizedField.includes(normalizedPhone)
    })
  })
}

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const phone = searchParams.get('phone')

    if (!phone) {
      return NextResponse.json({ error: 'Phone number is required' }, { status: 400 })
    }

    const normalizedPhone = phone.replace(/\D/g, '')
    console.log('[Phone Search API] Searching for:', { phone, normalizedPhone })

    // Step 1: Search Supabase cache
    console.log('[Phone Search API] Step 1: Searching Supabase cache...')
    const { data: supabaseCalls, error: supabaseError } = await supabase
      .from('calls')
      .select('*')
      .order('timestamp', { ascending: false })
      .limit(10000)

    if (supabaseError) {
      console.error('[Phone Search API] Supabase error:', supabaseError)
    }

    const supabaseFiltered = filterCallsByPhone(supabaseCalls || [], normalizedPhone)
    console.log('[Phone Search API] Supabase matches:', supabaseFiltered.length)

    let results: any[] = supabaseFiltered.map(transformCallToAPIResponse)

    // Step 2: If not enough results from Supabase, fallback to CTM
    if (results.length === 0) {
      console.log('[Phone Search API] Step 2: No Supabase results, falling back to CTM...')
      
      try {
        const ctmClient = new CTMClient()
        const ctmCalls = await ctmClient.calls.searchCallsByPhone(phone, 8760)
        console.log('[Phone Search API] CTM calls fetched:', ctmCalls.length)
        
        if (ctmCalls.length > 0) {
          const ctmTransformed = ctmCalls.map(transformCTMCallToAPIResponse)
          
          // Store CTM results to Supabase for future searches
          console.log('[Phone Search API] Storing CTM results to Supabase cache...')
          const callsToStore = ctmCalls.map((c: any) => ({
            ctm_call_id: c.id,
            user_id: user.id,
            phone: c.phone,
            direction: c.direction,
            duration: c.duration,
            status: c.status,
            timestamp: c.timestamp instanceof Date ? c.timestamp.toISOString() : c.timestamp,
            caller_number: c.callerNumber,
            tracking_number: c.trackingNumber,
            tracking_label: c.trackingLabel,
            source: c.source,
            source_id: c.sourceId,
            agent_id: c.agent?.id || c.agentId,
            agent_name: c.agent?.name || c.agentName,
            recording_url: c.recordingUrl,
            transcript: c.transcript,
            city: c.city,
            state: c.state,
            postal_code: c.postalCode,
            synced_at: new Date().toISOString(),
          }))

          await supabase.from('calls').upsert(callsToStore, { onConflict: 'ctm_call_id' })
          
          results = ctmTransformed
        }
      } catch (ctmError) {
        console.error('[Phone Search API] CTM fallback error:', ctmError)
      }
    }

    console.log('[Phone Search API] Total results:', results.length)

    return NextResponse.json({
      calls: results,
      count: results.length,
      searchPhone: phone,
    })
  } catch (error) {
    console.error('[Phone Search API] Error:', error)
    return NextResponse.json({ error: 'Failed to search calls' }, { status: 500 })
  }
}