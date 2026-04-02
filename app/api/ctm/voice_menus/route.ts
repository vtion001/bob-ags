import { NextRequest, NextResponse } from 'next/server'
import { VoiceMenusService } from '@/lib/ctm/services/voiceMenus'

export async function GET(request: NextRequest) {
  try {
    const voiceMenusService = new VoiceMenusService()
    const data = await voiceMenusService.getVoiceMenus()

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error fetching CTM voice menus:', error)
    return NextResponse.json(
      { error: 'Failed to fetch voice menus from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const voiceMenusService = new VoiceMenusService()
    const data = await voiceMenusService.createVoiceMenu(body)

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error creating CTM voice menu:', error)
    return NextResponse.json(
      { error: 'Failed to create voice menu in CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
