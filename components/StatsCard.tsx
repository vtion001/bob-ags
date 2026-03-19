import React from 'react'
import Card from './ui/Card'

export interface StatsCardProps {
  label: string
  value: string | number
  icon?: React.ReactNode
  trend?: {
    value: number
    direction: 'up' | 'down'
  }
}

export default function StatsCard({ label, value, icon, trend }: StatsCardProps) {
  return (
    <Card hoverable className="flex items-center gap-4 p-5">
      {icon && (
        <div className="flex-shrink-0 w-12 h-12 bg-cyan-500/10 rounded-lg flex items-center justify-center text-cyan-500">
          {icon}
        </div>
      )}
      <div className="flex-1">
        <p className="text-slate-400 text-sm">{label}</p>
        <p className="text-3xl font-bold text-white mt-1">{value}</p>
        {trend && (
          <p className={`text-xs mt-2 ${trend.direction === 'up' ? 'text-green-500' : 'text-red-500'}`}>
            {trend.direction === 'up' ? '↑' : '↓'} {Math.abs(trend.value)}%
          </p>
        )}
      </div>
    </Card>
  )
}
