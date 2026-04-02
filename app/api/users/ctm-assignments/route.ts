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

    // Get CTM assignments from Supabase
    const { data, error } = await supabase
      .from('ctm_assignments')
      .select('*')
      .eq('user_id', user.id)

    if (error) {
      console.error('Error fetching CTM assignments:', error)
      return NextResponse.json({
        success: true,
        assignments: []
      })
    }

    return NextResponse.json({
      success: true,
      assignments: data || []
    })
  } catch (error) {
    console.error('CTM assignments error:', error)
    return NextResponse.json({
      success: true,
      assignments: []
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

    // Update CTM assignments in Supabase
    const { data, error } = await supabase
      .from('ctm_assignments')
      .upsert({
        user_id: user.id,
        ...body
      })
      .select()
      .single()

    if (error) {
      console.error('Error updating CTM assignments:', error)
      return NextResponse.json(
        { error: 'Failed to update CTM assignments' },
        { status: 500 }
      )
    }

    return NextResponse.json({
      success: true,
      assignments: data
    })
  } catch (error) {
    console.error('CTM assignments error:', error)
    return NextResponse.json(
      { error: 'Failed to update CTM assignments' },
      { status: 500 }
    )
  }
}
