'use client'

import React, { useState, useEffect, useCallback } from 'react'
import { useRouter } from 'next/navigation'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'
import { Call } from '@/lib/ctm'

const KIEL_AGENT_ID = 'USR606009BC8AF41AD2856B590114A37B63'

export default function MonitorPage() {
  const router = useRouter()
  const [isMonitoring, setIsMonitoring] = useState(false)
  const [pollingInterval, setPollingInterval] = useState(3)
  const [activeCalls, setActiveCalls] = useState<Call[]>([])
  const [recentAnalyzed, setRecentAnalyzed] = useState<Call[]>([])
  const [error, setError] = useState<string | null>(null)

  const fetchActiveCalls = useCallback(async () => {
    try {
      const res = await fetch('/api/ctm/active-calls')
      if (!res.ok) throw new Error('Failed to fetch active calls')
      const data = await res.json()
      const allCalls = data.calls || []
      const kielCalls = allCalls.filter((call: Call) => call.agent?.id === KIEL_AGENT_ID)
      setActiveCalls(kielCalls)
      setError(null)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    }
  }, [])

  const fetchRecentAnalyzed = useCallback(async () => {
    try {
      const res = await fetch('/api/ctm/dashboard/stats?limit=10&hours=1')
      if (!res.ok) throw new Error('Failed to fetch recent analysis')
      const data = await res.json()
      setRecentAnalyzed(data.recentCalls?.filter((c: Call) => c.score !== undefined) || [])
    } catch (err) {
      console.error('Error fetching recent analysis:', err)
    }
  }, [])

  useEffect(() => {
    if (isMonitoring) {
      fetchActiveCalls()
      fetchRecentAnalyzed()
      const interval = setInterval(() => {
        fetchActiveCalls()
        fetchRecentAnalyzed()
      }, pollingInterval * 1000)
      return () => clearInterval(interval)
    }
  }, [isMonitoring, pollingInterval, fetchActiveCalls, fetchRecentAnalyzed])

  const formatDuration = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}:${secs.toString().padStart(2, '0')}`
  }

  const getScoreBadge = (score?: number) => {
    if (!score) return null
    if (score >= 75) return { label: 'Hot', className: 'bg-navy-900 text-white' }
    if (score >= 50) return { label: 'Warm', className: 'bg-amber-100 text-amber-800' }
    return { label: 'Cold', className: 'bg-slate-100 text-slate-600' }
  }

  const formatTimeAgo = (date: Date) => {
    const now = new Date()
    const diff = now.getTime() - new Date(date).getTime()
    const minutes = Math.floor(diff / 60000)
    if (minutes < 60) return `${minutes}m ago`
    const hours = Math.floor(diff / 3600000)
    return `${hours}h ago`
  }

  return (
    <div className="p-6 lg:p-8 max-w-4xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-navy-900 mb-2">Live Monitor</h1>
        <p className="text-navy-500">Monitor active calls in real-time</p>
      </div>

      <Card className="p-6 mb-6">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <div className={`relative`}>
              <div className={`w-3 h-3 rounded-full ${isMonitoring ? 'bg-emerald-500' : 'bg-slate-400'}`} />
              {isMonitoring && (
                <>
                  <span className="absolute inset-0 w-3 h-3 rounded-full bg-emerald-500 animate-ping" />
                  <span className="absolute inset-0 w-3 h-3 rounded-full bg-emerald-500 animate-pulse opacity-75" />
                </>
              )}
            </div>
            <div>
              <span className="text-navy-900 font-semibold block">
                {isMonitoring ? 'Monitoring Active' : 'Monitoring Disabled'}
              </span>
              {isMonitoring && (
                <span className="text-emerald-600 text-sm">Checking every {pollingInterval}s</span>
              )}
            </div>
          </div>
          <Button
            variant={isMonitoring ? 'secondary' : 'primary'}
            size="md"
            onClick={() => setIsMonitoring(!isMonitoring)}
          >
            {isMonitoring ? 'Stop Monitoring' : 'Start Monitoring'}
          </Button>
        </div>

        {error && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p className="text-red-600 text-sm">{error}</p>
          </div>
        )}

        <div className="bg-navy-50 rounded-lg p-4">
          <div className="flex items-center gap-4">
            <div className="flex-1">
              <label className="text-sm text-navy-700 mb-2 block">Polling Interval (seconds)</label>
              <input
                type="range"
                min="1"
                max="30"
                value={pollingInterval}
                onChange={(e) => setPollingInterval(parseInt(e.target.value))}
                className="w-full h-2 bg-navy-200 rounded-lg appearance-none cursor-pointer accent-navy-900"
              />
            </div>
            <Input
              type="number"
              min="1"
              max="60"
              value={pollingInterval}
              onChange={(e) => setPollingInterval(parseInt(e.target.value) || 3)}
              className="w-20"
            />
          </div>
        </div>
      </Card>

      <div className="mb-6">
        <div className="flex items-center gap-3 mb-4">
          <h2 className="text-xl font-bold text-navy-900">Active Calls</h2>
          {activeCalls.length > 0 && (
            <span className="px-2.5 py-0.5 rounded-full bg-navy-900 text-white text-xs font-semibold">
              {activeCalls.length} LIVE
            </span>
          )}
        </div>

        {activeCalls.length > 0 ? (
          <div className="grid gap-4">
            {activeCalls.map(call => (
              <Card key={call.id} className="p-6">
                <div className="flex items-start justify-between mb-4">
                  <div>
                    <h3 className="text-2xl font-bold text-navy-900 font-mono">{call.phone}</h3>
                    <p className="text-navy-500 text-sm mt-1">Duration: {formatDuration(call.duration)}</p>
                  </div>
                  <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-navy-900 text-white">
                    <span className="w-2 h-2 rounded-full bg-white animate-pulse" />
                    <span className="font-semibold text-sm uppercase tracking-wide">Live</span>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div className="bg-navy-50 rounded-lg p-4">
                    <p className="text-navy-500 text-sm">Call Type</p>
                    <p className="text-navy-900 font-semibold capitalize">{call.direction}</p>
                  </div>
                  <div className="bg-navy-50 rounded-lg p-4">
                    <p className="text-navy-500 text-sm">Status</p>
                    <p className="text-navy-900 font-semibold capitalize">{call.status}</p>
                  </div>
                </div>

                <div className="flex gap-3">
                  <Button 
                    variant="primary" 
                    size="md" 
                    className="flex-1"
                    onClick={() => router.push(`/dashboard/calls/${call.id}`)}
                  >
                    View Details
                  </Button>
                  <Button variant="secondary" size="md" className="flex-1">
                    Quick Note
                  </Button>
                </div>
              </Card>
            ))}
          </div>
        ) : (
          <Card className="p-12 text-center">
            <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-navy-100 mb-4">
              <svg className="w-8 h-8 text-navy-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <h3 className="text-lg font-semibold text-navy-900 mb-1">No active calls</h3>
            <p className="text-navy-500 text-sm">Active calls will appear here when detected</p>
          </Card>
        )}
      </div>

      <div>
        <div className="flex items-center gap-3 mb-4">
          <h2 className="text-xl font-bold text-navy-900">Recent Analysis</h2>
          <span className="px-2 py-0.5 rounded-full bg-navy-100 text-navy-600 text-xs font-medium">
            Last hour
          </span>
        </div>
        <Card className="p-4">
          {recentAnalyzed.length > 0 ? (
            <div className="space-y-2">
              {recentAnalyzed.map(call => {
                const badge = getScoreBadge(call.score)
                return (
                  <div 
                    key={call.id} 
                    className="flex items-center justify-between p-3 rounded-lg bg-navy-50 hover:bg-navy-100 cursor-pointer transition-colors"
                    onClick={() => router.push(`/dashboard/calls/${call.id}`)}
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                        <svg className="w-4 h-4 text-navy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                      </div>
                      <div>
                        <p className="text-navy-900 font-medium">{call.phone}</p>
                        <p className="text-navy-500 text-xs">{formatTimeAgo(call.timestamp)}</p>
                      </div>
                    </div>
                    {badge && (
                      <span className={`px-2.5 py-1 text-xs rounded-full font-semibold ${badge.className}`}>
                        {badge.label}
                      </span>
                    )}
                  </div>
                )
              })}
            </div>
          ) : (
            <p className="text-navy-400 text-center py-4">No recent analysis available</p>
          )}
        </Card>
      </div>
    </div>
  )
}
