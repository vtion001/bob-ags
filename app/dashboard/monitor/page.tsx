'use client'

import React, { useState } from 'react'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'

export default function MonitorPage() {
  const [isMonitoring, setIsMonitoring] = useState(true)
  const [pollingInterval, setPollingInterval] = useState(3)
  const [activeCall, setActiveCall] = useState<{
    phone: string
    duration: number
    timestamp: Date
  } | null>({
    phone: '+1 (555) 123-4567',
    duration: 145,
    timestamp: new Date(),
  })

  const formatDuration = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}:${secs.toString().padStart(2, '0')}`
  }

  return (
    <div className="p-6 lg:p-8 max-w-3xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-white mb-2">Live Monitor</h1>
        <p className="text-slate-400">Monitor active calls in real-time</p>
      </div>

      {/* Status Indicator */}
      <Card className="p-6 mb-6">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <div className={`w-3 h-3 rounded-full ${isMonitoring ? 'bg-green-500 animate-pulse' : 'bg-slate-500'}`}></div>
            <span className="text-white font-semibold">
              {isMonitoring ? 'Monitoring Active' : 'Monitoring Disabled'}
            </span>
          </div>
          <Button
            variant={isMonitoring ? 'secondary' : 'primary'}
            size="sm"
            onClick={() => setIsMonitoring(!isMonitoring)}
          >
            {isMonitoring ? 'Stop' : 'Start'} Monitoring
          </Button>
        </div>

        {/* Settings */}
        <div className="bg-navy-900/50 rounded-lg p-4 space-y-4">
          <div>
            <label className="text-sm text-slate-400 mb-2 block">Polling Interval (seconds)</label>
            <div className="flex items-center gap-2">
              <Input
                type="number"
                min="1"
                max="60"
                value={pollingInterval}
                onChange={(e) => setPollingInterval(parseInt(e.target.value))}
                className="w-20"
              />
              <span className="text-slate-400 text-sm">seconds</span>
            </div>
            <p className="text-xs text-slate-500 mt-2">Default: 3 seconds</p>
          </div>
        </div>
      </Card>

      {/* Active Calls */}
      <div className="mb-6">
        <h2 className="text-xl font-bold text-white mb-4">Active Calls</h2>
        {activeCall ? (
          <Card className="p-6 bg-gradient-to-br from-navy-800/50 to-navy-900/50 border-cyan-500/30">
            <div className="flex items-start justify-between mb-6">
              <div>
                <h3 className="text-2xl font-bold text-cyan-400 font-mono">{activeCall.phone}</h3>
                <p className="text-slate-400 text-sm mt-1">Duration: {formatDuration(activeCall.duration)}</p>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                <span className="text-red-400 font-semibold text-sm">LIVE</span>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4 mb-6">
              <div className="bg-navy-900/50 rounded-lg p-4">
                <p className="text-slate-400 text-sm">Call Type</p>
                <p className="text-white font-semibold mt-1">Inbound</p>
              </div>
              <div className="bg-navy-900/50 rounded-lg p-4">
                <p className="text-slate-400 text-sm">Status</p>
                <p className="text-cyan-400 font-semibold mt-1">Connected</p>
              </div>
            </div>

            <div className="flex gap-2">
              <Button variant="primary" size="md" className="flex-1">
                View Details
              </Button>
              <Button variant="secondary" size="md" className="flex-1">
                Quick Note
              </Button>
            </div>
          </Card>
        ) : (
          <Card className="p-12 text-center">
            <div className="flex justify-center mb-4">
              <div className="p-3 bg-slate-500/10 rounded-lg">
                <svg className="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p className="text-slate-400 text-lg">No active calls at the moment</p>
            <p className="text-slate-500 text-sm mt-1">Active calls will appear here when detected</p>
          </Card>
        )}
      </div>

      {/* Recent Analysis */}
      <div>
        <h2 className="text-xl font-bold text-white mb-4">Recent Analysis</h2>
        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center justify-between p-3 bg-navy-900/50 rounded-lg">
              <div>
                <p className="text-white font-medium">+1 (555) 456-7890</p>
                <p className="text-slate-400 text-sm">2 minutes ago</p>
              </div>
              <span className="px-3 py-1 bg-red-500/20 text-red-400 text-xs rounded-full font-semibold">
                Hot
              </span>
            </div>
            <div className="flex items-center justify-between p-3 bg-navy-900/50 rounded-lg">
              <div>
                <p className="text-white font-medium">+1 (555) 567-8901</p>
                <p className="text-slate-400 text-sm">5 minutes ago</p>
              </div>
              <span className="px-3 py-1 bg-amber-500/20 text-amber-400 text-xs rounded-full font-semibold">
                Warm
              </span>
            </div>
            <div className="flex items-center justify-between p-3 bg-navy-900/50 rounded-lg">
              <div>
                <p className="text-white font-medium">+1 (555) 678-9012</p>
                <p className="text-slate-400 text-sm">8 minutes ago</p>
              </div>
              <span className="px-3 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full font-semibold">
                Cold
              </span>
            </div>
          </div>
        </Card>
      </div>

      {/* Notification */}
      {activeCall && (
        <div className="fixed bottom-4 right-4 bg-green-500/20 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg flex items-center gap-2 animate-pulse">
          <div className="w-2 h-2 bg-green-500 rounded-full"></div>
          <span>New analysis ready</span>
        </div>
      )}
    </div>
  )
}
