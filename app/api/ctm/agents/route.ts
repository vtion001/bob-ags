import { NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'
import { CTMClient } from '@/lib/ctm'

export async function GET() {
  try {
    const supabase = await createServerSupabase()
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const ctmClient = new CTMClient()
    const agents = await ctmClient.getAgents()
    const userGroups = await ctmClient.getUserGroups()

    return NextResponse.json({ 
      agents,
      userGroups 
    })
  } catch (error) {
    console.error('CTM agents error:', error)
    return NextResponse.json({ error: 'Failed to fetch agents' }, { status: 500 })
  }
}