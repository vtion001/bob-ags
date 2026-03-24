import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'
import { transformCTMCallToAPIResponse } from '@/lib/calls/transformer'

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const phone = searchParams.get('phone')
    const hours = parseInt(searchParams.get('hours') || '8760')

    if (!phone) {
      return NextResponse.json({ error: 'Phone number is required' }, { status: 400 })
    }

    console.log('[Phone Search API] Starting search:', { phone, hours })

    const ctmClient = new CTMClient()
    console.log('[Phone Search API] CTM Client initialized')
    
    const calls = await ctmClient.calls.searchCallsByPhone(phone, hours)
    console.log('[Phone Search API] Raw calls from CTM:', calls.length)
    
    if (calls.length > 0) {
      console.log('[Phone Search API] Sample call phones:', calls.slice(0, 3).map(c => ({
        phone: c.phone,
        callerNumber: c.callerNumber,
        trackingNumber: c.trackingNumber,
      })))
    }
    
    const apiResponses = calls.map(transformCTMCallToAPIResponse)

    console.log('[Phone Search API] Returning:', apiResponses.length)

    return NextResponse.json({
      calls: apiResponses,
      count: apiResponses.length,
      searchPhone: phone,
    })
  } catch (error) {
    console.error('[Phone Search API] Error:', error)
    return NextResponse.json({ error: 'Failed to search calls' }, { status: 500 })
  }
}