@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Knowledge Base</h2>
        <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-[#0A1628] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1A2640] focus:bg-[#1A2640] active:bg-[#0A1628] transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Entry
        </button>
    </div>
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="search-input" placeholder="Search knowledge base..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent"
                        value="{{ request('search') }}">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="md:w-48">
                <select id="category-filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Knowledge Base List -->
    <div class="bg-white rounded-lg shadow">
        <div class="divide-y divide-gray-200">
            @forelse($knowledgeBases as $kb)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($kb->category === 'company') bg-blue-100 text-blue-800
                                    @elseif($kb->category === 'account') bg-green-100 text-green-800
                                    @elseif($kb->category === 'resource') bg-purple-100 text-purple-800
                                    @elseif($kb->category === 'faq') bg-yellow-100 text-yellow-800
                                    @elseif($kb->category === 'script') bg-pink-100 text-pink-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $categories[$kb->category] ?? $kb->category }}
                                </span>
                                @if(!$kb->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $kb->title }}</h3>
                            @if($kb->description)
                                <p class="mt-1 text-sm text-gray-500">{{ Str::limit($kb->description, 150) }}</p>
                            @endif
                            @if($kb->tags)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach($kb->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <a href="{{ route('knowledge-base.edit', $kb->id) }}" class="p-2 text-gray-400 hover:text-[#0A1628] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('knowledge-base.destroy', $kb->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400">
                        <span>Updated {{ $kb->updated_at->diffForHumans() }}</span>
                        @if($kb->entries->count() > 0)
                            <span class="mx-2">•</span>
                            <span>{{ $kb->entries->count() }} chunks indexed</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No knowledge base entries</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first entry.</p>
                    <div class="mt-6">
                        <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-[#0A1628] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1A2640] transition-colors">
                            Add Entry
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $knowledgeBases->links() }}
    </div>

    <!-- Create Modal -->
    <div id="create-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Add Knowledge Base Entry</h3>
                    <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('knowledge-base.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                            <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma separated)</label>
                                <input type="text" name="tags" placeholder="insurance, va, mental-health" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <textarea name="content" rows="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent font-mono text-sm" placeholder="Enter the knowledge base content here. This will be automatically chunked for AI retrieval."></textarea>
                            <p class="mt-1 text-xs text-gray-500">Content will be automatically split into smaller chunks for efficient AI retrieval.</p>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-4 h-4 text-[#0A1628] border-gray-300 rounded focus:ring-[#0A1628]">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">Active (visible to AI)</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeCreateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-[#0A1628] border border-transparent rounded-lg text-sm font-medium text-white hover:bg-[#1A2640]">Create Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
        }
    });

    // Search with debounce
    let searchTimeout;
    document.getElementById('search-input').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            const search = e.target.value;
            const category = document.getElementById('category-filter').value;
            const url = new URL(window.location);
            if (search) url.searchParams.set('search', search);
            else url.searchParams.delete('search');
            if (category) url.searchParams.set('category', category);
            else url.searchParams.delete('category');
            window.location = url;
        }, 500);
    });

    // Category filter
    document.getElementById('category-filter').addEventListener('change', function(e) {
        const category = e.target.value;
        const search = document.getElementById('search-input').value;
        const url = new URL(window.location);
        if (category) url.searchParams.set('category', category);
        else url.searchParams.delete('category');
        if (search) url.searchParams.set('search', search);
        window.location = url;
    });
</script>
@endpush
