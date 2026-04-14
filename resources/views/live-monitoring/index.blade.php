@extends('layouts.app')

@section('title', 'Live Monitoring - Agent View')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Live Monitoring</h1>
        <p class="text-sm text-gray-500 mt-1">Real-time AI-powered call assistance</p>
    </div>
    <button onclick="startNewSession()" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Start New Session
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if($activeSessions->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Sessions</h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($activeSessions as $session)
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer" onclick="window.location.href='{{ route('live-monitoring.session', $session->session_id) }}'">
                <div class="flex items-center justify-between mb-3">
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                    <span class="text-xs text-gray-500">{{ $session->started_at?->diffForHumans() }}</span>
                </div>
                <h3 class="font-medium text-gray-900">{{ $session->agent_name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $session->caller_number ?? 'No caller' }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400 truncate">{{ Str::limit($session->transcript_text ?? 'No transcript yet...', 50) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Start</h2>
        <div class="grid gap-6 md:grid-cols-2">
            <div class="border border-white/20 rounded-lg p-6 hover:border-white/40 transition-colors bg-navy-900">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-white">New Call Session</h3>
                        <p class="text-sm text-gray-300">Start monitoring a new incoming call</p>
                    </div>
                </div>
                <button onclick="startNewSession()" class="w-full bg-white text-navy-900 hover:bg-gray-100 py-2 rounded-lg transition-colors font-medium">
                    Start Session
                </button>
            </div>

            <div class="border border-white/20 rounded-lg p-6 hover:border-white/40 transition-colors bg-navy-900">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-white">Practice Mode</h3>
                        <p class="text-sm text-gray-300">Test with simulated scenarios</p>
                    </div>
                </div>
                <button onclick="startPracticeSession()" class="w-full bg-white text-navy-900 hover:bg-gray-100 py-2 rounded-lg transition-colors font-medium">
                    Start Practice
                </button>
            </div>
        </div>
    </div>

    @if($recentSessions->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Sessions</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentSessions as $session)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $session->agent_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->caller_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $session->status === 'ended' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->started_at?->format('M d, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($session->started_at && $session->ended_at)
                                {{ $session->started_at->diff($session->ended_at)->format('%H:%I:%S') }}
                            @elseif($session->started_at)
                                {{ $session->started_at->diff(now())->format('%H:%I:%S') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('live-monitoring.session', $session->session_id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
const config = {
    startUrl: '{{ route('live-monitoring.start') }}',
    sessionUrl: '{{ url('live-monitoring/session') }}',
    agentName: '{{ Auth::user()->name }}',
    ctmAgentId: '{{ Auth::user()->ctm_agent_id ?? '' }}'
};

async function startNewSession() {
    try {
        const response = await fetch(config.startUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                agent_name: config.agentName,
                ctm_agent_id: config.ctmAgentId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            window.location.href = config.sessionUrl + '/' + data.session_id;
        } else {
            alert('Failed to start session: ' + data.error);
        }
    } catch (error) {
        alert('Error starting session: ' + error.message);
    }
}

async function startPracticeSession() {
    startNewSession();
}
</script>
@endsection
