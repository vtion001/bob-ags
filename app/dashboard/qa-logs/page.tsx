'use client'

import React, { useState, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import { useAuth } from '@/contexts/AuthContext'
import {
  ChevronUpIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClockIcon,
  PhoneIcon,
  RefreshCwIcon,
  FunnelIcon,
} from 'lucide-react'
import { cn } from '@/lib/utils'

interface CallRecord {
  id: string
  ctm_call_id: string
  phone: string
  direction: string
  duration: number
  score: number | null
  sentiment: string | null
  created_at: string
  agent_name: string | null
  disposition: string | null
}

const PAGE_SIZE_OPTIONS = [10, 25, 50, 100]

function formatDate(dateStr: string) {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}

function formatTimeAgo(dateStr: string) {
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  return `${days}d ago`
}

function ScoreBadge({ score }: { score: number | null }) {
  if (score === null) {
    return (
      <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-navy-100 text-navy-600">
        N/A
      </span>
    )
  }
  
  let bgClass = 'bg-red-50 text-red-600'
  if (score >= 85) bgClass = 'bg-green-50 text-green-700'
  else if (score >= 70) bgClass = 'bg-emerald-50 text-emerald-600'
  else if (score >= 50) bgClass = 'bg-amber-50 text-amber-600'

  return (
    <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold', bgClass)}>
      {Math.round(score)}
    </span>
  )
}

export default function QALogsPage() {
  const router = useRouter()
  const { role, isLoading: authLoading } = useAuth()
  const [calls, setCalls] = useState<CallRecord[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [total, setTotal] = useState(0)
  
  const [page, setPage] = useState(0)
  const [pageSize, setPageSize] = useState(25)
  const [sortField, setSortField] = useState<'created_at' | 'score'>('created_at')
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('desc')

  const fetchCalls = async () => {
    setIsLoading(true)
    setError(null)
    
    try {
      const params = new URLSearchParams({
        limit: String(pageSize),
        offset: String(page * pageSize),
      })
      
      const res = await fetch(`/api/qa-overrides?${params}`)
      const data = await res.json()
      
      if (!res.ok) {
        throw new Error(data.error || 'Failed to fetch calls')
      }
      
      setCalls(data.calls || [])
      setTotal(data.total || 0)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load calls')
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => {
    if (!authLoading && (role === 'admin' || role === 'qa')) {
      fetchCalls()
    }
  }, [authLoading, role, page, pageSize])

  const sortedCalls = [...calls].sort((a, b) => {
    let aVal = sortField === 'created_at' 
      ? new Date(a.created_at).getTime() 
      : (a.score || 0)
    let bVal = sortField === 'created_at' 
      ? new Date(b.created_at).getTime() 
      : (b.score || 0)
    
    if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1
    if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1
    return 0
  })

  const handleSort = (field: 'created_at' | 'score') => {
    if (sortField === field) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc')
    } else {
      setSortField(field)
      setSortDirection('desc')
    }
  }

  const totalPages = Math.ceil(total / pageSize)

  const SortHeader = ({ field, label }: { field: 'created_at' | 'score'; label: string }) => (
    <th 
      className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider cursor-pointer hover:bg-navy-50 transition-colors"
      onClick={() => handleSort(field)}
    >
      <div className="flex items-center gap-1">
        {label}
        {sortField === field ? (
          sortDirection === 'asc' ? (
            <ChevronUpIcon className="w-4 h-4" />
          ) : (
            <ChevronDownIcon className="w-4 h-4" />
          )
        ) : (
          <ChevronUpIcon className="w-4 h-4 opacity-30" />
        )}
      </div>
    </th>
  )

  if (authLoading) {
    return (
      <div className="p-6 lg:p-8 max-w-7xl mx-auto">
        <div className="animate-pulse space-y-4">
          <div className="h-8 bg-navy-100 rounded w-48"></div>
          <div className="h-4 bg-navy-100 rounded w-64"></div>
          <div className="h-96 bg-navy-100 rounded mt-8"></div>
        </div>
      </div>
    )
  }

  if (role !== 'admin' && role !== 'qa') {
    return (
      <div className="p-6 lg:p-8 max-w-7xl mx-auto">
        <Card className="p-12 text-center">
          <div className="flex flex-col items-center gap-4">
            <div className="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center">
              <FunnelIcon className="w-8 h-8 text-amber-500" />
            </div>
            <h2 className="text-xl font-bold text-navy-900">Access Restricted</h2>
            <p className="text-navy-500 max-w-md">
              QA Logs are only available to QA and Admin users.
            </p>
            <Button variant="secondary" onClick={() => router.push('/dashboard')}>
              Return to Dashboard
            </Button>
          </div>
        </Card>
      </div>
    )
  }

  return (
    <div className="p-6 lg:p-8 max-w-7xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-navy-900 mb-2">QA Logs</h1>
        <p className="text-navy-500">Review analyzed calls with AI scoring</p>
      </div>

      {error && (
        <Card className="p-4 mb-6 border-red-200 bg-red-50">
          <p className="text-red-600 font-medium">{error}</p>
          <Button variant="secondary" size="sm" onClick={fetchCalls} className="mt-2">
            <RefreshCwIcon className="w-4 h-4 mr-1" /> Retry
          </Button>
        </Card>
      )}

      <Card className="overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-navy-50 border-b border-navy-200">
              <tr>
                <SortHeader field="created_at" label="Date" />
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Call ID
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Phone
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Direction
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Agent
                </th>
                <SortHeader field="score" label="AI Score" />
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Sentiment
                </th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Details
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-navy-100">
              {isLoading ? (
                <tr>
                  <td colSpan={8} className="px-4 py-12 text-center">
                    <div className="flex justify-center items-center gap-2 text-navy-500">
                      <RefreshCwIcon className="w-5 h-5 animate-spin" />
                      <span>Loading analyzed calls...</span>
                    </div>
                  </td>
                </tr>
              ) : sortedCalls.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-4 py-12 text-center text-navy-500">
                    No analyzed calls found
                  </td>
                </tr>
              ) : (
                sortedCalls.map((call) => (
                  <tr key={call.id} className="hover:bg-navy-50/50 transition-colors">
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-2">
                        <ClockIcon className="w-4 h-4 text-navy-400" />
                        <div>
                          <p className="text-sm font-medium text-navy-900">{formatTimeAgo(call.created_at)}</p>
                          <p className="text-xs text-navy-400">{formatDate(call.created_at)}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <span className="text-sm font-mono text-navy-700">
                        {call.ctm_call_id || call.id.slice(0, 8)}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1.5">
                        <PhoneIcon className="w-4 h-4 text-navy-400" />
                        <span className="text-sm text-navy-700">{call.phone || 'N/A'}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <span className={cn(
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                        call.direction === 'inbound' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'
                      )}>
                        {call.direction || 'N/A'}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <span className="text-sm text-navy-700">{call.agent_name || 'Unknown'}</span>
                    </td>
                    <td className="px-4 py-3">
                      <ScoreBadge score={call.score} />
                    </td>
                    <td className="px-4 py-3">
                      <span className="text-sm text-navy-700 capitalize">{call.sentiment || 'N/A'}</span>
                    </td>
                    <td className="px-4 py-3">
                      <Button variant="ghost" size="sm" onClick={() => router.push(`/dashboard/calls/${call.id}`)}>
                        View
                      </Button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {totalPages > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-navy-200 bg-navy-50">
            <div className="flex items-center gap-4">
              <span className="text-sm text-navy-500">
                Showing {page * pageSize + 1} to {Math.min((page + 1) * pageSize, total)} of {total}
              </span>
              <select
                value={pageSize}
                onChange={(e) => {
                  setPageSize(Number(e.target.value))
                  setPage(0)
                }}
                className="px-2 py-1 border border-navy-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-navy-500"
              >
                {PAGE_SIZE_OPTIONS.map((size) => (
                  <option key={size} value={size}>
                    {size} per page
                  </option>
                ))}
              </select>
            </div>
            <div className="flex items-center gap-2">
              <Button
                variant="secondary"
                size="sm"
                onClick={() => setPage(page - 1)}
                disabled={page === 0}
              >
                <ChevronLeftIcon className="w-4 h-4 mr-1" />
                Previous
              </Button>
              <span className="text-sm text-navy-500">
                Page {page + 1} of {totalPages}
              </span>
              <Button
                variant="secondary"
                size="sm"
                onClick={() => setPage(page + 1)}
                disabled={page >= totalPages - 1}
              >
                Next
                <ChevronRightIcon className="w-4 h-4 ml-1" />
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  )
}