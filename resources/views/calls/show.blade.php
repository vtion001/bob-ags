@extends('layouts.app')

@section('title', 'Call Details')

@section('content')
{{-- Debug: Show call data in development --}}
@if(config('app.debug'))
<div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4">
    <details>
        <summary class="font-bold cursor-pointer">Debug: Call Data (Click to expand)</summary>
        <pre class="mt-2 text-xs overflow-auto max-h-96">{{ print_r([
            'id' => $call->id,
            'ctm_call_id' => $call->ctm_call_id,
            'ctm_sid' => $call->ctm_sid,
            'caller_number' => $call->caller_number,
            'direction' => $call->direction,
            'duration' => $call->duration,
            'agent_name' => $call->agent_name,
            'agent_id' => $call->agent_id,
            'call_datetime' => $call->call_datetime,
            'source' => $call->source,
            'tracking_label' => $call->tracking_label,
            'recording_url' => $call->recording_url,
            'recording_url_present' => !empty($call->recording_url),
            'transcript_text_present' => !empty($call->transcript_text),
            'transcript_text_length' => $call->transcript_text ? strlen($call->transcript_text) : 0,
            'status' => $call->status,
            'has_qaLog' => $call->qaLog ? 'Yes' : 'No',
            'qaLog_total_score' => $call->qaLog ? $call->qaLog->getAttribute('total_score') : null,
        ], true) }}</pre>
    </details>
</div>
@endif

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
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-black">Call Details</h2>
                        @if($call->transferred)
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                            🔄 Transferred
                        </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Phone Number</p>
                            <p class="font-medium text-black">{{ $call->caller_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Direction</p>
                            <p class="font-medium text-black">{{ ucfirst($call->direction ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date & Time</p>
                            <p class="font-medium text-black">{{ $call->call_datetime ? $call->call_datetime->format('M d, Y g:i A') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Duration</p>
                            <p class="font-medium text-black">{{ $call->duration ?? 0 }} seconds</p>
                        </div>
                        @if($call->talk_time)
                        <div>
                            <p class="text-sm text-gray-500">Talk Time</p>
                            <p class="font-medium text-black">{{ $call->talk_time }} seconds</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm text-gray-500">Agent</p>
                            <p class="font-medium text-black">{{ $call->agent_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Source</p>
                            <p class="font-medium text-black">{{ $call->source ?? $call->tracking_label ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recording Player -->
                @if($call->recording_url)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-black mb-4">Recording</h2>
                    <audio controls class="w-full mb-4">
                        <source src="{{ route('calls.recording', $call->ctm_call_id) }}">
                        Your browser does not support the audio element.
                    </audio>
                    <div class="flex gap-2">
                        @if(!$call->transcript_text && $call->recording_url)
                        <form method="POST" action="{{ route('calls.transcribe', $call->ctm_call_id ?? $call->id) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-navy-900 hover:bg-navy-800 text-white px-4 py-3 rounded-lg font-medium">
                                Transcribe Recording
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('calls.recording-download', $call->ctm_call_id) }}" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-medium text-center">
                            Download
                        </a>
                    </div>
                </div>
                @endif

                <!-- Transcript -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-black mb-4">Transcript</h2>
                    <div id="transcript-container">
                        @if(!empty($call->transcript_text))
                            <div id="transcript-text" class="prose max-w-none text-black">
                                {!! nl2br(e($call->transcript_text)) !!}
                            </div>
                        @elseif($isTranscribing)
                            <div id="transcript-loading" class="flex flex-col items-center justify-center py-8">
                                <svg class="animate-spin h-8 w-8 text-navy-900 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Transcribing recording...</p>
                            </div>
                        @else
                            <p class="text-gray-500">No transcript available.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: QA Analysis -->
            <div class="space-y-6">
                <!-- Score Card -->
                <div class="bg-navy-900 rounded-lg p-6 text-white">
                    <h2 class="text-lg font-semibold mb-4">QA Score</h2>
                    @if($call->qaLog && $call->qaLog->total_score !== null)
                        <div class="text-center">
                            <p class="text-5xl font-bold">{{ $call->qaLog->total_score }}</p>
                            <p class="text-gray-300 mt-1">/ 100</p>
                            <p class="text-sm mt-2 
                                @if(($call->qaLog->sentiment ?? '') === 'positive') text-green-300
                                @elseif(($call->qaLog->sentiment ?? '') === 'neutral') text-yellow-300
                                @else text-red-300
                                @endif">
                                {{ ucfirst($call->qaLog->sentiment ?? 'N/A') }}
                            </p>
                            @php
                                $criteriaScores = $call->qaLog->criteria_scores ?? [];
                                $passed = collect($criteriaScores)->filter(fn($c) => ($c['pass'] ?? false) || ($c['na'] ?? false))->count();
                                $total = 25;
                            @endphp
                            <p class="text-xs text-gray-400 mt-2">{{ $passed }}/{{ $total }} criteria passed</p>
                        </div>
                    @else
                        <p class="text-center text-gray-300">Not analyzed</p>
                    @endif
                </div>

                <!-- Disposition Card -->
                @if($call->qaLog && !empty($call->qaLog->disposition))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-black mb-2">Disposition</h2>
                    @php
                        $dispositionLabels = [
                            'qualified' => 'Qualified Lead',
                            'warm' => 'Warm Lead',
                            'refer' => 'Refer',
                            'do-not-refer' => 'Do Not Refer',
                            'auto-fail' => 'Critical Violation',
                            'unqualified' => 'Unqualified',
                        ];
                        $disposition = $call->qaLog->disposition;
                        $dispositionLabel = $dispositionLabels[$disposition] ?? ucfirst(str_replace('-', ' ', $disposition));
                    @endphp
                    <p class="text-black font-medium">{{ $dispositionLabel }}</p>
                    @if($disposition === 'auto-fail' || $disposition === 'unqualified')
                        <p class="text-sm text-red-600 mt-1">Requires supervisor review</p>
                    @endif
                </div>
                @endif

                <!-- Tags -->
                @if($call->qaLog && !empty($call->qaLog->notes))
                @php
                    $notes = $call->qaLog->notes;
                    preg_match_all('/\b(excellent|good|needs-improvement|poor|unqualified-transfer|hipaa-risk|medical-advice-risk|ztp-violation)\b/i', $notes, $matches);
                    $tags = array_unique($matches[0] ?? []);
                @endphp
                @if(!empty($tags))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-black mb-3">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <span class="px-3 py-1 bg-navy-900 text-white rounded-full text-xs font-medium">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                <!-- Run Analysis Button -->
                @if(!$call->qaLog || $call->qaLog->total_score === null)
                    @if($call->transcript_text || $call->recording_url)
                    <form method="POST" action="{{ route('calls.analyze', $call->ctm_call_id ?? $call->id) }}">
                        @csrf
                        <button type="submit" class="w-full bg-navy-900 hover:bg-navy-800 text-white px-4 py-3 rounded-lg font-medium">
                            Run QA Analysis
                        </button>
                    </form>
                    @endif
                @endif

                <!-- Rubric Breakdown -->
                @if($call->qaLog && !empty($call->qaLog->rubric_breakdown))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-black mb-4">Score Breakdown</h2>
                    <div class="space-y-3">
                        @foreach($call->qaLog->rubric_breakdown as $category => $data)
                            @if(is_array($data) && isset($data['score']) && isset($data['max']) && $data['max'] > 0)
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
                @if($call->qaLog && !empty($call->qaLog->criteria_scores))
                    @php
                        $ztpViolations = collect($call->qaLog->criteria_scores)->filter(function($r) {
                            return ($r['ztp'] ?? false) && !($r['pass'] ?? true);
                        });
                    @endphp
                    @if($ztpViolations->isNotEmpty())
                    <div class="bg-red-600 rounded-lg p-6 text-white">
                        <h2 class="text-lg font-semibold mb-2">ZTP Violations</h2>
                        <ul class="text-sm space-y-1">
                            @foreach($ztpViolations as $id => $violation)
                                <li>• {{ $id }}: {{ $violation['criterion'] ?? '' }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Detailed Rubric Results -->
        @if($call->qaLog && !empty($call->qaLog->criteria_scores))
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
                        @foreach($call->qaLog->criteria_scores as $id => $result)
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

        <!-- Coaching Insights -->
        @if($call->qaLog && !empty($call->qaLog->coaching_insights))
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h2 class="text-xl font-bold text-black mb-3">Coaching Insights</h2>
            <p class="text-gray-700 leading-relaxed">{{ $call->qaLog->coaching_insights }}</p>
        </div>
        @endif

        <!-- Recommended Training Focus -->
        @if($call->qaLog && !empty($call->qaLog->recommendations))
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-6">
            <h2 class="text-xl font-bold text-black mb-3">Recommended Training Focus</h2>
            <div class="text-gray-700 leading-relaxed space-y-2">
                {!! nl2br(e($call->qaLog->recommendations)) !!}
            </div>
        </div>
        @endif
    </div>
</div>

@if($isTranscribing)
@push('scripts')
<script>
(function() {
    const ctmCallId = "{{ $call->ctm_call_id }}";
    const container = document.getElementById('transcript-container');

    function poll() {
        fetch(`/calls/${ctmCallId}/transcript-status`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'complete') {
                    container.innerHTML = '<div class="prose max-w-none text-black">' + data.transcript.replace(/\n/g, '<br>') + '</div>';
                } else if (data.status === 'failed') {
                    container.innerHTML = '<p class="text-red-500">Transcription failed: ' + (data.message || 'Unknown error') + '</p>';
                } else {
                    setTimeout(poll, 3000);
                }
            })
            .catch(() => setTimeout(poll, 5000));
    }

    poll();
})();
</script>
@endpush
@endif
@endsection
