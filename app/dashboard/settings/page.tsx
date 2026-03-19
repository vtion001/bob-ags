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
  const [lightMode, setLightMode] = useState(true)
  const [emailNotifications, setEmailNotifications] = useState(false)

  const handleChange = (field: string, value: string) => {
    setCredentials(prev => ({
      ...prev,
      [field]: value,
    }))
  }

  const handleSave = async () => {
    setIsSaving(true)
    try {
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
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-navy-900 mb-2">Settings</h1>
        <p className="text-navy-500">Manage your integrations and preferences</p>
      </div>

      <Card className="p-6 mb-6">
        <h2 className="text-lg font-bold text-navy-900 mb-6">CTM Integrations</h2>
        
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

      <Card className="p-6 mb-6">
        <h2 className="text-lg font-bold text-navy-900 mb-6">AI Analysis</h2>
        
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

      <Card className="p-6 mb-6">
        <h2 className="text-lg font-bold text-navy-900 mb-6">Preferences</h2>
        
        <div className="space-y-4">
          <div className="flex items-center justify-between p-4 rounded-lg bg-navy-50">
            <div>
              <p className="text-navy-900 font-medium">Light Mode</p>
              <p className="text-sm text-navy-500">Clean white interface</p>
            </div>
            <button
              onClick={() => setLightMode(!lightMode)}
              className={`relative w-14 h-7 rounded-full transition-colors ${
                lightMode ? 'bg-navy-900' : 'bg-navy-200'
              }`}
            >
              <span
                className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-transform flex items-center justify-center ${
                  lightMode ? 'translate-x-7.5' : 'translate-x-0.5'
                }`}
              >
                <span className={`w-2 h-2 rounded-full ${lightMode ? 'bg-navy-900' : 'bg-navy-400'}`} />
              </span>
            </button>
          </div>

          <div className="flex items-center justify-between p-4 rounded-lg bg-navy-50">
            <div>
              <p className="text-navy-900 font-medium">Email Notifications</p>
              <p className="text-sm text-navy-500">Receive notifications for hot leads</p>
            </div>
            <button
              onClick={() => setEmailNotifications(!emailNotifications)}
              className={`relative w-14 h-7 rounded-full transition-colors ${
                emailNotifications ? 'bg-navy-900' : 'bg-navy-200'
              }`}
            >
              <span
                className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-transform flex items-center justify-center ${
                  emailNotifications ? 'translate-x-7.5' : 'translate-x-0.5'
                }`}
              >
                <span className={`w-2 h-2 rounded-full ${emailNotifications ? 'bg-navy-900' : 'bg-navy-400'}`} />
              </span>
            </button>
          </div>
        </div>
      </Card>

      <Card className="p-6 border-red-200">
        <h2 className="text-lg font-bold text-red-600 mb-4">Danger Zone</h2>
        <p className="text-navy-600 mb-4">
          Clear all stored credentials. This action cannot be undone.
        </p>
        <Button
          variant="secondary"
          size="md"
          className="text-red-600 border-red-300 hover:bg-red-50"
        >
          Clear All Credentials
        </Button>
      </Card>

      {saveMessage && (
        <div className="fixed bottom-6 right-6 z-50 bg-navy-900 text-white px-4 py-3 rounded-lg shadow-lg">
          {saveMessage}
        </div>
      )}
    </div>
  )
}
