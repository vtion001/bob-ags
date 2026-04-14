@extends('layouts.app')

@section('title', 'Supervisor Dashboard')

@push('styles')
<style>
.sessions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1rem;
}

.session-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.2s;
}

.session-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.session-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.alert-badge {
    animation: pulse-alert 2s infinite;
}

@keyframes pulse-alert {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
}

.live-indicator {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.transcript-preview {
    max-height: 120px;
    overflow: hidden;
    position: relative;
}

.transcript-preview::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(transparent, white);
}

.stat-card {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.05) 100%);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 12px;
    padding: 16px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e40af;
}

.ztp-alert-card {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 12px;
    padding: 14px;
}

.agent-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
}
</style>
@endpush

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Supervisor Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Real-time monitoring of all active sessions</p>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 px-4 py-2 bg-green-100 rounded-lg">
            <div class="live-indicator"></div>
            <span class="text-sm font-medium text-green-800" id="activeCount">{{ $activeSessions->count() }}</span>
            <span class="text-sm text-green-600">Active Sessions</span>
        </div>
        <div class="text-sm text-gray-500">
            Last updated: <span id="lastUpdated">{{ now()->format('H:i:s') }}</span>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-sm text-gray-500 mb-1">Active Sessions</p>
            <p class="stat-value" id="statActive">{{ $activeSessions->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 mb-1">ZTP Alerts Today</p>
            <p class="stat-value" id="statZtp">{{ $ztpAlerts->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 mb-1">Total Calls Today</p>
            <p class="stat-value" id="statCalls">-</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 mb-1">Avg. Resolution Time</p>
            <p class="stat-value" id="statAvgTime">-</p>
        </div>
    </div>

    @if($ztpAlerts->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Recent ZTP Alerts
        </h2>
        <div class="space-y-3" id="ztpAlertsList">
            @foreach($ztpAlerts as $alert)
            <div class="ztp-alert-card">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="agent-avatar">
                            {{ substr($alert['agent_name'] ?? 'A', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $alert['agent_name'] ?? 'Unknown Agent' }}</p>
                            <p class="text-sm text-red-600 mt-1">{{ $alert['pattern'] ?? 'ZTP Violation' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $alert['message'] ?? '' }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $alert['timestamp'] ?? '' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Sessions</h2>
        <div class="sessions-grid" id="sessionsGrid">
            @forelse($activeSessions as $session)
            <div class="session-card" id="session-{{ $session->session_id }}">
                <div class="session-header">
                    <div class="flex items-center gap-3">
                        <div class="agent-avatar">
                            {{ substr($session->agent_name, 0, 1) }}
                        </div>
                        <div class="text-white">
                            <p class="font-medium">{{ $session->agent_name }}</p>
                            <p class="text-xs text-gray-300">{{ $session->caller_number ?? 'Unknown caller' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(($session->ztp_alerts ?? []) !== [])
                        <span class="alert-badge px-2 py-1 bg-red-500 text-white text-xs rounded-full">
                            {{ count($session->ztp_alerts ?? []) }} Alert(s)
                        </span>
                        @endif
                        <div class="live-indicator"></div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-gray-500">Started</span>
                        <span class="text-gray-900">{{ $session->started_at?->diffForHumans() }}</span>
                    </div>
                    <div class="transcript-preview">
                        <p class="text-sm text-gray-600">{{ Str::limit($session->transcript_text ?? 'No transcript yet...', 150) }}</p>
                    </div>
                    @if(($session->active_suggestions['what_to_say'] ?? null))
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Current Suggestion</p>
                        <p class="text-sm text-blue-600">{{ Str::limit($session->active_suggestions['what_to_say'], 80) }}</p>
                    </div>
                    @endif
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('live-monitoring.session', $session->session_id) }}" class="flex-1 bg-navy-900 hover:bg-navy-800 text-white text-center py-2 rounded-lg text-sm transition-colors">
                            View Session
                        </a>
                        <button onclick="sendAlert('{{ $session->session_id }}')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                            Alert Agent
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500">No active sessions</p>
                <p class="text-sm text-gray-400 mt-1">Active sessions will appear here in real-time</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
let eventSource = null;

function initEventSource() {
    eventSource = new EventSource('{{ route('supervisor.live-stream') }}');
    
    eventSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        updateDashboard(data);
    };
    
    eventSource.onerror = function() {
        console.log('SSE connection lost, reconnecting...');
        setTimeout(initEventSource, 3000);
    };
}

function updateDashboard(data) {
    document.getElementById('activeCount').textContent = data.active_count;
    document.getElementById('statActive').textContent = data.active_count;
    document.getElementById('lastUpdated').textContent = new Date(data.timestamp).toLocaleTimeString();
    
    let ztpCount = 0;
    data.sessions.forEach(session => {
        ztpCount += (session.ztp_alerts || []).length;
    });
    document.getElementById('statZtp').textContent = ztpCount;
    
    updateSessionsGrid(data.sessions);
}

function updateSessionsGrid(sessions) {
    const grid = document.getElementById('sessionsGrid');
    
    if (sessions.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500">No active sessions</p>
            </div>
        `;
        return;
    }
    
    sessions.forEach(session => {
        const existingCard = document.getElementById('session-' + session.session_id);
        const hasAlerts = (session.ztp_alerts || []).length > 0;
        const suggestion = session.active_suggestions?.what_to_say || '';
        
        if (existingCard) {
            existingCard.querySelector('.transcript-preview p').textContent = session.transcript_preview || 'No transcript yet...';
            if (suggestion) {
                const suggestionEl = existingCard.querySelector('.text-blue-600');
                if (suggestionEl) {
                    suggestionEl.textContent = suggestion.substring(0, 80);
                }
            }
        } else {
            const cardHtml = `
                <div class="session-card" id="session-${session.session_id}">
                    <div class="session-header">
                        <div class="flex items-center gap-3">
                            <div class="agent-avatar">
                                ${(session.agent_name || 'A').substring(0, 1)}
                            </div>
                            <div class="text-white">
                                <p class="font-medium">${escapeHtml(session.agent_name || 'Unknown')}</p>
                                <p class="text-xs text-gray-300">${escapeHtml(session.caller_number || 'Unknown caller')}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            ${hasAlerts ? `<span class="alert-badge px-2 py-1 bg-red-500 text-white text-xs rounded-full">${session.ztp_alerts.length} Alert(s)</span>` : ''}
                            <div class="live-indicator"></div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between text-sm mb-3">
                            <span class="text-gray-500">Started</span>
                            <span class="text-gray-900">${formatRelativeTime(session.started_at)}</span>
                        </div>
                        <div class="transcript-preview">
                            <p class="text-sm text-gray-600">${escapeHtml(session.transcript_preview || 'No transcript yet...')}</p>
                        </div>
                        ${suggestion ? `
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Current Suggestion</p>
                            <p class="text-sm text-blue-600">${escapeHtml(suggestion.substring(0, 80))}</p>
                        </div>
                        ` : ''}
                        <div class="mt-4 flex gap-2">
                            <a href="/live-monitoring/session/${session.session_id}" class="flex-1 bg-navy-900 hover:bg-navy-800 text-white text-center py-2 rounded-lg text-sm transition-colors">
                                View Session
                            </a>
                            <button onclick="sendAlert('${session.session_id}')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                Alert Agent
                            </button>
                        </div>
                    </div>
                </div>
            `;
            grid.insertAdjacentHTML('afterbegin', cardHtml);
        }
    });
}

function sendAlert(sessionId) {
    alert('Alert sent to agent for session: ' + sessionId);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatRelativeTime(timestamp) {
    if (!timestamp) return 'Just now';
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + ' min ago';
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return diffHours + ' hr ago';
    return date.toLocaleDateString();
}

document.addEventListener('DOMContentLoaded', function() {
    initEventSource();
});
</script>
@endsection
