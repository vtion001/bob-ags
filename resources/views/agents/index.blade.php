@extends('layouts.app')

@section('title', 'Agent Profiles')

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
        <div class="flex flex-col gap-4 mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-black">Agent Profiles</h1>
                <form method="GET" action="{{ route('agents.sync') }}">
                    <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg font-medium">
                        Sync from CTM
                    </button>
                </form>
            </div>

            <!-- Filter Bar -->
            <div class="bg-gray-50 rounded-lg p-4">
                <form method="POST" action="{{ route('agents.save-filters') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label for="user_group" class="text-xs font-medium text-gray-600 uppercase tracking-wider">User Group</label>
                        <select name="user_group" id="user_group" class="rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black text-sm min-w-[180px]">
                            <option value="">All Groups</option>
                            @foreach($userGroups as $group)
                                <option value="{{ $group }}" {{ $savedUserGroup === $group ? 'selected' : '' }}>
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label for="email_domain" class="text-xs font-medium text-gray-600 uppercase tracking-wider">Email Domain</label>
                        <input
                            type="text"
                            name="email_domain"
                            id="email_domain"
                            value="{{ $savedEmailDomain }}"
                            placeholder="@yourcompany.com"
                            class="rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black text-sm min-w-[180px]"
                        />
                    </div>
                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium text-sm">
                        Save Filters
                    </button>
                </form>
                @if($savedEmailDomain || $savedUserGroup)
                    <p class="text-xs text-gray-500 mt-2">
                        Active filters:
                        @if($savedUserGroup)
                            <span class="font-medium">Group: {{ $savedUserGroup }}</span>
                        @endif
                        @if($savedEmailDomain)
                            <span class="font-medium ml-2">Domain: {{ $savedEmailDomain }}</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-500">Total Agents</p>
                <p class="text-2xl font-bold text-navy-900">{{ $agents->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-500">Linked to Users</p>
                <p class="text-2xl font-bold text-green-600">{{ $linkedAgents->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm text-gray-500">Unlinked Agents</p>
                <p class="text-2xl font-bold text-orange-600">{{ $agents->whereNull('user_id')->count() }}</p>
            </div>
        </div>

        <!-- Agents Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($agents->isEmpty())
                <p class="text-gray-500 text-center py-12">No agents found. Click "Sync from CTM" to fetch agents.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-navy-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Agent Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CTM ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Linked User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Call Count</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($agents as $agent)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-black">{{ $agent->ctm_agent_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $agent->ctm_agent_email ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">{{ $agent->ctm_agent_id }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($agent->user)
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                            {{ $agent->user->name }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            Unlinked
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-black">{{ $agent->getCallCount() }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('agents.show', $agent->id) }}" 
                                            class="text-navy-900 hover:text-navy-700 font-medium">
                                            View
                                        </a>
                                        @if(!$agent->user)
                                            <button type="button" onclick="showLinkModal({{ $agent->id }}, &quot;{{ $agent->ctm_agent_name }}&quot;)"
                                                class="text-blue-600 hover:text-blue-800 font-medium">
                                                Link
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('agents.unlink', $agent->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-800 font-medium">
                                                    Unlink
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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

<!-- Link Modal -->
<div id="linkModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-black mb-4">Link Agent to User</h3>
            <p class="text-sm text-gray-500 mb-4">Agent: <span id="modalAgentName" class="font-medium text-black"></span></p>
            <form method="POST" id="linkForm">
                @csrf
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Select User</label>
                    <select name="user_id" id="userSelect" class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black" required>
                        <option value="">Choose a user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="hideLinkModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-4 py-2 rounded-lg font-medium">
                        Link Agent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showLinkModal(agentId, agentName) {
    document.getElementById('modalAgentName').textContent = agentName;
    document.getElementById('linkForm').action = '/agents/' + agentId + '/link';
    document.getElementById('linkModal').classList.remove('hidden');
}

function hideLinkModal() {
    document.getElementById('linkModal').classList.add('hidden');
    document.getElementById('userSelect').value = '';
}
</script>
@endpush
