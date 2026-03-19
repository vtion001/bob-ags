'use client'

import React from 'react'
import Link from 'next/link'
import { Call } from '@/lib/mockData'
import Card from './ui/Card'

export interface CallTableProps {
  calls: Call[]
  onCallClick?: (callId: string) => void
}

function formatTime(date: Date) {
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  return `${days}d ago`
}

function formatDuration(seconds: number) {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}m ${secs}s`
}

function getScoreBadgeColor(score?: number) {
  if (!score) return 'bg-slate-500/20 text-slate-400'
  if (score >= 75) return 'bg-red-500/20 text-red-400'
  if (score >= 50) return 'bg-amber-500/20 text-amber-400'
  return 'bg-slate-500/20 text-slate-400'
}

function getScoreLabel(score?: number) {
  if (!score) return 'Pending'
  if (score >= 75) return 'Hot'
  if (score >= 50) return 'Warm'
  return 'Cold'
}

export default function CallTable({ calls, onCallClick }: CallTableProps) {
  if (calls.length === 0) {
    return (
      <Card className="text-center py-12">
        <p className="text-slate-400">No calls yet</p>
      </Card>
    )
  }

  return (
    <Card hoverable={false} className="overflow-x-auto p-0">
      <table className="w-full">
        <thead className="bg-navy-900/50 border-b border-navy-700">
          <tr>
            <th className="text-left px-6 py-3 text-sm font-semibold text-slate-400">Time</th>
            <th className="text-left px-6 py-3 text-sm font-semibold text-slate-400">Phone</th>
            <th className="text-left px-6 py-3 text-sm font-semibold text-slate-400">Type</th>
            <th className="text-left px-6 py-3 text-sm font-semibold text-slate-400">Duration</th>
            <th className="text-left px-6 py-3 text-sm font-semibold text-slate-400">Score</th>
            <th className="text-left px-6 py-3 text-sm font-semibold text-slate-400">Status</th>
          </tr>
        </thead>
        <tbody>
          {calls.map((call, index) => (
            <tr
              key={call.id}
              className={`border-b border-navy-700/50 transition-colors duration-200 hover:bg-cyan-500/5 cursor-pointer ${
                index % 2 === 0 ? 'bg-navy-800/30' : ''
              }`}
              onClick={() => onCallClick?.(call.id)}
            >
              <td className="px-6 py-4 text-sm text-white">
                <Link href={`/dashboard/calls/${call.id}`}>
                  {formatTime(call.timestamp)}
                </Link>
              </td>
              <td className="px-6 py-4 text-sm text-cyan-400">
                <Link href={`/dashboard/calls/${call.id}`}>
                  {call.phone}
                </Link>
              </td>
              <td className="px-6 py-4 text-sm text-slate-400">
                <span className="capitalize">
                  {call.direction === 'inbound' ? '📞 Inbound' : '📤 Outbound'}
                </span>
              </td>
              <td className="px-6 py-4 text-sm text-white">
                {formatDuration(call.duration)}
              </td>
              <td className="px-6 py-4 text-sm">
                <span className={`px-3 py-1 rounded-full text-xs font-semibold ${getScoreBadgeColor(call.score)}`}>
                  {call.score ? `${Math.round(call.score)}%` : 'Pending'}
                </span>
              </td>
              <td className="px-6 py-4 text-sm">
                <span className="capitalize text-slate-400">
                  {call.status === 'completed' ? '✓ Done' : call.status === 'missed' ? '✕ Missed' : '⏱ Active'}
                </span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </Card>
  )
}
