import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

export async function GET(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()

    if (!user) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 }
      )
    }

    // Get user permissions from Supabase
    const { data: userRole, error } = await supabase
      .from('user_roles')
      .select('role, permissions')
      .eq('user_id', user.id)
      .single()

    if (error || !userRole) {
      return NextResponse.json({
        success: true,
        role: 'viewer',
        permissions: {
          can_view_calls: true,
          can_view_monitor: true,
          can_view_history: false,
          can_view_agents: false,
          can_manage_settings: false,
          can_manage_users: false,
          can_run_analysis: false,
        }
      })
    }

    return NextResponse.json({
      success: true,
      role: userRole.role,
      permissions: userRole.permissions || {}
    })
  } catch (error) {
    console.error('Permissions error:', error)
    return NextResponse.json({
      success: true,
      role: 'viewer',
      permissions: {}
    })
  }
}

export async function PUT(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()

    if (!user) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 }
      )
    }

    const body = await request.json()
    const { role, permissions } = body

    // Update user permissions in Supabase
    const { data, error } = await supabase
      .from('user_roles')
      .upsert({
        user_id: user.id,
        role: role || 'viewer',
        permissions: permissions || {}
      })
      .select()
      .single()

    if (error) {
      console.error('Error updating permissions:', error)
      return NextResponse.json(
        { error: 'Failed to update permissions' },
        { status: 500 }
      )
    }

    return NextResponse.json({
      success: true,
      role: data.role,
      permissions: data.permissions
    })
  } catch (error) {
    console.error('Permissions update error:', error)
    return NextResponse.json(
      { error: 'Failed to update permissions' },
      { status: 500 }
    )
  }
}
