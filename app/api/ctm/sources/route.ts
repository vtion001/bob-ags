import { NextRequest, NextResponse } from 'next/server'
import { SourcesService } from '@/lib/ctm/services/sources'

export async function GET(request: NextRequest) {
  try {
    const sourcesService = new SourcesService()
    const data = await sourcesService.getSources()

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error fetching CTM sources:', error)
    return NextResponse.json(
      { error: 'Failed to fetch sources from CallTrackingMetrics' },
      { status: 502 }
    )
  }
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const sourcesService = new SourcesService()
    const data = await sourcesService.createSource(body)

    return NextResponse.json({
      success: true,
      ...data
    })
  } catch (error) {
    console.error('Error creating CTM source:', error)
    return NextResponse.json(
      { error: 'Failed to create source in CallTrackingMetrics' },
      { status: 502 }
    )
  }
}
