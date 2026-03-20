'use client'

import React, { useState, useEffect } from 'react'
import Button from '@/components/ui/Button'
import StatsCard from '@/components/StatsCard'
import CallTable from '@/components/CallTable'
import Select, { SelectOption } from '@/components/ui/select'
import { Call } from '@/lib/ctm'

interface Agent {
  id: string
  uid: number
  name: string
  email: string
}

interface UserGroup {
  id: string
  name: string
  userIds: number[]
}

interface DashboardStats {
  totalCalls: number
  analyzed: number
  hotLeads: number
  avgScore: string
}

type TimeRange = '24h' | '7d' | '30d' | '90d' | 'custom'

function getHoursFromRange(range: TimeRange): number {
  switch (range) {
    case '24h': return 24
    case '7d': return 168
    case '30d': return 720
    case '90d': return 2160
    default: return 168
  }
}

function formatDateRange(range: TimeRange): string {
  switch (range) {
    case '24h': return 'Last 24 Hours'
    case '7d': return 'Last 7 Days'
    case '30d': return 'Last 30 Days'
    case '90d': return 'Last 90 Days'
    case 'custom': return 'Custom Range'
  }
}

export default function DashboardPage() {
  const [isLoading, setIsLoading] = useState(true)
  const [isRefreshing, setIsRefreshing] = useState(false)
  const [isAnalyzing, setIsAnalyzing] = useState(false)
  const [analyzeProgress, setAnalyzeProgress] = useState<string>('')
  const [autoRefresh, setAutoRefresh] = useState(true)
  const [userGroups, setUserGroups] = useState<UserGroup[]>([])
  const [allAgents, setAllAgents] = useState<Agent[]>([])
  const [selectedGroup, setSelectedGroup] = useState<string>('all')
  const [selectedAgent, setSelectedAgent] = useState<string>('all')
  const [selectedAgentUid, setSelectedAgentUid] = useState<number | null>(null)
  const [timeRange, setTimeRange] = useState<TimeRange>('7d')
  const [customStartDate, setCustomStartDate] = useState<string>('')
  const [customEndDate, setCustomEndDate] = useState<string>('')
  const [stats, setStats] = useState<DashboardStats>({
    totalCalls: 0,
    analyzed: 0,
    hotLeads: 0,
    avgScore: '0',
  })
  const [recentCalls, setRecentCalls] = useState<Call[]>([])
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const checkAuthAndFetch = async () => {
      try {
        const sessionRes = await fetch('/api/auth/session')
        if (!sessionRes.ok) {
          window.location.href = '/'
          return
        }
        setIsLoading(false)
        fetchAgents()
      } catch {
        window.location.href = '/'
      }
    }
    checkAuthAndFetch()
  }, [])

  const fetchAgents = async () => {
    try {
      const res = await fetch('/api/ctm/agents')
      if (res.ok) {
        const data = await res.json()
        setAllAgents(data.agents || [])
        setUserGroups(data.userGroups || [])
      }
    } catch (err) {
      console.error('Failed to fetch agents:', err)
    }
  }

  const handleGroupChange = (groupId: string) => {
    setSelectedGroup(groupId)
    setSelectedAgent('all')
    setSelectedAgentUid(null)
  }

  const handleAgentChange = (agentId: string) => {
    setSelectedAgent(agentId)
    const agent = allAgents.find(a => a.id === agentId)
    setSelectedAgentUid(agent?.uid || null)
  }

  const getAvailableAgents = (): Agent[] => {
    if (selectedGroup === 'all') {
      return allAgents
    }
    const group = userGroups.find(g => g.id === selectedGroup)
    if (!group) return []
    return allAgents.filter(agent => group.userIds.includes(agent.uid))
  }

  const getHoursParam = (): number => {
    if (timeRange === 'custom' && customStartDate && customEndDate) {
      const start = new Date(customStartDate)
      const end = new Date(customEndDate)
      const hours = Math.max(1, Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60)))
      return Math.min(hours, 2160)
    }
    return getHoursFromRange(timeRange)
  }

  const fetchData = async () => {
    try {
      const hours = getHoursParam()
      let url = `/api/calls?limit=500&hours=${hours}`
      
      if (selectedAgentUid !== null) {
        url += `&agentId=${selectedAgentUid}`
      }
      
      // Fetch from cache first - this returns immediately with cached data
      const res = await fetch(url)
      if (!res.ok) throw new Error('Failed to fetch data')
      const data = await res.json()
      
      let filteredCalls = (data.calls || []).filter(
        (call: Call) => call.direction === 'inbound'
      )
      
      if (selectedGroup !== 'all') {
        const group = userGroups.find(g => g.id === selectedGroup)
        if (group) {
          filteredCalls = filteredCalls.filter((call: Call) => {
            if (call.agent?.id) {
              const agent = allAgents.find(a => a.id === call.agent?.id)
              return agent ? group.userIds.includes(agent.uid) : false
            }
            return false
          })
        }
      }
      
      const inboundTotal = filteredCalls.length
      const analyzedCount = filteredCalls.filter((c: Call) => c.score || c.analysis).length
      const hotLeadsCount = filteredCalls.filter((c: Call) => c.analysis?.sentiment === 'positive' || (c.score && c.score >= 80)).length
      const scoredCalls = filteredCalls.filter((c: Call) => c.score && c.score > 0)
      const avgScore = scoredCalls.length > 0 
        ? Math.round(scoredCalls.reduce((sum: number, c: Call) => sum + (c.score || 0), 0) / scoredCalls.length)
        : 0
      
      setStats({
        totalCalls: inboundTotal,
        analyzed: analyzedCount,
        hotLeads: hotLeadsCount,
        avgScore: avgScore.toString(),
      })
      setRecentCalls(filteredCalls.slice(0, 50))
      setError(null)
      
      // Trigger background sync if the API indicates cache is stale
      // This will only sync NEW calls (incremental), not re-fetch everything
      if (data.needsSync) {
        syncCallsInBackground()
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    }
  }

  const syncCallsInBackground = async () => {
    try {
      // Incremental sync - only fetches new calls since last cached call
      let url = '/api/calls?hours=2160'
      if (selectedAgentUid !== null) {
        url += `&agentId=${selectedAgentUid}`
      }
      // Fire and forget - don't await, don't block UI
      fetch(url, { method: 'POST' }).catch(err => {
        console.error('Background sync failed:', err)
      })
    } catch (err) {
      console.error('Background sync failed:', err)
    }
  }

  const handleSyncNow = async () => {
    setIsRefreshing(true)
    try {
      // Trigger a full sync
      let syncUrl = '/api/calls?hours=2160'
      if (selectedAgentUid !== null) {
        syncUrl += `&agentId=${selectedAgentUid}`
      }
      const syncRes = await fetch(syncUrl, { method: 'POST' })
      if (!syncRes.ok) throw new Error('Sync failed')
      
      // Then fetch fresh data with skipSync to avoid re-triggering background sync
      const hours = getHoursParam()
      let url = `/api/calls?limit=500&hours=${hours}&skipSync=true`
      if (selectedAgentUid !== null) {
        url += `&agentId=${selectedAgentUid}`
      }
      const res = await fetch(url)
      if (!res.ok) throw new Error('Failed to fetch data')
      const data = await res.json()
      
      let filteredCalls = (data.calls || []).filter(
        (call: Call) => call.direction === 'inbound'
      )
      
      if (selectedGroup !== 'all') {
        const group = userGroups.find(g => g.id === selectedGroup)
        if (group) {
          filteredCalls = filteredCalls.filter((call: Call) => {
            if (call.agent?.id) {
              const agent = allAgents.find(a => a.id === call.agent?.id)
              return agent ? group.userIds.includes(agent.uid) : false
            }
            return false
          })
        }
      }
      
      const inboundTotal = filteredCalls.length
      const analyzedCount = filteredCalls.filter((c: Call) => c.score || c.analysis).length
      const hotLeadsCount = filteredCalls.filter((c: Call) => c.analysis?.sentiment === 'positive' || (c.score && c.score >= 80)).length
      const scoredCalls = filteredCalls.filter((c: Call) => c.score && c.score > 0)
      const avgScore = scoredCalls.length > 0 
        ? Math.round(scoredCalls.reduce((sum: number, c: Call) => sum + (c.score || 0), 0) / scoredCalls.length)
        : 0
      
      setStats({
        totalCalls: inboundTotal,
        analyzed: analyzedCount,
        hotLeads: hotLeadsCount,
        avgScore: avgScore.toString(),
      })
      setRecentCalls(filteredCalls.slice(0, 50))
      setError(null)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Sync failed')
    } finally {
      setIsRefreshing(false)
    }
  }

  useEffect(() => {
    fetchData()
  }, [selectedAgentUid, selectedGroup, timeRange, customStartDate, customEndDate])

  useEffect(() => {
    if (!autoRefresh) return
    
    const interval = setInterval(() => {
      fetchData()
    }, 30000)
    
    return () => clearInterval(interval)
  }, [autoRefresh, selectedAgentUid, selectedGroup, timeRange])

  const toggleAutoRefresh = () => {
    setAutoRefresh(!autoRefresh)
  }

  const handleAnalyze = async () => {
    setIsAnalyzing(true)
    setAnalyzeProgress('Fetching calls from cache...')
    
    try {
      const callsRes = await fetch('/api/calls?limit=500&hours=168&skipSync=true')
      if (!callsRes.ok) throw new Error('Failed to fetch calls from cache')
      const callsData = await callsRes.json()
      
      const callsWithoutAnalysis = (callsData.calls || []).filter(
        (c: Call & { ctm_call_id?: string }) => c.direction === 'inbound' && !c.score && c.ctm_call_id
      )
      
      if (callsWithoutAnalysis.length === 0) {
        setAnalyzeProgress('All calls already analyzed!')
        setTimeout(() => setAnalyzeProgress(''), 2000)
        setIsAnalyzing(false)
        return
      }

      const callIds = callsWithoutAnalysis.map((c: Call & { ctm_call_id?: string }) => c.ctm_call_id!).filter(Boolean)
      setAnalyzeProgress(`Analyzing ${callIds.length} calls...`)
      
      const analyzeRes = await fetch('/api/ctm/calls/analyze', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ callIds }),
      })
      
      if (!analyzeRes.ok) throw new Error('Analysis failed')
      
      const result = await analyzeRes.json()
      setAnalyzeProgress(`Analyzed ${result.analyzed} calls successfully!`)
      
      await fetchData()
      
      setTimeout(() => setAnalyzeProgress(''), 3000)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Analysis failed')
    } finally {
      setIsAnalyzing(false)
    }
  }

  if (isLoading) {
    return (
      <div className="p-6 lg:p-8 max-w-7xl mx-auto">
        <div className="flex items-center justify-center h-64">
          <div className="w-12 h-12 border-4 border-navy-100 border-t-navy-900 rounded-full animate-spin" />
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 lg:p-8 max-w-7xl mx-auto">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 className="text-3xl font-bold text-navy-900 mb-2">Dashboard</h1>
          <p className="text-navy-500">Monitor and analyze your calls in real-time</p>
        </div>
        <div className="flex gap-3 items-center flex-wrap">
          <div className="flex items-center gap-2">
            <svg className="w-4 h-4 text-navy-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <Select
              value={timeRange}
              onChange={(value) => setTimeRange(value as TimeRange)}
              options={[
                { value: '24h', label: 'Last 24 Hours' },
                { value: '7d', label: 'Last 7 Days' },
                { value: '30d', label: 'Last 30 Days' },
                { value: '90d', label: 'Last 90 Days' },
                { value: 'custom', label: 'Custom Range' },
              ]}
              className="w-40"
            />
          </div>
          {timeRange === 'custom' && (
            <>
              <input
                type="date"
                value={customStartDate}
                onChange={(e) => setCustomStartDate(e.target.value)}
                className="px-3 py-2 rounded-lg text-sm font-medium bg-white border border-navy-200 text-navy-900 focus:outline-none focus:border-navy-400"
              />
              <span className="text-navy-500">to</span>
              <input
                type="date"
                value={customEndDate}
                onChange={(e) => setCustomEndDate(e.target.value)}
                className="px-3 py-2 rounded-lg text-sm font-medium bg-white border border-navy-200 text-navy-900 focus:outline-none focus:border-navy-400"
              />
            </>
          )}
          {userGroups.length > 0 && (
            <Select
              value={selectedGroup}
              onChange={handleGroupChange}
              options={[
                { value: 'all', label: 'All Groups' },
                ...userGroups.map((group) => ({
                  value: group.id,
                  label: group.name,
                })),
              ]}
              className="w-40"
            />
          )}
          {getAvailableAgents().length > 0 && (
            <Select
              value={selectedAgent}
              onChange={handleAgentChange}
              options={[
                { value: 'all', label: 'All Agents' },
                ...getAvailableAgents().map((agent) => ({
                  value: agent.id,
                  label: agent.name,
                })),
              ]}
              className="w-40"
            />
          )}
          <Button
            variant="secondary"
            size="md"
            onClick={handleSyncNow}
            isLoading={isRefreshing}
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Sync Now
          </Button>
          <button
            onClick={toggleAutoRefresh}
            className={`flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
              autoRefresh 
                ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
            }`}
          >
            <svg className={`w-4 h-4 ${autoRefresh ? 'animate-spin-slow' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            {autoRefresh ? 'Auto' : 'Paused'}
          </button>
          <Button 
            variant="primary" 
            size="md"
            onClick={handleAnalyze}
            isLoading={isAnalyzing}
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Run Analysis
          </Button>
        </div>
      </div>

      {analyzeProgress && (
        <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div className="flex items-center gap-3">
            <div className="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
            <p className="text-blue-600 font-medium">{analyzeProgress}</p>
          </div>
        </div>
      )}

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div className="flex items-start gap-3">
            <svg className="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p className="text-red-600 font-medium">{error}</p>
              <p className="text-navy-500 text-sm mt-1">Please check your CTM credentials in .env</p>
            </div>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatsCard
          label="Total Calls"
          value={stats.totalCalls}
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          }
          trend={{ value: 12, direction: 'up' }}
        />
        <StatsCard
          label="Analyzed"
          value={`${stats.analyzed}/${stats.totalCalls}`}
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          }
        />
        <StatsCard
          label="Hot Leads"
          value={stats.hotLeads}
          icon={
            <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.266-.526.75-.36 1.158.328.808.454 1.703.454 2.674 0 1.553-.37 3.078-1.083 4.413.071.165.136.33.201.492.126.277.208.576.208.906 0 .893-.36 1.702-.94 2.289a1 1 0 001.415 1.414c.822-.822 1.333-1.96 1.333-3.203 0-.339-.027-.674-.08-1.003.686-1.46 1.081-3.081 1.081-4.762 0-1.2-.132-2.371-.382-3.5.226-.617.733-1.058 1.341-1.058.981 0 1.793.795 1.8 1.772.007.09.01.18.01.27 0 1.03-.244 2.006-.68 2.87.313.29.64.56.977.776.604.404 1.266.72 1.964.93.504.144 1.028.226 1.567.226 1.654 0 3.173-.447 4.506-1.23.177-.106.35-.218.519-.336a1 1 0 10-1.219-1.612c-.134.1-.268.2-.406.3-1.09.766-2.408 1.209-3.806 1.209-.42 0-.835-.04-1.24-.118-.327.073-.666.11-1.013.11-.982 0-1.793-.795-1.8-1.773a5.946 5.946 0 00-.01-.269zM8.596 4.001A1 1 0 007.18 2.586c-.822.822-1.333 1.96-1.333 3.203 0 .339.027.674.08 1.003-.686 1.46-1.081 3.081-1.081 4.762 0 1.2.132 2.371.382 3.5-.226.617-.733 1.058-1.341 1.058-.981 0-1.793-.795-1.8-1.772a5.946 5.946 0 00-.01-.269c0-1.03.244-2.006.68-2.87-.313-.29-.64-.56-.977-.776-.604-.404-1.266-.72-1.964-.93-.504-.144-1.028-.226-1.567-.226-1.654 0-3.173.447-4.506 1.23-.177.106-.35.218-.519.336a1 1 0 101.219 1.612c.134-.1.268-.2.406-.3 1.09-.766 2.408-1.209 3.806-1.209.42 0 .835.04 1.24.118.327-.073.666-.11 1.013-.11.982 0 1.793.795 1.8 1.773.008.09.01.18.01.269 0 1.03-.244 2.006-.68 2.87.313.29.64.56.977.776.604.404 1.266.72 1.964.93.504.144 1.028.226 1.567.226 1.654 0 3.173-.447 4.506-1.23.177-.106.35-.218.519-.336a1 1 0 10-1.219-1.612c-.134.1-.268.2-.406.3-1.09.766-2.408 1.209-3.806 1.209-.42 0-.835-.04-1.24-.118-.327.073-.666.11-1.013.11-.982 0-1.793-.795-1.8-1.773-.008-.09-.01-.18-.01-.269 0-1.03.244-2.006.68-2.87-.313-.29-.64-.56-.977-.776-.604-.404-1.266-.72-1.964-.93-.504-.144-1.028-.226-1.567-.226-1.654 0-3.173.447-4.506 1.23z" clipRule="evenodd" />
            </svg>
          }
          trend={{ value: 8, direction: 'up' }}
        />
        <StatsCard
          label="Avg Score"
          value={`${stats.avgScore}%`}
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          }
        />
      </div>

      <div>
        <div className="mb-5">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-bold text-navy-900">Recent Calls</h2>
            <div className="flex gap-2 text-sm text-navy-500">
              {selectedGroup !== 'all' && (
                <span className="px-2 py-1 bg-navy-100 rounded">
                  Group: {userGroups.find(g => g.id === selectedGroup)?.name}
                </span>
              )}
              {selectedAgent !== 'all' && (
                <span className="px-2 py-1 bg-navy-100 rounded">
                  Agent: {allAgents.find(a => a.id === selectedAgent)?.name}
                </span>
              )}
              <span className="px-2 py-1 bg-navy-100 rounded">
                {formatDateRange(timeRange)}
              </span>
            </div>
          </div>
          <p className="text-navy-500 text-sm mt-1">{recentCalls.length} calls found</p>
        </div>
        <CallTable calls={recentCalls} />
      </div>
    </div>
  )
}
