'use client'

import React from 'react'

export interface ScoreCircleProps {
  score: number
  label?: string
  size?: 'sm' | 'md' | 'lg'
}

export default function ScoreCircle({ score, label = 'Score', size = 'md' }: ScoreCircleProps) {
  const validScore = score && !isNaN(score) ? score : 0
  
  const getColorStyles = () => {
    if (validScore >= 75) {
      return 'border-red-500 bg-red-500/10'
    } else if (validScore >= 50) {
      return 'border-amber-500 bg-amber-500/10'
    } else {
      return 'border-slate-500 bg-slate-500/10'
    }
  }

  const getLabel = () => {
    if (validScore >= 75) return 'Hot'
    if (validScore >= 50) return 'Warm'
    return 'Cold'
  }

  const sizeMap = {
    sm: { container: 'w-20 h-20', border: 'border-2', text: 'text-2xl' },
    md: { container: 'w-32 h-32', border: 'border-4', text: 'text-5xl' },
    lg: { container: 'w-40 h-40', border: 'border-4', text: 'text-6xl' },
  }

  const sizeConfig = sizeMap[size]

  return (
    <div className="flex flex-col items-center gap-3">
      <div
        className={`${sizeConfig.container} ${sizeConfig.border} ${getColorStyles()} rounded-full flex items-center justify-center`}
      >
        <span className={`${sizeConfig.text} font-bold text-navy-900`}>{validScore > 0 ? Math.round(validScore) : '-'}</span>
      </div>
      <div className="text-center">
        <p className="text-sm text-navy-500">{label}</p>
        <p className="text-lg font-semibold text-navy-900">{getLabel()}</p>
      </div>
    </div>
  )
}
