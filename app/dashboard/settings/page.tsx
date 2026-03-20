'use client'

import React, { useState, useEffect } from 'react'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/select'
import { createClient } from '@/lib/supabase/client'

interface UserSettings {
  ctm_access_key: string
  ctm_secret_key: string
  ctm_account_id: string
  openrouter_api_key: string
  default_client: string
  light_mode: boolean
  email_notifications: boolean
  auto_sync_calls: boolean
  call_sync_interval: number
}

interface UserRole {
  id: string
  user_id: string
  email: string
  role: 'admin' | 'manager' | 'viewer'
  permissions: {
    can_view_calls: boolean
    can_view_monitor: boolean
    can_view_history: boolean
    can_view_agents: boolean
    can_manage_settings: boolean
    can_manage_users: boolean
    can_run_analysis: boolean
  }
  approved?: boolean
  approved_by?: string
  created_at: string
}

interface CurrentUser {
  role: string
  permissions: UserRole['permissions']
  email: string
}

export default function SettingsPage() {
  const [settings, setSettings] = useState<UserSettings>({
    ctm_access_key: '',
    ctm_secret_key: '',
    ctm_account_id: '',
    openrouter_api_key: '',
    default_client: 'flyland',
    light_mode: true,
    email_notifications: false,
    auto_sync_calls: true,
    call_sync_interval: 60,
  })
  const [isLoading, setIsLoading] = useState(true)
  const [isSaving, setIsSaving] = useState(false)
  const [saveMessage, setSaveMessage] = useState('')
  const [error, setError] = useState('')
  const [currentUser, setCurrentUser] = useState<CurrentUser | null>(null)
  const [users, setUsers] = useState<UserRole[]>([])
  const [isAdmin, setIsAdmin] = useState(false)
  const [showAddUser, setShowAddUser] = useState(false)
  const [newUserEmail, setNewUserEmail] = useState('')
  const [newUserRole, setNewUserRole] = useState<'admin' | 'manager' | 'viewer'>('viewer')

  const supabase = createClient()

  useEffect(() => {
    loadSettings()
  }, [])

  const loadSettings = async () => {
    setIsLoading(true)
    try {
      const [settingsRes, permissionsRes] = await Promise.all([
        fetch('/api/settings'),
        fetch('/api/users/permissions')
      ])
      
      if (settingsRes.ok) {
        const data = await settingsRes.json()
        if (data.settings) {
          setSettings(prev => ({ ...prev, ...data.settings }))
        }
      }
      
      if (permissionsRes.ok) {
        const permData = await permissionsRes.json()
        setCurrentUser({ 
          role: permData.role, 
          permissions: permData.permissions,
          email: permData.email 
        })
        setIsAdmin(permData.role === 'admin')
        
        if (permData.role === 'admin') {
          const usersRes = await fetch('/api/users/permissions/update')
          if (usersRes.ok) {
            const usersData = await usersRes.json()
            const allUsers = [...(usersData.roles || [])]
            if (permData.email !== 'agsdev@allianceglobalsolutions.com' && !allUsers.find(u => u.email === 'agsdev@allianceglobalsolutions.com')) {
              allUsers.unshift({
                id: 'dev-admin',
                user_id: 'agsdev@allianceglobalsolutions.com',
                email: 'agsdev@allianceglobalsolutions.com',
                role: 'admin',
                permissions: {
                  can_view_calls: true,
                  can_view_monitor: true,
                  can_view_history: true,
                  can_view_agents: true,
                  can_manage_settings: true,
                  can_manage_users: true,
                  can_run_analysis: true,
                },
                created_at: new Date().toISOString(),
              })
            }
            setUsers(allUsers)
          }
        }
      }
    } catch (err) {
      console.error('Failed to load settings:', err)
    } finally {
      setIsLoading(false)
    }
  }

  const handleSave = async () => {
    setIsSaving(true)
    setError('')
    try {
      const res = await fetch('/api/settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings),
      })
      
      if (!res.ok) throw new Error('Failed to save settings')
      
      setSaveMessage('Settings saved successfully')
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsSaving(false)
    }
  }

  const handleClearCredentials = async () => {
    if (!confirm('Are you sure you want to clear all credentials? This cannot be undone.')) {
      return
    }

    setIsSaving(true)
    try {
      const res = await fetch('/api/settings', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
      })
      
      if (!res.ok) throw new Error('Failed to clear settings')
      
      setSettings({
        ctm_access_key: '',
        ctm_secret_key: '',
        ctm_account_id: '',
        openrouter_api_key: '',
        default_client: 'flyland',
        light_mode: true,
        email_notifications: false,
        auto_sync_calls: true,
        call_sync_interval: 60,
      })
      setSaveMessage('Credentials cleared')
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsSaving(false)
    }
  }

  const handleAddUser = async () => {
    if (!newUserEmail) return
    
    setIsSaving(true)
    try {
      const permissions = getDefaultPermissions(newUserRole)
      const res = await fetch('/api/users/permissions/update', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          targetUserId: newUserEmail,
          email: newUserEmail,
          role: newUserRole,
          permissions,
        }),
      })
      
      if (!res.ok) throw new Error('Failed to add user')
      
      setSaveMessage('User added successfully')
      setShowAddUser(false)
      setNewUserEmail('')
      setNewUserRole('viewer')
      
      const usersRes = await fetch('/api/users/permissions/update')
      if (usersRes.ok) {
        const usersData = await usersRes.json()
        setUsers(usersData.roles || [])
      }
      
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsSaving(false)
    }
  }

  const handleUpdateRole = async (userId: string, role: 'admin' | 'manager' | 'viewer') => {
    setIsSaving(true)
    try {
      const permissions = getDefaultPermissions(role)
      const user = users.find(u => u.user_id === userId)
      
      const res = await fetch('/api/users/permissions/update', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          targetUserId: userId,
          email: user?.email || userId,
          role,
          permissions,
        }),
      })
      
      if (!res.ok) throw new Error('Failed to update role')
      
      setUsers(users.map(u => 
        u.user_id === userId 
          ? { ...u, role, permissions }
          : u
      ))
      
      setSaveMessage('Role updated successfully')
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsSaving(false)
    }
  }

  const getDefaultPermissions = (role: string): UserRole['permissions'] => {
    switch (role) {
      case 'admin':
        return {
          can_view_calls: true,
          can_view_monitor: true,
          can_view_history: true,
          can_view_agents: true,
          can_manage_settings: true,
          can_manage_users: true,
          can_run_analysis: true,
        }
      case 'manager':
        return {
          can_view_calls: true,
          can_view_monitor: true,
          can_view_history: true,
          can_view_agents: true,
          can_manage_settings: false,
          can_manage_users: false,
          can_run_analysis: true,
        }
      default:
        return {
          can_view_calls: true,
          can_view_monitor: true,
          can_view_history: false,
          can_view_agents: false,
          can_manage_settings: false,
          can_manage_users: false,
          can_run_analysis: false,
        }
    }
  }

  const handleApproveUser = async (userId: string, role: 'manager' | 'viewer') => {
    setIsSaving(true)
    try {
      const permissions = getDefaultPermissions(role)
      const user = users.find(u => u.user_id === userId)
      
      const res = await fetch('/api/users/permissions/update', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          targetUserId: userId,
          email: user?.email || userId,
          role: role,
          permissions: permissions,
          approved: true,
          approvedBy: currentUser?.email,
        }),
      })
      
      if (!res.ok) throw new Error('Failed to approve user')
      
      setUsers(users.map(u => 
        u.user_id === userId 
          ? { ...u, role, approved: true, approved_by: currentUser?.email || '' }
          : u
      ))
      
      setSaveMessage('User approved successfully')
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsSaving(false)
    }
  }

  const handleRejectUser = async (userId: string) => {
    if (!confirm('Are you sure you want to reject this user? This will remove their access.')) {
      return
    }
    
    setIsSaving(true)
    try {
      const { error } = await supabase
        .from('user_roles')
        .delete()
        .eq('user_id', userId)
      
      if (error) throw error
      
      setUsers(users.filter(u => u.user_id !== userId))
      
      setSaveMessage('User rejected and removed')
      setTimeout(() => setSaveMessage(''), 3000)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsSaving(false)
    }
  }

  if (isLoading) {
    return (
      <div className="p-6 lg:p-8 max-w-3xl mx-auto">
        <div className="flex items-center justify-center h-64">
          <div className="w-12 h-12 border-4 border-navy-100 border-t-navy-900 rounded-full animate-spin" />
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 lg:p-8 max-w-3xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-navy-900 mb-2">Settings</h1>
        <p className="text-navy-500">Manage your integrations and preferences</p>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600">
          {error}
        </div>
      )}

      <Card className="p-6 mb-6">
        <h2 className="text-lg font-bold text-navy-900 mb-6">CTM Integrations</h2>
        
        <div className="space-y-4 mb-6">
          <Input
            label="CTM Access Key"
            type="password"
            value={settings.ctm_access_key}
            onChange={(e) => setSettings(prev => ({ ...prev, ctm_access_key: e.target.value }))}
            placeholder="Enter your access key"
            hint="Your CallTrackingMetrics API access key"
          />
          
          <Input
            label="CTM Secret Key"
            type="password"
            value={settings.ctm_secret_key}
            onChange={(e) => setSettings(prev => ({ ...prev, ctm_secret_key: e.target.value }))}
            placeholder="Enter your secret key"
            hint="Your CallTrackingMetrics API secret key"
          />

          <Input
            label="CTM Account ID"
            type="text"
            value={settings.ctm_account_id}
            onChange={(e) => setSettings(prev => ({ ...prev, ctm_account_id: e.target.value }))}
            placeholder="Enter your account ID"
            hint="Your CallTrackingMetrics account ID"
          />

          <Input
            label="Default Client"
            type="text"
            value={settings.default_client}
            onChange={(e) => setSettings(prev => ({ ...prev, default_client: e.target.value }))}
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
            value={settings.openrouter_api_key}
            onChange={(e) => setSettings(prev => ({ ...prev, openrouter_api_key: e.target.value }))}
            placeholder="Enter your OpenRouter API key"
            hint="API key for AI-powered analysis"
          />
        </div>

        <Button variant="primary" size="md" onClick={handleSave} isLoading={isSaving}>
          Save AI Settings
        </Button>
      </Card>

      <Card className="p-6 mb-6">
        <h2 className="text-lg font-bold text-navy-900 mb-6">Sync Settings</h2>
        
        <div className="space-y-4">
          <div className="flex items-center justify-between p-4 rounded-lg bg-navy-50">
            <div>
              <p className="text-navy-900 font-medium">Auto-sync Calls</p>
              <p className="text-sm text-navy-500">Automatically sync calls from CTM</p>
            </div>
            <button
              onClick={() => setSettings(prev => ({ ...prev, auto_sync_calls: !prev.auto_sync_calls }))}
              className={`relative w-14 h-7 rounded-full transition-colors ${
                settings.auto_sync_calls ? 'bg-navy-900' : 'bg-navy-200'
              }`}
            >
              <span
                className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-transform flex items-center justify-center ${
                  settings.auto_sync_calls ? 'translate-x-7.5' : 'translate-x-0.5'
                }`}
              >
                <span className={`w-2 h-2 rounded-full ${settings.auto_sync_calls ? 'bg-navy-900' : 'bg-navy-400'}`} />
              </span>
            </button>
          </div>

          {settings.auto_sync_calls && (
            <div className="p-4 rounded-lg bg-navy-50">
              <label className="block text-navy-900 font-medium mb-2">Sync Interval (minutes)</label>
              <Select
                value={String(settings.call_sync_interval)}
                onChange={(value) => setSettings(prev => ({ ...prev, call_sync_interval: parseInt(value) }))}
                options={[
                  { value: '15', label: 'Every 15 minutes' },
                  { value: '30', label: 'Every 30 minutes' },
                  { value: '60', label: 'Every hour' },
                  { value: '120', label: 'Every 2 hours' },
                ]}
                className="w-full"
              />
            </div>
          )}
        </div>

        <Button variant="primary" size="md" onClick={handleSave} isLoading={isSaving} className="mt-6">
          Save Sync Settings
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
              onClick={() => setSettings(prev => ({ ...prev, light_mode: !prev.light_mode }))}
              className={`relative w-14 h-7 rounded-full transition-colors ${
                settings.light_mode ? 'bg-navy-900' : 'bg-navy-200'
              }`}
            >
              <span
                className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-transform flex items-center justify-center ${
                  settings.light_mode ? 'translate-x-7.5' : 'translate-x-0.5'
                }`}
              >
                <span className={`w-2 h-2 rounded-full ${settings.light_mode ? 'bg-navy-900' : 'bg-navy-400'}`} />
              </span>
            </button>
          </div>

          <div className="flex items-center justify-between p-4 rounded-lg bg-navy-50">
            <div>
              <p className="text-navy-900 font-medium">Email Notifications</p>
              <p className="text-sm text-navy-500">Receive notifications for hot leads</p>
            </div>
            <button
              onClick={() => setSettings(prev => ({ ...prev, email_notifications: !prev.email_notifications }))}
              className={`relative w-14 h-7 rounded-full transition-colors ${
                settings.email_notifications ? 'bg-navy-900' : 'bg-navy-200'
              }`}
            >
              <span
                className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-transform flex items-center justify-center ${
                  settings.email_notifications ? 'translate-x-7.5' : 'translate-x-0.5'
                }`}
              >
                <span className={`w-2 h-2 rounded-full ${settings.email_notifications ? 'bg-navy-900' : 'bg-navy-400'}`} />
              </span>
            </button>
          </div>
        </div>

        <Button variant="primary" size="md" onClick={handleSave} isLoading={isSaving} className="mt-6">
          Save Preferences
        </Button>
      </Card>

      {isAdmin && (
        <Card className="p-6 mb-6 border-navy-200">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h2 className="text-lg font-bold text-navy-900">User Permissions</h2>
              <p className="text-sm text-navy-500">Manage user roles and navigation access</p>
            </div>
            <Button
              variant="primary"
              size="sm"
              onClick={() => setShowAddUser(!showAddUser)}
            >
              {showAddUser ? 'Cancel' : 'Add User'}
            </Button>
          </div>

          {showAddUser && (
            <div className="mb-6 p-4 bg-navy-50 rounded-lg">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Input
                  label="Email"
                  type="email"
                  value={newUserEmail}
                  onChange={(e) => setNewUserEmail(e.target.value)}
                  placeholder="user@example.com"
                />
                <Select
                  label="Role"
                  value={newUserRole}
                  onChange={(value) => setNewUserRole(value as 'admin' | 'manager' | 'viewer')}
                  options={[
                    { value: 'viewer', label: 'Viewer - Monitor only' },
                    { value: 'manager', label: 'Manager - Full access (no settings)' },
                    { value: 'admin', label: 'Admin - Full access' },
                  ]}
                  className="w-full"
                />
                <div className="flex items-end">
                  <Button
                    variant="primary"
                    size="md"
                    onClick={handleAddUser}
                    isLoading={isSaving}
                    className="w-full"
                  >
                    Add User
                  </Button>
                </div>
              </div>
            </div>
          )}

          <div className="space-y-4">
            {(() => {
              const pendingUsers = users.filter(u => !u.approved && u.email !== 'agsdev@allianceglobalsolutions.com' && u.id !== 'dev-admin')
              const approvedUsers = users.filter(u => u.approved || u.email === 'agsdev@allianceglobalsolutions.com' || u.id === 'dev-admin')
              
              return (
                <>
                  {pendingUsers.length > 0 && (
                    <div className="mb-6">
                      <h3 className="text-sm font-semibold text-orange-600 uppercase tracking-wide mb-3">
                        Pending Approval ({pendingUsers.length})
                      </h3>
                      <div className="space-y-3">
                        {pendingUsers.map((user) => (
                          <div key={user.id} className="flex items-center justify-between p-4 rounded-lg bg-orange-50 border border-orange-200">
                            <div className="flex-1">
                              <p className="font-medium text-navy-900">{user.email}</p>
                              <p className="text-sm text-orange-600">Waiting for approval</p>
                            </div>
                            <div className="flex gap-2">
                              <Button
                                variant="secondary"
                                size="sm"
                                onClick={() => handleApproveUser(user.user_id, 'viewer')}
                                disabled={isSaving}
                              >
                                Approve Viewer
                              </Button>
                              <Button
                                variant="primary"
                                size="sm"
                                onClick={() => handleApproveUser(user.user_id, 'manager')}
                                disabled={isSaving}
                              >
                                Approve Manager
                              </Button>
                              <Button
                                variant="secondary"
                                size="sm"
                                onClick={() => handleRejectUser(user.user_id)}
                                disabled={isSaving}
                                className="text-red-600 border-red-300 hover:bg-red-50"
                              >
                                Reject
                              </Button>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                  
                  <h3 className="text-sm font-semibold text-navy-600 uppercase tracking-wide mb-3">
                    Approved Users ({approvedUsers.length})
                  </h3>
                  
                  {approvedUsers.length === 0 && !pendingUsers.length ? (
                    <p className="text-navy-500 text-center py-4">No users yet. Users will appear here after signing up.</p>
                  ) : (
                    <div className="space-y-3">
                      {approvedUsers.map((user) => {
                        const isDevAdmin = user.email === 'agsdev@allianceglobalsolutions.com' || user.id === 'dev-admin'
                        return (
                          <div key={user.id} className={`flex items-center justify-between p-4 rounded-lg ${isDevAdmin ? 'bg-amber-50 border border-amber-200' : 'bg-navy-50'}`}>
                            <div className="flex-1">
                              <div className="flex items-center gap-2">
                                <p className="font-medium text-navy-900">{user.email}</p>
                                {isDevAdmin && (
                                  <span className="px-2 py-0.5 text-xs font-medium bg-amber-500 text-white rounded-full">
                                    Admin
                                  </span>
                                )}
                                {user.approved && !isDevAdmin && (
                                  <span className="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                    Approved
                                  </span>
                                )}
                              </div>
                              <p className="text-sm text-navy-500">
                                {user.role === 'admin' && 'Full access to all features'}
                                {user.role === 'manager' && 'Access to calls, monitor, history, and analysis'}
                                {user.role === 'viewer' && 'Monitor tab only'}
                              </p>
                            </div>
                            {isDevAdmin ? (
                              <span className="px-3 py-2 text-sm font-medium text-amber-700 bg-amber-100 rounded-lg">
                                Full Access
                              </span>
                            ) : (
                              <Select
                                value={user.role}
                                onChange={(value) => handleUpdateRole(user.user_id, value as 'admin' | 'manager' | 'viewer')}
                                options={[
                                  { value: 'viewer', label: 'Viewer' },
                                  { value: 'manager', label: 'Manager' },
                                  { value: 'admin', label: 'Admin' },
                                ]}
                                disabled={isSaving}
                              />
                            )}
                          </div>
                        )
                      })}
                    </div>
                  )}
                </>
              )
            })()}
          </div>
        </Card>
      )}

      <Card className="p-6 border-red-200">
        <h2 className="text-lg font-bold text-red-600 mb-4">Danger Zone</h2>
        <p className="text-navy-600 mb-4">
          Clear all stored credentials. This action cannot be undone.
        </p>
        <Button
          variant="secondary"
          size="md"
          onClick={handleClearCredentials}
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