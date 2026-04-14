@extends('layouts.app')

@section('title', 'Live Session - ' . $session->session_id)

@push('styles')
<style>
.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.session-timer {
    font-family: monospace;
}
</style>
@endpush

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
<div id="app">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
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
                    <p class="text-gray-400 text-center">Use the floating window to add transcript entries...</p>
                </div>
                <div class="mt-4 flex gap-3">
                    <input type="text" id="manualTranscript" placeholder="Enter transcript manually (for testing)..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button onclick="addTranscript()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Add
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">How to Use</h2>
                <div class="space-y-4 text-sm text-gray-600">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-semibold text-xs">1</span>
                        </div>
                        <p>Click "Add" or type in the floating window to add transcript entries</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-semibold text-xs">2</span>
                        </div>
                        <p>AI suggestions will appear in the floating window</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-semibold text-xs">3</span>
                        </div>
                        <p>Drag the window anywhere on screen</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-semibold text-xs">4</span>
                        </div>
                        <p>Use the chat to ask questions or get help</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Actions</h2>
                <div class="space-y-3">
                    <button onclick="toggleFloatingWindow()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Toggle AI Assistant
                    </button>
                    <a href="{{ route('live-monitoring.index') }}" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors text-center">
                        Back to Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <floating-window
        ref="floatingWindow"
        :session-id="'{{ $session->session_id }}'"
        :start-time="'{{ $session->started_at?->toIso8601String() }}'"
        :show="floatingWindowVisible"
        v-on:toggle="floatingWindowVisible = $event"
        v-on:time-update="updateMainTimer"
    ></floating-window>
</div>

<script>
const sessionId = '{{ $session->session_id }}';

function updateMainTimer(time) {
    const timer = document.getElementById('mainTimer');
    if (timer) timer.textContent = time;
}

function toggleFloatingWindow() {
    if (window.vueApp && window.vueApp._instance && window.vueApp._instance.proxy) {
        window.vueApp._instance.proxy.toggleFloatingWindow();
    }
}

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
    .then(() => {
        input.value = '';
    });
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
