@extends('layouts.app')

@section('title', 'Agent Profile - ' . $agent->ctm_agent_name)

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('agents.index') }}" class="inline-flex items-center text-navy-900 hover:text-navy-700 mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Agents
        </a>

        <!-- Agent Info Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-black mb-2">{{ $agent->ctm_agent_name }}</h1>
                    <p class="text-gray-500">{{ $agent->ctm_agent_email ?? 'No email' }}</p>
                    <p class="text-sm text-gray-400 font-mono mt-1">ID: {{ $agent->ctm_agent_id }}</p>
                </div>
                <div class="text-right">
                    @if($agent->user)
                        <p class="text-sm text-gray-500">Linked to</p>
                        <p class="font-medium text-black">{{ $agent->user->name }}</p>
                        <p class="text-sm text-gray-400">{{ $agent->user->email }}</p>
                    @else
                        <span class="px-3 py-1 rounded text-sm font-medium bg-gray-100 text-gray-600">
                            Unlinked
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-500">Total Calls</p>
                <p class="text-3xl font-bold text-navy-900">{{ $totalCalls }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-500">Analyzed Calls</p>
                <p class="text-3xl font-bold text-green-600">{{ $analyzedCalls }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-500">Avg QA Score</p>
                <p class="text-3xl font-bold text-blue-600">
                    @php
                        $avgScore = \App\Models\QaLog::whereIn('call_id', $agent->calls()->pluck('id'))->avg('total_score');
                        echo $avgScore ? round($avgScore, 1) : 'N/A';
                    @endphp
                </p>
            </div>
        </div>

        <!-- Agent Calls Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-black">Recent Calls</h2>
            </div>
            @if($calls->isEmpty())
                <p class="text-gray-500 text-center py-12">No calls found for this agent.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-navy-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Direction</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($calls as $call)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-black">{{ $call->caller_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-medium 
                                        @if(($call->direction ?? '') === 'inbound') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($call->direction ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-black">{{ $call->call_datetime ? $call->call_datetime->format('M d, Y H:i') : 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-black">{{ $call->duration ?? 0 }}s</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($call->qaLog?->total_score)
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            @if($call->qaLog->total_score >= 80) bg-green-100 text-green-800
                                            @elseif($call->qaLog->total_score >= 60) bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $call->qaLog->total_score }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('calls.show', $call->ctm_call_id ?? $call->id) }}" 
                                        class="text-navy-900 hover:text-navy-700 font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 bg-gray-50">
                    {{ $calls->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
