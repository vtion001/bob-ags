import React from 'react'

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string
  error?: string
  hint?: string
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, label, error, hint, ...props }, ref) => {
    return (
      <div className="w-full">
        {label && (
          <label className="block text-sm font-medium text-white mb-2">
            {label}
          </label>
        )}
        <input
          ref={ref}
          className={`w-full px-4 py-2.5 bg-navy-900 border border-navy-600 rounded-sm text-white placeholder-slate-500 transition-all duration-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none ${
            error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
          } ${className || ''}`}
          {...props}
        />
        {error && (
          <p className="text-xs text-red-500 mt-1">{error}</p>
        )}
        {hint && !error && (
          <p className="text-xs text-slate-400 mt-1">{hint}</p>
        )}
      </div>
    )
  }
)

Input.displayName = 'Input'

export default Input
