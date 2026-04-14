@extends('layouts.app')

@section('title', 'Calls')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-black">Calls</h1>
            <form method="GET" action="{{ route('calls.sync') }}">
                <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg font-medium">
                    Sync Calls
                </button>
            </form>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Phone</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black"
                        placeholder="Search by phone...">
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg font-medium w-full">
                        Filter
                    </button>
                </div>
            </form>
        </div>

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
                                <td class="px-4 py-3 text-sm text-black">{{ $call->agent_name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($call->qaLog?->total_score)
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            @if($call->qaLog->total_score >= 80) bg-green-100 text-green-800
                                            @elseif($call->qaLog->total_score >= 60) bg-yellow-100 text-yellow-800
                                            @elseif($call->qaLog->total_score >= 40) bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $call->qaLog->total_score }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-black max-w-xs truncate">{{ $call->qaLog?->disposition ?? 'N/A' }}</td>
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

                <!-- Pagination -->
                <div class="px-4 py-3 bg-gray-50">
                    {{ $calls->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
