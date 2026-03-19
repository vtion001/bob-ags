import React from 'react'
import Card from './ui/Card'

export interface EmptyStateProps {
  icon?: React.ReactNode
  title: string
  description: string
  action?: {
    label: string
    onClick: () => void
  }
}

export default function EmptyState({ icon, title, description, action }: EmptyStateProps) {
  return (
    <Card className="py-16 px-6 text-center flex flex-col items-center gap-4">
      {icon ? (
        <div className="w-16 h-16 rounded-full bg-cyan-500/10 flex items-center justify-center text-cyan-500">
          {icon}
        </div>
      ) : (
        <div className="w-16 h-16 rounded-full bg-slate-500/10 flex items-center justify-center">
          <svg className="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
        </div>
      )}
      <div>
        <h3 className="text-lg font-bold text-white mb-1">{title}</h3>
        <p className="text-slate-400 text-sm">{description}</p>
      </div>
      {action && (
        <button
          onClick={action.onClick}
          className="mt-4 px-4 py-2 bg-cyan-500 text-navy-900 rounded-xs font-medium hover:bg-cyan-400 transition-colors"
        >
          {action.label}
        </button>
      )}
    </Card>
  )
}
