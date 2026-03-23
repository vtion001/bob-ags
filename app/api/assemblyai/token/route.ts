import { NextRequest, NextResponse } from 'next/server'

export async function POST(request: NextRequest) {
  try {
    const apiKey = process.env.ASSEMBLYAI_API_KEY || process.env.NEXT_PUBLIC_ASSEMBLYAI_API_KEY
    
    if (!apiKey) {
      return NextResponse.json(
        { error: 'AssemblyAI API key not configured. Set ASSEMBLYAI_API_KEY in your environment.' },
        { status: 500 }
      )
    }
    
    console.log('[Token API] Using API key:', apiKey.substring(0, 8) + '...')

    // Use direct fetch to create temporary token via AssemblyAI streaming API
    const response = await fetch(`https://streaming.assemblyai.com/v3/token?expires_in_seconds=600`, {
      method: 'GET',
      headers: {
        'Authorization': apiKey,
        'Content-Type': 'application/json',
      },
    })
    
    if (!response.ok) {
      const errorText = await response.text()
      console.error('[Token API] Direct fetch failed:', response.status, errorText)
      throw new Error(`Token API returned ${response.status}: ${errorText}`)
    }
    
    const data = await response.json()
    console.log('[Token API] Got token:', data.token?.substring(0, 20) + '...')

    return NextResponse.json({
      success: true,
      token: data.token,
    })
  } catch (error) {
    console.error('AssemblyAI token error:', error)
    const errorMessage = error instanceof Error ? error.message : 'Unknown error'
    return NextResponse.json(
      { error: `Failed to generate AssemblyAI token: ${errorMessage}` },
      { status: 500 }
    )
  }
}