import React from 'react'

export interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  hoverable?: boolean
}

const Card = React.forwardRef<HTMLDivElement, CardProps>(
  ({ className, hoverable = true, ...props }, ref) => {
    const baseStyles = 'bg-navy-800/50 border border-navy-700 rounded-lg p-6 backdrop-blur-sm transition-all duration-200'
    const hoverStyles = hoverable ? 'hover:border-cyan-500/50 hover:shadow-glow-cyan' : ''
    
    return (
      <div
        ref={ref}
        className={`${baseStyles} ${hoverStyles} ${className || ''}`}
        {...props}
      />
    )
  }
)

Card.displayName = 'Card'

export default Card
