import { Call } from './mockData'

interface CTMConfig {
  accessKey: string
  secretKey: string
  accountId: string
}

interface GetCallsParams {
  limit?: number
  hours?: number
  status?: string | null
  sourceId?: string | null
}

interface CTMCall {
  id: number
  caller_id?: string
  phone_number?: string
  duration?: number
  status?: string
  direction?: string
  started_at?: string
  source_id?: string
  tracking_number?: string
}

export class CTMClient {
  private accessKey: string
  private secretKey: string
  private accountId: string
  private baseUrl: string

  constructor() {
    this.accessKey = process.env.CTM_ACCESS_KEY || ''
    this.secretKey = process.env.CTM_SECRET_KEY || ''
    this.accountId = process.env.CTM_ACCOUNT_ID || ''
    this.baseUrl = 'https://api.calltrackingmetrics.com/api/v1'
  }

  private async makeRequest<T>(endpoint: string): Promise<T> {
    if (!this.accessKey || !this.secretKey || !this.accountId) {
      throw new Error('CTM credentials not configured')
    }

    const url = `${this.baseUrl}${endpoint}`
    const auth = Buffer.from(`${this.accessKey}:${this.secretKey}`).toString('base64')

    const response = await fetch(url, {
      headers: {
        'Authorization': `Basic ${auth}`,
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`CTM API error: ${response.status} ${response.statusText}`)
    }

    return response.json()
  }

  async getCalls(params: GetCallsParams = {}): Promise<Call[]> {
    const { limit = 100, hours = 24, status, sourceId } = params
    
    let endpoint = `/accounts/${this.accountId}/calls.json?limit=${limit}&hours=${hours}`
    if (status) endpoint += `&status=${status}`
    if (sourceId) endpoint += `&source_id=${sourceId}`

    const data = await this.makeRequest<{ calls?: CTMCall[] }>(endpoint)
    
    if (!data.calls) return []

    return data.calls.map((call: CTMCall) => this.transformCall(call))
  }

  async getCall(callId: string): Promise<Call | null> {
    try {
      const data = await this.makeRequest<{ call?: CTMCall }>(
        `/accounts/${this.accountId}/calls/${callId}`
      )
      return data.call ? this.transformCall(data.call) : null
    } catch {
      return null
    }
  }

  async getCallTranscript(callId: string): Promise<string> {
    const data = await this.makeRequest<{ transcript?: string }>(
      `/accounts/${this.accountId}/calls/${callId}/transcript`
    )
    return data.transcript || ''
  }

  private transformCall(ctmCall: CTMCall): Call {
    return {
      id: String(ctmCall.id),
      phone: ctmCall.phone_number || ctmCall.caller_id || '',
      direction: (ctmCall.direction as 'inbound' | 'outbound') || 'inbound',
      duration: ctmCall.duration || 0,
      status: this.mapStatus(ctmCall.status),
      timestamp: ctmCall.started_at ? new Date(ctmCall.started_at) : new Date(),
    }
  }

  private mapStatus(ctmStatus?: string): 'completed' | 'missed' | 'active' {
    if (!ctmStatus) return 'completed'
    const statusMap: Record<string, 'completed' | 'missed' | 'active'> = {
      'completed': 'completed',
      'missed': 'missed',
      'active': 'active',
      'voicemail': 'completed',
    }
    return statusMap[ctmStatus.toLowerCase()] || 'completed'
  }
}

export function createCTMClient(): CTMClient {
  return new CTMClient()
}