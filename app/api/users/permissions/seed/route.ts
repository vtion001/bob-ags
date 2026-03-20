import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

const DEV_EMAIL = 'agsdev@allianceglobalsolutions.com'

export async function POST(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const adminPermissions = {
      can_view_calls: true,
      can_view_monitor: true,
      can_view_history: true,
      can_view_agents: true,
      can_manage_settings: true,
      can_manage_users: true,
      can_run_analysis: true,
    }

    const { data, error } = await supabase
      .from('user_roles')
      .upsert({
        user_id: user.id,
        email: user.email,
        role: 'admin',
        permissions: adminPermissions,
        updated_at: new Date().toISOString(),
      })
      .select()
      .single()

    if (error) {
      console.error('Error seeding admin:', error)
      return NextResponse.json({ error: 'Failed to seed admin' }, { status: 500 })
    }

    return NextResponse.json({ success: true, data })
  } catch (error) {
    console.error('Seed admin error:', error)
    return NextResponse.json({ error: 'Failed to seed admin' }, { status: 500 })
  }
}
