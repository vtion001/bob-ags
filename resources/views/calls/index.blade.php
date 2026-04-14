@extends('layouts.app')

@section('title', 'Calls')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-black">Calls</h1>
            <div class="flex gap-3">
                <form method="GET" action="{{ route('calls.sync') }}">
                    <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg font-medium">
                        Sync to Database
                    </button>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('calls.search-ctm') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Phone</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black"
                        placeholder="Search by phone...">
                </div>
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-gray-700 mb-1">Agent</label>
                    <select name="agent_id" id="agent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                        <option value="">All Agents</option>
                        @foreach($agents ?? [] as $agent)
                            <option value="{{ $agent->ctm_agent_id }}" {{ request('agent_id') == $agent->ctm_agent_id ? 'selected' : '' }}>
                                {{ $agent->ctm_agent_name ?? $agent->ctm_agent_email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" 
                        value="{{ request('date_from', now()->subMonths(6)->format('Y-m-d')) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" 
                        value="{{ request('date_to', now()->format('Y-m-d')) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg font-medium flex-1">
                        Search CTM
                    </button>
                    <a href="{{ route('calls.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if(isset($searchFrom) && $searchFrom === 'ctm')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-800">
                <strong>CTM Search Results:</strong> Showing {{ $filteredCount ?? $calls->count() }} of {{ $totalEntries ?? 0 }} total calls
                @if(isset($dateFrom) && isset($dateTo))
                    from {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                @endif
            </p>
        </div>
        @endif

        <!-- Calls Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($calls->isEmpty())
                <p class="text-gray-500 text-center py-12">No calls found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-navy-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Direction</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Agent</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Disposition</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($calls as $call)
                            @php
                                $callId = is_object($call) ? ($call->ctm_call_id ?? $call->id) : ($call['id'] ?? null);
                                $callerNumber = is_object($call) ? $call->caller_number : ($call['caller_number'] ?? null);
                                $direction = is_object($call) ? $call->direction : ($call['direction'] ?? 'inbound');
                                $callDatetime = is_object($call) ? $call->call_datetime : (\Carbon\Carbon::parse($call['timestamp'] ?? null) ?? null);
                                $duration = is_object($call) ? $call->duration : ($call['duration'] ?? 0);
                                $agentName = is_object($call) ? $call->agent_name : ($call['agent']['name'] ?? $call['agent_name'] ?? null);
                                $qaLog = is_object($call) ? $call->qaLog : (isset($localCalls[$callId]) ? $localCalls[$callId]->qaLog : null);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-black">{{ $callerNumber ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-medium 
                                        @if(($direction ?? '') === 'inbound' || ($direction ?? '') === 'msg_inbound') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($direction ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-black">
                                    @if($callDatetime)
                                        {{ $callDatetime instanceof \Carbon\Carbon ? $callDatetime->format('M d, Y H:i') : \Carbon\Carbon::parse($callDatetime)->format('M d, Y H:i') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-black">{{ $duration ?? 0 }}s</td>
                                <td class="px-4 py-3 text-sm text-black">{{ $agentName ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($qaLog?->total_score)
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            @if($qaLog->total_score >= 80) bg-green-100 text-green-800
                                            @elseif($qaLog->total_score >= 60) bg-yellow-100 text-yellow-800
                                            @elseif($qaLog->total_score >= 40) bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $qaLog->total_score }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-black max-w-xs truncate">{{ $qaLog?->disposition ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('calls.show', $callId) }}" 
                                        class="text-navy-900 hover:text-navy-700 font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(!isset($searchFrom) || $searchFrom !== 'ctm')
                <div class="px-4 py-3 bg-gray-50">
                    {{ $calls->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
