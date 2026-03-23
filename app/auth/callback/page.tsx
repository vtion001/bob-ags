'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'

type AuthStatus = 'loading' | 'success' | 'denied' | 'error'

export default function AuthCallback() {
  const router = useRouter()
  const supabase = createClient()
  const [status, setStatus] = useState<AuthStatus>('loading')
  const [message, setMessage] = useState('')

  useEffect(() => {
    const handleAuthCallback = async () => {
      try {
        const { data: { user }, error } = await supabase.auth.getUser()

        if (error) {
          console.error('Auth callback error:', error)
          setStatus('error')
          setMessage('Authentication failed. Please try again.')
          setTimeout(() => router.push('/'), 5000)
          return
        }

        if (!user) {
          setStatus('error')
          setMessage('No user found. Please sign up.')
          setTimeout(() => router.push('/'), 5000)
          return
        }

        const response = await fetch('/api/auth/agent-lookup', {
          method: 'POST',
        })

        const result = await response.json()

        if (result.status === 'deny' || result.status === 'manual') {
          setStatus('denied')
          setMessage(result.message)
          await supabase.auth.signOut()
          setTimeout(() => router.push('/'), 8000)
          return
        }

        if (result.status === 'error') {
          setStatus('error')
          setMessage(result.message || 'Failed to process sign-in. Please try again.')
          setTimeout(() => router.push('/'), 5000)
          return
        }

        setStatus('success')
        router.push('/dashboard')
      } catch (err) {
        console.error('Unexpected error in auth callback:', err)
        setStatus('error')
        setMessage('An unexpected error occurred. Please try again.')
        setTimeout(() => router.push('/'), 5000)
      }
    }

    handleAuthCallback()
  }, [router, supabase])

  return (
    <div className="min-h-screen flex items-center justify-center bg-white">
      <div className="flex flex-col items-center gap-4 max-w-md text-center px-6">
        {status === 'loading' && (
          <>
            <div className="w-12 h-12 border-4 border-navy-100 border-t-navy-900 rounded-full animate-spin" />
            <p className="text-navy-500 font-medium">Completing sign in...</p>
            <p className="text-navy-400 text-sm">Looking up your agent account...</p>
          </>
        )}

        {status === 'success' && (
          <>
            <div className="w-12 h-12 border-4 border-green-100 border-t-green-600 rounded-full animate-spin" />
            <p className="text-green-600 font-medium">Agent account found!</p>
            <p className="text-navy-500 text-sm">Redirecting to dashboard...</p>
          </>
        )}

        {status === 'denied' && (
          <>
            <div className="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center">
              <svg className="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p className="text-red-600 font-semibold text-lg">Access Denied</p>
            <p className="text-navy-600 text-sm">{message}</p>
            <p className="text-navy-400 text-xs mt-2">Redirecting to home page in a moment...</p>
          </>
        )}

        {status === 'error' && (
          <>
            <div className="w-16 h-16 rounded-full bg-orange-50 flex items-center justify-center">
              <svg className="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <p className="text-orange-600 font-semibold text-lg">Sign In Issue</p>
            <p className="text-navy-600 text-sm">{message}</p>
            <p className="text-navy-400 text-xs mt-2">Redirecting to home page...</p>
          </>
        )}
      </div>
    </div>
  )
}
