'use client'

import React, { useState, useEffect, useCallback } from 'react'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'
import { createClient } from '@/lib/supabase/client'

interface CTMAgent {
  id: string
  name: string
  email: string
}

interface AgentProfile {
  id: string
  name: string
  agent_id: string
  email: string | null
  phone: string | null
  notes: string | null
  created_at: string
}

export default function AgentsPage() {
  const [agents, setAgents] = useState<AgentProfile[]>([])
  const [ctmAgents, setCtmAgents] = useState<CTMAgent[]>([])
  const [showForm, setShowForm] = useState(false)
  const [showCTMFetch, setShowCTMFetch] = useState(false)
  const [editingAgent, setEditingAgent] = useState<AgentProfile | null>(null)
  const [formData, setFormData] = useState({
    name: '',
    agentId: '',
    email: '',
    phone: '',
    notes: '',
  })
  const [error, setError] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [isFetchingCTM, setIsFetchingCTM] = useState(false)

  const supabase = createClient()

  const fetchAgents = useCallback(async () => {
    setIsLoading(true)
    const { data, error } = await supabase
      .from('agent_profiles')
      .select('*')
      .order('name')

    if (error) {
      setError(error.message)
    } else {
      setAgents(data || [])
    }
    setIsLoading(false)
  }, [])

  useEffect(() => {
    fetchAgents()
  }, [fetchAgents])

  const fetchCTMAgents = async () => {
    setIsFetchingCTM(true)
    setError(null)
    try {
      const response = await fetch('/api/ctm/agents')
      if (!response.ok) throw new Error('Failed to fetch CTM agents')
      const data = await response.json()
      setCtmAgents(data.agents || [])
      setShowCTMFetch(true)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setIsFetchingCTM(false)
    }
  }

  const handleAddCTMAgent = (ctmAgent: CTMAgent) => {
    setFormData({
      name: ctmAgent.name,
      agentId: ctmAgent.id,
      email: ctmAgent.email,
      phone: '',
      notes: '',
    })
    setEditingAgent(null)
    setShowForm(true)
    setShowCTMFetch(false)
  }

  const handleAddAllCTMAgents = async () => {
    const existingAgentIds = agents.map(a => a.agent_id)
    const newAgents = ctmAgents.filter(a => !existingAgentIds.includes(a.id))
    
    if (newAgents.length === 0) {
      setError('All CTM agents already exist in your profiles')
      return
    }

    const { error } = await supabase.from('agent_profiles').insert(
      newAgents.map(a => ({
        name: a.name,
        agent_id: a.id,
        email: a.email,
        phone: null,
        notes: null,
      }))
    )

    if (error) {
      setError(error.message)
    } else {
      fetchAgents()
      setShowCTMFetch(false)
    }
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!formData.name || !formData.agentId) {
      setError('Name and Agent ID are required')
      return
    }

    if (editingAgent) {
      const { error } = await supabase
        .from('agent_profiles')
        .update({
          name: formData.name,
          agent_id: formData.agentId,
          email: formData.email || null,
          phone: formData.phone || null,
          notes: formData.notes || null,
        })
        .eq('id', editingAgent.id)

      if (error) {
        setError(error.message)
        return
      }
    } else {
      const { error } = await supabase
        .from('agent_profiles')
        .insert([
          {
            name: formData.name,
            agent_id: formData.agentId,
            email: formData.email || null,
            phone: formData.phone || null,
            notes: formData.notes || null,
          }
        ])

      if (error) {
        setError(error.message)
        return
      }
    }

    resetForm()
    fetchAgents()
  }

  const resetForm = () => {
    setFormData({ name: '', agentId: '', email: '', phone: '', notes: '' })
    setEditingAgent(null)
    setShowForm(false)
    setError(null)
  }

  const handleEdit = (agent: AgentProfile) => {
    setEditingAgent(agent)
    setFormData({
      name: agent.name,
      agentId: agent.agent_id,
      email: agent.email || '',
      phone: agent.phone || '',
      notes: agent.notes || '',
    })
    setShowForm(true)
  }

  const handleDelete = async (id: string) => {
    if (confirm('Are you sure you want to delete this agent profile?')) {
      await supabase
        .from('agent_profiles')
        .delete()
        .eq('id', id)
      fetchAgents()
    }
  }

  if (isLoading) {
    return (
      <div className="p-6 lg:p-8 max-w-6xl mx-auto">
        <div className="flex items-center justify-center h-64">
          <div className="w-12 h-12 border-4 border-navy-100 border-t-navy-900 rounded-full animate-spin" />
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 lg:p-8 max-w-6xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-navy-900 mb-2">Agent Profiles</h1>
        <p className="text-navy-500">Manage agent profiles for filtering call history</p>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p className="text-red-600 font-medium">{error}</p>
        </div>
      )}

      <div className="mb-6 flex gap-3 justify-end">
        <Button
          variant="secondary"
          size="md"
          onClick={fetchCTMAgents}
          isLoading={isFetchingCTM}
          disabled={isFetchingCTM || isLoading}
        >
          Fetch from CTM
        </Button>
        <Button
          variant="primary"
          size="md"
          onClick={() => setShowForm(!showForm)}
        >
          {showForm ? 'Cancel' : '+ Add Agent'}
        </Button>
      </div>

      {showForm && (
        <Card className="p-6 mb-6">
          <h3 className="text-lg font-bold text-navy-900 mb-4">
            {editingAgent ? 'Edit Agent Profile' : 'Add New Agent Profile'}
          </h3>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                label="Name *"
                type="text"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                placeholder="John Smith"
              />
              <Input
                label="Agent ID *"
                type="text"
                value={formData.agentId}
                onChange={(e) => setFormData({ ...formData, agentId: e.target.value })}
                placeholder="agent_123456"
              />
              <Input
                label="Email"
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                placeholder="john@company.com"
              />
              <Input
                label="Phone"
                type="text"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                placeholder="+1 234 567 8900"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-navy-700 mb-2">Notes</label>
              <textarea
                value={formData.notes}
                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                placeholder="Additional notes about this agent..."
                className="w-full px-4 py-2.5 bg-white border border-navy-200 rounded-lg text-navy-900 placeholder-navy-400 transition-all duration-200 focus:border-navy-500 focus:ring-2 focus:ring-navy-500/20 focus:outline-none"
                rows={3}
              />
            </div>
            <div className="flex gap-3">
              <Button type="submit" variant="primary" size="md">
                {editingAgent ? 'Update Agent' : 'Add Agent'}
              </Button>
              <Button type="button" variant="secondary" size="md" onClick={resetForm}>
                Cancel
              </Button>
            </div>
          </form>
        </Card>
      )}

      {showCTMFetch && ctmAgents.length > 0 && (
        <Card className="p-6 mb-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-bold text-navy-900">
              CTM Agents ({ctmAgents.length})
            </h3>
            <div className="flex gap-2">
              <Button variant="primary" size="sm" onClick={handleAddAllCTMAgents}>
                Add All
              </Button>
              <Button variant="secondary" size="sm" onClick={() => setShowCTMFetch(false)}>
                Close
              </Button>
            </div>
          </div>
          <div className="space-y-2 max-h-64 overflow-y-auto">
            {ctmAgents.map((agent) => {
              const exists = agents.some(a => a.agent_id === agent.id)
              return (
                <div
                  key={agent.id}
                  className="flex items-center justify-between p-3 bg-navy-50 rounded-lg"
                >
                  <div>
                    <p className="font-medium text-navy-900">{agent.name}</p>
                    <p className="text-sm text-navy-500">{agent.email}</p>
                  </div>
                  {exists ? (
                    <span className="text-sm text-green-600 font-medium">Already added</span>
                  ) : (
                    <Button variant="secondary" size="sm" onClick={() => handleAddCTMAgent(agent)}>
                      Add
                    </Button>
                  )}
                </div>
              )
            })}
          </div>
        </Card>
      )}

      {agents.length === 0 ? (
        <Card className="p-12 text-center">
          <div className="w-16 h-16 bg-navy-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-navy-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <h3 className="text-lg font-semibold text-navy-900 mb-2">No agent profiles yet</h3>
          <p className="text-navy-500 mb-4">Create agent profiles to easily filter calls by agent.</p>
          <Button variant="primary" size="md" onClick={() => setShowForm(true)}>
            Add First Agent
          </Button>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {agents.map((agent) => (
            <Card key={agent.id} className="p-6 hover:shadow-lg transition-shadow">
              <div className="flex items-start justify-between mb-4">
                <div className="w-12 h-12 bg-navy-100 rounded-full flex items-center justify-center">
                  <span className="text-xl font-bold text-navy-900">
                    {agent.name.charAt(0).toUpperCase()}
                  </span>
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={() => handleEdit(agent)}
                    className="p-2 text-navy-400 hover:text-navy-600 hover:bg-navy-50 rounded-lg transition-colors"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                  </button>
                  <button
                    onClick={() => handleDelete(agent.id)}
                    className="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
              <h3 className="text-lg font-semibold text-navy-900 mb-1">{agent.name}</h3>
              <p className="text-sm text-navy-500 mb-2">ID: {agent.agent_id}</p>
              {agent.email && (
                <p className="text-sm text-navy-400 mb-1">{agent.email}</p>
              )}
              {agent.phone && (
                <p className="text-sm text-navy-400 mb-1">{agent.phone}</p>
              )}
              {agent.notes && (
                <p className="text-sm text-navy-400 mt-3 line-clamp-2">{agent.notes}</p>
              )}
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}