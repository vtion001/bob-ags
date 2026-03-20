import React from 'react'
import Card from '@/components/ui/Card'
import { Call } from '@/lib/ctm'

interface CallerInfoCardProps {
  call: Call
  formatDuration?: (seconds: number) => string
}

export default function CallerInfoCard({ call, formatDuration }: CallerInfoCardProps) {
  const defaultFormatDuration = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}m ${secs}s`
  }

  const format = formatDuration || defaultFormatDuration

  return (
    <Card className="p-6">
      <h3 className="text-sm font-semibold text-navy-500 mb-4">Caller Info</h3>
      <div className="space-y-4">
        {call.callerNumber && (
          <div>
            <p className="text-xs text-navy-400 uppercase">Phone</p>
            <p className="text-navy-900 font-mono mt-1">{call.callerNumber}</p>
          </div>
        )}
        <div>
          <p className="text-xs text-navy-400 uppercase">Duration</p>
          <p className="text-navy-900 mt-1">{format(call.duration)}</p>
        </div>
        <div>
          <p className="text-xs text-navy-400 uppercase">Direction</p>
          <p className="text-navy-900 capitalize mt-1">{call.direction}</p>
        </div>
        <div>
          <p className="text-xs text-navy-400 uppercase">Status</p>
          <p className={`capitalize mt-1 ${
            call.status === 'completed' ? 'text-emerald-600' :
            call.status === 'missed' ? 'text-red-600' :
            'text-amber-600'
          }`}>
            {call.status}
          </p>
        </div>
        {call.city && (
          <div>
            <p className="text-xs text-navy-400 uppercase">Location</p>
            <p className="text-navy-900 mt-1">{call.city}, {call.state}</p>
          </div>
        )}
        {call.trackingLabel && (
          <div>
            <p className="text-xs text-navy-400 uppercase">Source</p>
            <p className="text-navy-900 mt-1">{call.trackingLabel}</p>
          </div>
        )}
      </div>
    </Card>
  )
}
