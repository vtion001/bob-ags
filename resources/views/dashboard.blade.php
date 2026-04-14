@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Calls -->
            <div class="bg-navy-900 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Total Calls</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_calls'] ?? 0 }}</p>
                    </div>
                    <div class="text-4xl opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Analyzed Calls -->
            <div class="bg-navy-900 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Analyzed</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['analyzed_calls'] ?? 0 }}</p>
                    </div>
                    <div class="text-4xl opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Score -->
            <div class="bg-navy-900 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Average Score</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['avg_score'] ?? 0 }}<span class="text-lg">/100</span></p>
                    </div>
                    <div class="text-4xl opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Qualified Leads -->
            <div class="bg-navy-900 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-300">Qualified Leads</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['qualified_leads'] ?? 0 }}</p>
                    </div>
                    <div class="text-4xl opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Calls -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-black">Recent Calls</h2>
                <a href="{{ route('calls.index') }}" class="text-navy-900 hover:text-navy-700 text-sm font-medium">
                    View All →
                </a>
            </div>

            @if(empty($recentCalls))
                <p class="text-gray-500 text-center py-8">No calls yet. Connect your CTM account to start syncing calls.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-navy-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Disposition</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentCalls as $call)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-black">{{ $call['caller_number'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-black">{{ isset($call['timestamp']) ? \Carbon\Carbon::parse($call['timestamp'])->format('M d, Y H:i') : 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-black">{{ $call['duration'] ?? 0 }}s</td>
                                <td class="px-4 py-3 text-sm">
                                    @if(isset($call['score']))
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            @if($call['score'] >= 80) bg-green-100 text-green-800
                                            @elseif($call['score'] >= 60) bg-yellow-100 text-yellow-800
                                            @elseif($call['score'] >= 40) bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $call['score'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Not scored</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-black">{{ $call['disposition'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('calls.show', $call['ctm_call_id'] ?? $call['id']) }}" class="text-navy-900 hover:text-navy-700 font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
