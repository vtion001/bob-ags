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

    const { data: userSettings, error } = await supabase
      .from('user_settings')
      .select('settings')
      .eq('user_id', user.id)
      .single()

    if (error || !userSettings) {
      return NextResponse.json({
        success: true,
        settings: {
          ctm_access_key: '',
          ctm_secret_key: '',
          ctm_account_id: '',
          openrouter_api_key: '',
          default_client: 'flyland',
          light_mode: true,
          email_notifications: false,
          auto_sync_calls: true,
          call_sync_interval: 60,
        }
      })
    }

    return NextResponse.json({
      success: true,
      settings: userSettings.settings
    })
  } catch (error) {
    console.error('Error fetching user settings:', error)
    return NextResponse.json(
      { error: 'Failed to fetch user settings' },
      { status: 500 }
    )
  }
}
