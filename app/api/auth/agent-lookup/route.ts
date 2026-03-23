import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'

export async function POST(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()

    if (!user || !user.email) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const ctmClient = new CTMClient()
    const agents = await ctmClient.agents.getAgents()

    if (!agents || agents.length === 0) {
      return NextResponse.json({
        status: 'deny',
        message: 'No CTM agents configured. Please contact your administrator.',
      })
    }

    const matches = agents.filter(
      (agent) => agent.email?.toLowerCase() === user.email?.toLowerCase()
    )

    if (matches.length === 0) {
      return NextResponse.json({
        status: 'deny',
        message:
          'Your email is not registered as a CTM agent. Please contact your administrator to get access.',
      })
    }

    if (matches.length > 1) {
      return NextResponse.json({
        status: 'manual',
        message: `Multiple agents found with email ${user.email}. Please contact your administrator to assign your agent.`,
      })
    }

    const agentId = matches[0].id

    const { data: existing, error: fetchError } = await supabase
      .from('user_settings')
      .select('settings')
      .eq('user_id', user.id)
      .single()

    if (fetchError && fetchError.code !== 'PGRST116') {
      console.error('Error fetching existing settings:', fetchError)
    }

    const currentSettings = existing?.settings || {}

    const updatedSettings = {
      ...currentSettings,
      ctm_agent_id: agentId,
    }

    const { error: upsertError } = await supabase
      .from('user_settings')
      .upsert({
        user_id: user.id,
        settings: updatedSettings,
        updated_at: new Date().toISOString(),
      })

    if (upsertError) {
      console.error('Error auto-assigning agent:', upsertError)
      return NextResponse.json({
        status: 'error',
        message: 'Failed to assign agent. Please try again.',
      })
    }

    return NextResponse.json({
      status: 'auto_assign',
      agentId,
    })
  } catch (error) {
    console.error('Agent lookup error:', error)
    return NextResponse.json({
      status: 'error',
      message: 'Failed to lookup agent. Please try again.',
    })
  }
}
