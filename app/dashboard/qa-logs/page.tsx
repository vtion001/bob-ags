'use client'

import React, { useState, useEffect, useMemo } from 'react'
import Link from 'next/link'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import { useAuth } from '@/contexts/AuthContext'
import {
  ChevronUpIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClockIcon,
  UserIcon,
  PhoneIcon,
  StarIcon,
  RefreshCwIcon,
  FunnelIcon,
} from 'lucide-react'
import { cn } from '@/lib/utils'

interface QAOverride {
  id: string
  call_id: string
  ctm_call_id: string
  user_id: string
  overrides: OverrideItem[]
  manual_score: number
  ai_score: number
  score_change: number
  override_count: number
  created_at: string
  override_user_email: string
}

interface OverrideItem {
  criterionId: string
  criterion: string
  overridePass: boolean
  originalPass: boolean
  overrideNote?: string
}

type SortField = 'created_at' | 'call_id' | 'override_user_email' | 'ai_score' | 'manual_score' | 'score_change'
type SortDirection = 'asc' | 'desc'

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

function ScoreBadge({ score, className }: { score: number; className?: string }) {
  let bgClass = 'bg-red-50 text-red-600'
  if (score >= 85) bgClass = 'bg-green-50 text-green-700'
  else if (score >= 70) bgClass = 'bg-emerald-50 text-emerald-600'
  else if (score >= 50) bgClass = 'bg-amber-50 text-amber-600'

  return (
    <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold', bgClass, className)}>
      {Math.round(score)}
    </span>
  )
}

function ChangeBadge({ change }: { change: number }) {
  if (change === 0) return null
  
  const isPositive = change > 0
  return (
    <span className={cn(
      'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
      isPositive ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'
    )}>
      {isPositive ? '+' : ''}{change}
    </span>
  )
}

export default function QALogsPage() {
  const { role, isLoading: authLoading } = useAuth()
  const [overrides, setOverrides] = useState<QAOverride[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [total, setTotal] = useState(0)
  
  // Pagination
  const [page, setPage] = useState(0)
  const [pageSize, setPageSize] = useState(25)
  
  // Sorting
  const [sortField, setSortField] = useState<SortField>('created_at')
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc')
  
  // Filters
  const [showFilters, setShowFilters] = useState(false)
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')
  const [filterUser, setFilterUser] = useState('')

  const fetchOverrides = async () => {
    setIsLoading(true)
    setError(null)
    
    try {
      const params = new URLSearchParams({
        limit: String(pageSize),
        offset: String(page * pageSize),
      })
      
      if (dateFrom) params.append('dateFrom', dateFrom)
      if (dateTo) params.append('dateTo', dateTo)
      if (filterUser) params.append('userId', filterUser)
      
      const res = await fetch(`/api/qa-overrides?${params}`)
      const data = await res.json()
      
      if (!res.ok) {
        throw new Error(data.error || 'Failed to fetch overrides')
      }
      
      setOverrides(data.overrides || [])
      setTotal(data.total || 0)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load QA overrides')
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => {
    if (!authLoading && (role === 'admin' || role === 'qa')) {
      fetchOverrides()
    }
  }, [authLoading, role, page, pageSize])

  const sortedOverrides = useMemo(() => {
    return [...overrides].sort((a, b) => {
      let aVal: any = a[sortField]
      let bVal: any = b[sortField]
      
      if (sortField === 'created_at') {
        aVal = new Date(aVal).getTime()
        bVal = new Date(bVal).getTime()
      }
      
      if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1
      if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1
      return 0
    })
  }, [overrides, sortField, sortDirection])

  const handleSort = (field: SortField) => {
    if (sortField === field) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc')
    } else {
      setSortField(field)
      setSortDirection('desc')
    }
  }

  const handleApplyFilters = () => {
    setPage(0)
    fetchOverrides()
  }

  const handleResetFilters = () => {
    setDateFrom('')
    setDateTo('')
    setFilterUser('')
    setPage(0)
    fetchOverrides()
  }

  const totalPages = Math.ceil(total / pageSize)

  const SortHeader = ({ field, label }: { field: SortField; label: string }) => (
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
              QA Logs are only available to QA and Admin users. Please contact your administrator if you believe you need access.
            </p>
            <Link href="/dashboard">
              <Button variant="secondary">Return to Dashboard</Button>
            </Link>
          </div>
        </Card>
      </div>
    )
  }

  return (
    <div className="p-6 lg:p-8 max-w-7xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-navy-900 mb-2">QA Logs</h1>
        <p className="text-navy-500">Review manual QA overrides and score changes</p>
      </div>

      {error && (
        <Card className="p-4 mb-6 border-red-200 bg-red-50">
          <p className="text-red-600 font-medium">{error}</p>
          <Button variant="secondary" size="sm" onClick={fetchOverrides} className="mt-2">
            <RefreshCwIcon className="w-4 h-4 mr-1" /> Retry
          </Button>
        </Card>
      )}

      <Card className="p-4 mb-6">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-4">
            <h2 className="text-lg font-semibold text-navy-900">Filters</h2>
            <Button 
              variant="ghost" 
              size="sm" 
              onClick={() => setShowFilters(!showFilters)}
              className="md:hidden"
            >
              {showFilters ? 'Hide' : 'Show'}
            </Button>
          </div>
          <div className="flex items-center gap-2 text-sm text-navy-500">
            <span>Total: {total} override{total !== 1 ? 's' : ''}</span>
          </div>
        </div>

        <div className={cn('grid gap-4 md:grid-cols-4', !showFilters && 'hidden md:grid')}>
          <div>
            <label className="block text-sm font-medium text-navy-700 mb-1">Date From</label>
            <input
              type="date"
              value={dateFrom}
              onChange={(e) => setDateFrom(e.target.value)}
              className="w-full px-3 py-2 border border-navy-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-navy-700 mb-1">Date To</label>
            <input
              type="date"
              value={dateTo}
              onChange={(e) => setDateTo(e.target.value)}
              className="w-full px-3 py-2 border border-navy-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy-500"
            />
          </div>
          <div className="md:col-span-2 flex items-end gap-2">
            <Button variant="primary" size="sm" onClick={handleApplyFilters}>
              Apply Filters
            </Button>
            <Button variant="secondary" size="sm" onClick={handleResetFilters}>
              Reset
            </Button>
          </div>
        </div>
      </Card>

      <Card className="overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-navy-50 border-b border-navy-200">
              <tr>
                <SortHeader field="created_at" label="Date" />
                <SortHeader field="call_id" label="Call ID" />
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Override By
                </th>
                <SortHeader field="ai_score" label="AI Score" />
                <SortHeader field="manual_score" label="Manual Score" />
                <SortHeader field="score_change" label="Change" />
                <th className="px-4 py-3 text-left text-xs font-semibold text-navy-500 uppercase tracking-wider">
                  Criteria Overridden
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
                      <span>Loading QA overrides...</span>
                    </div>
                  </td>
                </tr>
              ) : sortedOverrides.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-4 py-12 text-center text-navy-500">
                    No QA overrides found
                  </td>
                </tr>
              ) : (
                sortedOverrides.map((override) => (
                  <tr key={override.id} className="hover:bg-navy-50/50 transition-colors">
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-2">
                        <ClockIcon className="w-4 h-4 text-navy-400" />
                        <div>
                          <p className="text-sm font-medium text-navy-900">{formatTimeAgo(override.created_at)}</p>
                          <p className="text-xs text-navy-400">{formatDate(override.created_at)}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1.5">
                        <PhoneIcon className="w-4 h-4 text-navy-400" />
                        <span className="text-sm font-mono text-navy-700">
                          {override.ctm_call_id || override.call_id}
                        </span>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1.5">
                        <UserIcon className="w-4 h-4 text-navy-400" />
                        <span className="text-sm text-navy-700 truncate max-w-[150px]">
                          {override.override_user_email}
                        </span>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <ScoreBadge score={override.ai_score || 0} />
                    </td>
                    <td className="px-4 py-3">
                      <ScoreBadge score={override.manual_score || 0} />
                    </td>
                    <td className="px-4 py-3">
                      <ChangeBadge change={override.score_change || 0} />
                    </td>
                    <td className="px-4 py-3">
                      <span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">
                        {override.override_count || override.overrides?.length || 0}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <Link href={`/dashboard/calls/${override.call_id}`}>
                        <Button variant="ghost" size="sm">
                          View Call
                        </Button>
                      </Link>
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