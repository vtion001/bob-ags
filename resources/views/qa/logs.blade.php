@extends('layouts.app')

@section('title', 'QA Logs')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-black mb-6">QA Analysis Logs</h1>

        <!-- Score Distribution -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-navy-900 rounded-lg p-4 text-white relative overflow-hidden">
                <div class="absolute top-3 left-3 w-2 h-2 rounded-full bg-green-500"></div>
                <p class="text-sm pl-5">Excellent (85+)</p>
                <p class="text-2xl font-bold">{{ $scoreDistribution['excellent'] }}</p>
            </div>
            <div class="bg-navy-900 rounded-lg p-4 text-white relative overflow-hidden">
                <div class="absolute top-3 left-3 w-2 h-2 rounded-full bg-blue-500"></div>
                <p class="text-sm pl-5">Good (70-84)</p>
                <p class="text-2xl font-bold">{{ $scoreDistribution['good'] }}</p>
            </div>
            <div class="bg-navy-900 rounded-lg p-4 text-white relative overflow-hidden">
                <div class="absolute top-3 left-3 w-2 h-2 rounded-full bg-yellow-500"></div>
                <p class="text-sm pl-5">Needs Improvement (50-69)</p>
                <p class="text-2xl font-bold">{{ $scoreDistribution['needs_improvement'] }}</p>
            </div>
            <div class="bg-navy-900 rounded-lg p-4 text-white relative overflow-hidden">
                <div class="absolute top-3 left-3 w-2 h-2 rounded-full bg-red-500"></div>
                <p class="text-sm pl-5">Poor (0-49)</p>
                <p class="text-2xl font-bold">{{ $scoreDistribution['poor'] }}</p>
            </div>
        </div>

        <!-- Analyzed Calls -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if(empty($calls))
                <p class="text-gray-500 text-center py-12">No analyzed calls yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-navy-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Call ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Sentiment</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Disposition</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tags</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($calls as $call)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-mono text-black">{{ substr($call['ctm_call_id'] ?? '', 0, 12) }}...</td>
                                <td class="px-4 py-3 text-sm text-black">{{ isset($call['timestamp']) ? \Carbon\Carbon::parse($call['timestamp'])->format('M d, Y') : 'N/A' }}</td>
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
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if(isset($call['sentiment']))
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            @if($call['sentiment'] === 'positive') bg-green-100 text-green-800
                                            @elseif($call['sentiment'] === 'neutral') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($call['sentiment']) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-black max-w-xs truncate">{{ $call['disposition'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex flex-wrap gap-1">
                                        @if(!empty($call['tags']))
                                            @foreach(array_slice($call['tags'], 0, 3) as $tag)
                                                <span class="px-2 py-0.5 bg-navy-100 text-navy-800 rounded text-xs">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('calls.show', $call['ctm_call_id']) }}" 
                                        class="text-navy-900 hover:text-navy-700 font-medium">
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
