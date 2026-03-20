import { CTMClient } from '../client'
import type { Agent, UserGroup, CTMAgent, CTMUserGroup } from '@/lib/types'

export class AgentsService extends CTMClient {
  async getAgents(): Promise<Agent[]> {
    try {
      const allAgents: Agent[] = []
      let page = 1
      let hasMore = true

      while (hasMore) {
        const endpoint = page === 1
          ? `/accounts/${this.accountId}/agents.json`
          : `/accounts/${this.accountId}/agents.json?page=${page}`
        
        const data = await this.makeRequest<{ agents?: CTMAgent[]; next_page?: string }>(endpoint)
        
        if (data.agents) {
          for (const a of data.agents) {
            allAgents.push({
              id: a.id || String(a.uid) || '',
              uid: a.uid || 0,
              name: a.name || a.email || 'Unknown',
              email: a.email || '',
            })
          }
        }
        
        hasMore = !!data.next_page
        if (hasMore) page++
      }
      
      return allAgents
    } catch {
      return []
    }
  }

  async getUserGroups(): Promise<UserGroup[]> {
    try {
      const data = await this.makeRequest<{ user_groups?: CTMUserGroup[] }>(
        `/accounts/${this.accountId}/user_groups.json`
      )
      return (data.user_groups || []).map(g => ({
        id: String(g.id),
        name: g.name || 'Unknown',
        userIds: g.user_ids || [],
      }))
    } catch (err) {
      console.error('[CTM] getUserGroups error:', err)
      return []
    }
  }
}

export function createAgentsService(): AgentsService {
  return new AgentsService()
}
