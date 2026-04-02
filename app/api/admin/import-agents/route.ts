import { NextRequest, NextResponse } from 'next/server'
import { createClient } from '@supabase/supabase-js'
import { CTMClient } from '@/lib/ctm/client'

const PHILLIES_GROUP_UIDS = [
  535923, 552210, 552216, 599232, 599238,
  779372, 779375, 779378, 779381, 779387,
  835207, 838132, 857749, 873789, 873795,
  912540, 937020, 937023, 937026, 937032
]

export async function POST(request: NextRequest) {
  const { searchParams } = new URL(request.url)
  const secret = searchParams.get('secret')

  if (secret !== 'ags-admin-fix-2026') {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  }

  try {
    const ctmClient = new CTMClient()
    const supabaseAdmin = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL!,
      process.env.SUPABASE_SERVICE_ROLE_KEY!,
      { auth: { persistSession: false } }
    )

    const results = []

    // Fetch each agent by UID from CTM
    for (const uid of PHILLIES_GROUP_UIDS) {
      try {
        const agentData = await ctmClient.makeRequest<{ agent?: { id: string; uid: number; name: string; email: string } }>(
          `/accounts/${ctmClient.accountId}/agents/${uid}.json`
        )

        const agent = agentData.agent
        if (!agent) {
          results.push({ uid, status: 'not_found' })
          continue
        }

        // Check if already exists
        const { data: existing } = await supabaseAdmin
          .from('agent_profiles')
          .select('id')
          .eq('agent_id', agent.id)
          .single()

        if (existing) {
          results.push({ name: agent.name, email: agent.email, status: 'already_exists' })
          continue
        }

        // Insert new agent profile
        const { error: insertError } = await supabaseAdmin
          .from('agent_profiles')
          .insert({
            name: agent.name,
            agent_id: agent.id,
            email: agent.email || null,
            phone: null,
            notes: `Auto-imported from CTM Phillies group on ${new Date().toISOString()}`,
          })

        if (insertError) {
          console.error('[Import] Insert error:', agent.name, insertError)
          results.push({ name: agent.name, email: agent.email, status: 'error', error: insertError.message })
        } else {
          results.push({ name: agent.name, email: agent.email, status: 'imported' })
        }
      } catch (err) {
        console.error('[Import] Error fetching UID:', uid, err)
        results.push({ uid, status: 'error' })
      }
    }

    return NextResponse.json({
      success: true,
      total: PHILLIES_GROUP_UIDS.length,
      imported: results.filter(r => r.status === 'imported').length,
      alreadyExists: results.filter(r => r.status === 'already_exists').length,
      notFound: results.filter(r => r.status === 'not_found').length,
      errors: results.filter(r => r.status === 'error').length,
      results,
    })
  } catch (error) {
    console.error('[Import Agents] Error:', error)
    return NextResponse.json(
      { error: error instanceof Error ? error.message : 'Import failed' },
      { status: 500 }
    )
  }
}