import { NextResponse } from 'next/server'
import { NextRequest } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'

const DEV_EMAIL = 'agsdev@allianceglobalsolutions.com'

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const ctmClient = new CTMClient()
    const calls = await ctmClient.calls.getActiveCalls()

    const isDevAdmin = user.email === DEV_EMAIL

    const { data: userRole } = await supabase
      .from('user_roles')
      .select('role')
      .eq('user_id', user.id)
      .single()

    const isAdmin = isDevAdmin || userRole?.role === 'admin'

    if (isAdmin) {
      return NextResponse.json({ calls })
    }

    const { data: userSettings } = await supabase
      .from('user_settings')
      .select('settings')
      .eq('user_id', user.id)
      .single()

    const settings = userSettings?.settings || {}
    const assignedAgentId = settings.ctm_agent_id

    if (!assignedAgentId) {
      return NextResponse.json({ calls: [] })
    }

    const filteredCalls = calls.filter(call => {
      const aid = call.agent?.id
      const aidStr = String(aid ?? '')
      const assignedStr = String(assignedAgentId)
      return aidStr === assignedStr
    })

    return NextResponse.json({ calls: filteredCalls })
  } catch (error) {
    console.error('CTM active calls error:', error)
    return NextResponse.json(
      { error: 'Failed to fetch active calls from CTM' },
      { status: 500 }
    )
  }
}
