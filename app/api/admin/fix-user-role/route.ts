import { NextRequest, NextResponse } from 'next/server'
import { createServerSupabase } from '@/lib/supabase/server'

export async function POST(request: NextRequest) {
  const { searchParams } = new URL(request.url)
  const secret = searchParams.get('secret')

  // Simple secret check - in production this should be an env var
  if (secret !== 'ags-admin-fix-2026') {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  }

  const supabase = await createServerSupabase(request)

  const devEmail = 'agsdev@allianceglobalsolutions.com'

  // Update user_roles to admin
  const { error: roleError } = await supabase
    .from('user_roles')
    .update({ role: 'admin', approved: true })
    .eq('email', devEmail)

  if (roleError) {
    console.error('Role update error:', roleError)
    return NextResponse.json({ error: roleError.message }, { status: 500 })
  }

  // Update users to superadmin
  const { error: userError } = await supabase
    .from('users')
    .update({ is_superadmin: true })
    .eq('email', devEmail)

  if (userError) {
    console.error('User update error:', userError)
    return NextResponse.json({ error: userError.message }, { status: 500 })
  }

  return NextResponse.json({
    success: true,
    message: `${devEmail} is now a super admin`
  })
}