@extends('layouts.app')

@section('title', 'Edit - ' . $kb->title)

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('knowledge-base.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Edit: {{ $kb->title }}</h2>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('knowledge-base.update', $kb->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $kb->title) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ $kb->category === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma separated)</label>
                        <input type="text" name="tags" value="{{ old('tags', implode(', ', $kb->tags ?? [])) }}" placeholder="insurance, va, mental-health" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent">{{ old('description', $kb->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" rows="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0A1628] focus:border-transparent font-mono text-sm">{{ old('content', $kb->content) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Content will be automatically split into smaller chunks for AI retrieval.</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $kb->is_active ? 'checked' : '' }} class="w-4 h-4 text-[#0A1628] border-gray-300 rounded focus:ring-[#0A1628]">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active (visible to AI)</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('knowledge-base.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-[#0A1628] border border-transparent rounded-lg text-sm font-medium text-white hover:bg-[#1A2640]">Update Entry</button>
            </div>
        </form>
    </div>
@endsection
