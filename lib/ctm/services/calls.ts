import { CTMClient } from '../client'
import { transformCall } from '../transformer'
import type { Call, GetCallsParams, CTMCall } from '@/lib/types'

function normalizePhoneForComparison(phone: string): string {
  const digits = phone.replace(/\D/g, '')
  if (digits.length === 11 && digits.startsWith('1')) {
    return digits.slice(1)
  }
  return digits
}

function phoneMatches(phone1: string, phone2: string): boolean {
  const norm1 = normalizePhoneForComparison(phone1)
  const norm2 = normalizePhoneForComparison(phone2)
  
  if (!norm1 || !norm2) return false
  
  if (norm1.length === norm2.length) {
    return norm1 === norm2
  }
  
  const shorter = norm1.length < norm2.length ? norm1 : norm2
  const longer = norm1.length < norm2.length ? norm2 : norm1
  
  return longer.slice(-shorter.length) === shorter
}

export class CallsService extends CTMClient {
  async getCalls(params: GetCallsParams = {}): Promise<Call[]> {
    const { limit = 100, hours = 24, status, sourceId, agentId } = params
    
    const callsPerRequest = 10
    const pagesNeeded = Math.ceil(limit / callsPerRequest)
    
    let allCalls: Call[] = []
    
    for (let page = 1; page <= pagesNeeded && allCalls.length < limit; page++) {
      let endpoint = `/accounts/${this.accountId}/calls.json?limit=${callsPerRequest}&hours=${hours}&page=${page}`
      if (status) endpoint += `&status=${status}`
      if (sourceId) endpoint += `&source_id=${sourceId}`
      if (agentId) endpoint += `&agent_id=${agentId}`

      const data = await this.makeRequest<{ calls?: CTMCall[] }>(endpoint)
      
      if (!data.calls || data.calls.length === 0) break

      const transformedCalls = data.calls.map(transformCall)
      
      if (agentId) {
        allCalls.push(...transformedCalls.filter(c => c.agent?.id === agentId))
      } else {
        allCalls.push(...transformedCalls)
      }
    }

    return allCalls
  }

  async getCall(callId: string): Promise<Call | null> {
    try {
      const data = await this.makeRequest<CTMCall>(
        `/accounts/${this.accountId}/calls/${callId}.json`
      )
      return data ? transformCall(data) : null
    } catch {
      return null
    }
  }

  async getCallTranscript(callId: string): Promise<string> {
    try {
      const data = await this.makeRequest<{ transcript?: string }>(
        `/accounts/${this.accountId}/calls/${callId}/transcript`
      )
      return data.transcript || ''
    } catch {
      return ''
    }
  }

  async getActiveCalls(): Promise<Call[]> {
    return this.getCalls({ hours: 1 })
  }

  async getRecentCalls(minutes: number = 5): Promise<Call[]> {
    const hours = Math.max(0.017, minutes / 60)
    return this.getCalls({ hours, limit: 50 })
  }

  async searchCallsByPhone(phoneNumber: string, hours: number = 8760): Promise<Call[]> {
    const allCalls = await this.getCalls({ limit: 5000, hours })
    
    console.log('[searchCallsByPhone] Fetched calls:', allCalls.length)
    
    return allCalls.filter(call => {
      const phoneFields = [
        call.phone,
        call.callerNumber,
        call.trackingNumber,
        call.destinationNumber,
        call.poolNumber,
        call.didNumber,
      ]
      return phoneFields.some(field => {
        if (!field) return false
        return phoneMatches(String(field), phoneNumber)
      })
    })
  }
}

export function createCallsService(): CallsService {
  return new CallsService()
}
