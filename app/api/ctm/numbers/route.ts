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
    const data = await ctmClient.getNumbers()

    return NextResponse.json(data)
  } catch (error) {
    console.error('CTM numbers error:', error)
    return NextResponse.json(
      { error: 'Failed to fetch numbers from CTM' },
      { status: 500 }
    )
  }
}
