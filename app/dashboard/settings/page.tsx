'use client'

import React, { useState } from 'react'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'

export default function SettingsPage() {
  const [credentials, setCredentials] = useState({
    ctmAccessKey: '',
    ctmSecretKey: '',
    ctmAccountId: '',
    openrouterKey: '',
  })
  const [isSaving, setIsSaving] = useState(false)
  const [saveMessage, setSaveMessage] = useState('')

  const handleChange = (field: string, value: string) => {
    setCredentials(prev => ({
      ...prev,
      [field]: value,
    }))
  }

  const handleSave = async () => {
    setIsSaving(true)
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      setSaveMessage('Settings saved successfully')
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (error) {
      setSaveMessage('Error saving settings')
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <div className="p-6 lg:p-8 max-w-3xl mx-auto">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-white mb-2">Settings</h1>
        <p className="text-slate-400">Manage your integrations and preferences</p>
      </div>

      {/* API Credentials Section */}
      <Card className="p-6 mb-6">
        <h2 className="text-xl font-bold text-white mb-6">CTM Integrations</h2>
        
        <div className="space-y-4 mb-6">
          <Input
            label="CTM Access Key"
            type="password"
            value={credentials.ctmAccessKey}
            onChange={(e) => handleChange('ctmAccessKey', e.target.value)}
            placeholder="Enter your access key"
            hint="Your CallTrackingMetrics API access key"
          />
          
          <Input
            label="CTM Secret Key"
            type="password"
            value={credentials.ctmSecretKey}
            onChange={(e) => handleChange('ctmSecretKey', e.target.value)}
            placeholder="Enter your secret key"
            hint="Your CallTrackingMetrics API secret key"
          />

          <Input
            label="CTM Account ID"
            type="text"
            value={credentials.ctmAccountId}
            onChange={(e) => handleChange('ctmAccountId', e.target.value)}
            placeholder="Enter your account ID"
            hint="Your CallTrackingMetrics account ID"
          />

          <Input
            label="Default Client"
            type="text"
            defaultValue="flyland"
            placeholder="Enter default client"
            hint="Default client for API requests"
          />
        </div>

        <Button variant="primary" size="md" onClick={handleSave} isLoading={isSaving}>
          Save CTM Settings
        </Button>
      </Card>

      {/* AI Configuration Section */}
      <Card className="p-6 mb-6">
        <h2 className="text-xl font-bold text-white mb-6">AI Analysis</h2>
        
        <div className="space-y-4 mb-6">
          <Input
            label="OpenRouter API Key"
            type="password"
            value={credentials.openrouterKey}
            onChange={(e) => handleChange('openrouterKey', e.target.value)}
            placeholder="Enter your OpenRouter API key"
            hint="API key for AI-powered analysis"
          />
        </div>

        <Button variant="primary" size="md" onClick={handleSave} isLoading={isSaving}>
          Save AI Settings
        </Button>
      </Card>

      {/* Preferences Section */}
      <Card className="p-6 mb-6">
        <h2 className="text-xl font-bold text-white mb-6">Preferences</h2>
        
        <div className="space-y-4">
          <div className="flex items-center justify-between p-3 rounded-lg bg-navy-900/50">
            <div>
              <p className="text-white font-medium">Dark Mode</p>
              <p className="text-sm text-slate-400">Always enabled</p>
            </div>
            <div className="w-14 h-8 bg-cyan-500 rounded-full flex items-center justify-end pr-1">
              <div className="w-6 h-6 bg-white rounded-full"></div>
            </div>
          </div>

          <div className="flex items-center justify-between p-3 rounded-lg bg-navy-900/50">
            <div>
              <p className="text-white font-medium">Email Notifications</p>
              <p className="text-sm text-slate-400">Receive notifications for hot leads</p>
            </div>
            <div className="w-14 h-8 bg-navy-600 rounded-full flex items-center justify-start pl-1">
              <div className="w-6 h-6 bg-slate-400 rounded-full"></div>
            </div>
          </div>
        </div>
      </Card>

      {/* Danger Zone */}
      <Card className="p-6 border-red-500/30 bg-red-500/5">
        <h2 className="text-xl font-bold text-red-400 mb-4">Danger Zone</h2>
        <p className="text-slate-400 mb-4">
          Clear all stored credentials. This action cannot be undone.
        </p>
        <Button
          variant="secondary"
          size="md"
          className="text-red-500 border-red-500/30 hover:bg-red-500/10"
        >
          Clear All Credentials
        </Button>
      </Card>

      {/* Save Message */}
      {saveMessage && (
        <div className="fixed bottom-4 right-4 bg-cyan-500/20 border border-cyan-500/30 text-cyan-300 px-4 py-3 rounded-lg">
          {saveMessage}
        </div>
      )}
    </div>
  )
}
