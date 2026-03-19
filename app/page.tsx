'use client'

import React, { useState } from 'react'
import { useRouter } from 'next/navigation'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Card from '@/components/ui/Card'

export default function LoginPage() {
  const router = useRouter()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [isLoading, setIsLoading] = useState(false)

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setIsLoading(true)

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      })

      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || 'Login failed')
      }

      router.push('/dashboard')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setIsLoading(false)
    }
  }

  const handleDemoMode = () => {
    router.push('/dashboard')
  }

  return (
    <div className="min-h-screen bg-navy-950 flex">
      {/* Left side - Brand */}
      <div className="hidden lg:flex lg:w-1/2 flex-col justify-center items-center p-12 bg-gradient-to-br from-navy-900 to-navy-950">
        <div className="max-w-md text-center">
          <div className="mb-8">
            <div className="inline-block p-4 bg-cyan-500/10 rounded-xl">
              <div className="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-400 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-navy-950" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                </svg>
              </div>
            </div>
          </div>
          <h1 className="text-4xl font-bold text-white mb-4">Mission Control</h1>
          <p className="text-xl text-cyan-400 mb-6">Transform calls into insights</p>
          <p className="text-slate-400 leading-relaxed">
            AI-powered call analysis dashboard for sales teams. Track, analyze, and optimize every conversation.
          </p>
        </div>
      </div>

      {/* Right side - Login Form */}
      <div className="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 sm:p-12">
        <div className="w-full max-w-md">
          <div className="mb-8">
            <h2 className="text-3xl font-bold text-white mb-2">Welcome Back</h2>
            <p className="text-slate-400">Enter your credentials to continue</p>
          </div>

          <Card hoverable={false} className="mb-6">
            <form onSubmit={handleLogin} className="space-y-4">
              <Input
                label="Email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="you@example.com"
                required
                disabled={isLoading}
              />
              <Input
                label="Password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                required
                disabled={isLoading}
              />
              {error && <p className="text-sm text-red-500 -mt-2">{error}</p>}
              <Button
                type="submit"
                variant="primary"
                size="lg"
                className="w-full"
                isLoading={isLoading}
                disabled={isLoading}
              >
                Sign In
              </Button>
            </form>
          </Card>

          <div className="relative mb-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-navy-700"></div>
            </div>
            <div className="relative flex justify-center text-sm">
              <span className="px-2 bg-navy-950 text-slate-400">or</span>
            </div>
          </div>

          <Button
            onClick={handleDemoMode}
            variant="secondary"
            size="lg"
            className="w-full"
            disabled={isLoading}
          >
            Try Demo Mode
          </Button>

          <p className="text-center text-slate-400 text-sm mt-6">
            Demo credentials: demo@example.com / demo
          </p>
        </div>
      </div>
    </div>
  )
}
