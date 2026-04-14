@extends('layouts.app')

@section('title', 'Call Details')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('calls.index') }}" class="inline-flex items-center text-navy-900 hover:text-navy-700 mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Calls
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Call Info & Recording -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Call Details Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-black mb-4">Call Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Phone Number</p>
                            <p class="font-medium text-black">{{ $call['caller_number'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Direction</p>
                            <p class="font-medium text-black">{{ ucfirst($call['direction'] ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date & Time</p>
                            <p class="font-medium text-black">{{ isset($call['timestamp']) ? \Carbon\Carbon::parse($call['timestamp'])->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Duration</p>
                            <p class="font-medium text-black">{{ $call['duration'] ?? 0 }} seconds</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Agent</p>
                            <p class="font-medium text-black">{{ $call['agent_name'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Source</p>
                            <p class="font-medium text-black">{{ $call['source'] ?? $call['tracking_label'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recording Player -->
                @if(!empty($call['recording_url']))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-black mb-4">Recording</h2>
                    <audio controls class="w-full">
                        <source src="{{ $call['recording_url'] }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                @endif

                <!-- Transcript -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-black mb-4">Transcript</h2>
                    @if(!empty($call['transcript']))
                        <div class="prose max-w-none text-black">
                            {!! nl2br(e($call['transcript'])) !!}
                        </div>
                    @else
                        <p class="text-gray-500">No transcript available.</p>
                    @endif
                </div>
            </div>

            <!-- Right Column: QA Analysis -->
            <div class="space-y-6">
                <!-- Score Card -->
                <div class="bg-navy-900 rounded-lg p-6 text-white">
                    <h2 class="text-lg font-semibold mb-4">QA Score</h2>
                    @if(isset($call['score']))
                        <div class="text-center">
                            <p class="text-5xl font-bold">{{ $call['score'] }}</p>
                            <p class="text-gray-300 mt-1">/ 100</p>
                            <p class="text-sm mt-2 
                                @if(($call['sentiment'] ?? '') === 'positive') text-green-300
                                @elseif(($call['sentiment'] ?? '') === 'neutral') text-yellow-300
                                @else text-red-300
                                @endif">
                                {{ ucfirst($call['sentiment'] ?? 'N/A') }}
                            </p>
                        </div>
                    @else
                        <p class="text-center text-gray-300">Not analyzed</p>
                    @endif
                </div>

                <!-- Disposition Card -->
                @if(!empty($call['disposition']))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-black mb-2">Disposition</h2>
                    <p class="text-black">{{ $call['disposition'] }}</p>
                </div>
                @endif

                <!-- Tags -->
                @if(!empty($call['tags']))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-black mb-3">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($call['tags'] as $tag)
                            <span class="px-3 py-1 bg-navy-900 text-white rounded-full text-xs font-medium">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Run Analysis Button -->
                @if(empty($call['score']) && !empty($call['transcript']))
                <form method="POST" action="{{ route('calls.analyze', $call['ctm_call_id'] ?? $call['id']) }}">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium">
                        Run QA Analysis
                    </button>
                </form>
                @elseif(empty($call['score']) && empty($call['transcript']))
                <p class="text-sm text-gray-500 text-center">Transcript needed for analysis</p>
                @endif

                <!-- Rubric Breakdown -->
                @if(!empty($call['rubric_breakdown']))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-black mb-4">Score Breakdown</h2>
                    <div class="space-y-3">
                        @foreach($call['rubric_breakdown'] as $category => $data)
                            @if(is_array($data) && isset($data['score']) && isset($data['max']))
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 capitalize">{{ str_replace('_', ' ', $category) }}</span>
                                    <span class="font-medium text-black">{{ $data['score'] }}/{{ $data['max'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php $width = $data['max'] > 0 ? round(($data['score'] / $data['max']) * 100) : 0; @endphp
                                    <div class="bg-navy-900 h-2 rounded-full" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- ZTP Violations -->
                @if(!empty($call['rubric_results']))
                    @php
                        $ztpViolations = collect($call['rubric_results'])->filter(function($r) {
                            return ($r['ztp'] ?? false) && !($r['pass'] ?? true);
                        });
                    @endphp
                    @if($ztpViolations->isNotEmpty())
                    <div class="bg-red-600 rounded-lg p-6 text-white">
                        <h2 class="text-lg font-semibold mb-2">ZTP Violations</h2>
                        <ul class="text-sm space-y-1">
                            @foreach($ztpViolations as $violation)
                                <li>• {{ $violation['id'] }}: {{ $violation['criterion'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Detailed Rubric Results -->
        @if(!empty($call['rubric_results']))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-black mb-4">Rubric Criteria Results</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-navy-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Criterion</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($call['rubric_results'] as $id => $result)
                        <tr class="{{ ($result['ztp'] ?? false) && !($result['pass'] ?? true) ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-black">{{ $id }}</td>
                            <td class="px-4 py-3 text-sm text-black">{{ $result['criterion'] ?? '' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $result['category'] ?? '' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($result['na'] ?? false)
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">N/A</span>
                                @elseif($result['pass'] ?? false)
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">PASS</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">FAIL</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $result['details'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
