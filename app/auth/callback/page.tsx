'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { createClient } from '@/lib/supabase/client'

export default function AuthCallback() {
  const router = useRouter()
  const supabase = createClient()

  useEffect(() => {
    const handleAuthCallback = async () => {
      try {
        const { data: { user }, error } = await supabase.auth.getUser()
        
        if (error) {
          console.error('Auth callback error:', error)
          router.push('/?error=auth_callback_error')
          return
        }

        if (user) {
          router.push('/dashboard')
        } else {
          router.push('/')
        }
      } catch (err) {
        console.error('Unexpected error in auth callback:', err)
        router.push('/?error=unexpected_error')
      }
    }

    handleAuthCallback()
  }, [router, supabase])

  return (
    <div className="min-h-screen flex items-center justify-center bg-white">
      <div className="flex flex-col items-center gap-4">
        <div className="w-12 h-12 border-4 border-navy-100 border-t-navy-900 rounded-full animate-spin" />
        <p className="text-navy-500 font-medium">Completing sign in...</p>
      </div>
    </div>
  )
}
