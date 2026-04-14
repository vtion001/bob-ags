@extends('layouts.app')

@section('title', 'Live Session - ' . $session->session_id)

@section('header')
<div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('live-monitoring.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Live Session</h1>
            <p class="text-sm text-gray-500 mt-1">Session ID: {{ $session->session_id }}</p>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 px-3 py-1 bg-green-100 rounded-full">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span class="text-sm font-medium text-green-800">Live</span>
        </div>
        <span class="session-timer text-lg font-semibold text-gray-700" id="mainTimer">00:00:00</span>
        <button onclick="endSession()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
            End Session
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Info</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Agent</p>
                <p class="font-medium text-gray-900">{{ $session->agent_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Caller</p>
                <p class="font-medium text-gray-900">{{ $session->caller_number ?? 'Not specified' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Started</p>
                <p class="font-medium text-gray-900">{{ $session->started_at?->format('H:i:s') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="font-medium text-gray-900 capitalize">{{ $session->status }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Transcript</h2>
        <div class="bg-gray-50 rounded-lg p-4 min-h-[300px] max-h-[400px] overflow-y-auto">
            <p class="text-gray-400 text-center">Waiting for transcript...</p>
        </div>
        <div class="mt-4 flex gap-3">
            <input type="text" id="manualTranscript" placeholder="Enter transcript manually (for testing)..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button onclick="addTranscript()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                Add
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Actions</h2>
        <div class="space-y-3">
            <a href="{{ route('live-monitoring.index') }}" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors text-center">
                Back to Sessions
            </a>
        </div>
    </div>
</div>

<script>
const sessionId = '{{ $session->session_id }}';

function addTranscript() {
    const input = document.getElementById('manualTranscript');
    const text = input.value.trim();
    if (!text) return;
    
    fetch('/api/live-monitoring/add-transcript', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            session_id: sessionId,
            text: text,
            speaker: 'caller'
        })
    })
    .then(() => { input.value = ''; });
}

function endSession() {
    if (!confirm('Are you sure you want to end this session?')) return;
    
    fetch('/live-monitoring/stop/' + sessionId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/live-monitoring';
        }
    });
}
</script>
@endsection
