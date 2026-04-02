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

    // Bulk sync is a no-op in standalone mode - returns empty result
    return NextResponse.json({
      success: true,
      synced: 0,
      message: 'Bulk sync completed (no-op in standalone mode)'
    })
  } catch (error) {
    console.error('Error in bulk sync:', error)
    return NextResponse.json(
      { error: 'Failed to perform bulk sync' },
      { status: 500 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const supabase = await createServerSupabase(request)
    const { data: { user } } = await supabase.auth.getUser()

    if (!user) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 }
      )
    }

    // Bulk sync is a no-op in standalone mode - returns empty result
    return NextResponse.json({
      success: true,
      synced: 0,
      message: 'Bulk sync completed (no-op in standalone mode)'
    })
  } catch (error) {
    console.error('Error in bulk sync:', error)
    return NextResponse.json(
      { error: 'Failed to perform bulk sync' },
      { status: 500 }
    )
  }
}
