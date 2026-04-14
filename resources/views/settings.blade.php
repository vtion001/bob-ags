@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-black mb-6">Settings</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- CTM Settings -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-black mb-4">CTM API Settings</h2>
            <form method="POST" action="{{ route('settings.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="ctm_access_key" class="block text-sm font-medium text-gray-700 mb-1">CTM Access Key</label>
                        <input type="text" name="ctm_access_key" id="ctm_access_key" 
                            value="{{ config('ctm.access_key') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="ctm_secret_key" class="block text-sm font-medium text-gray-700 mb-1">CTM Secret Key</label>
                        <input type="password" name="ctm_secret_key" id="ctm_secret_key"
                            value="{{ config('ctm.secret_key') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="ctm_account_id" class="block text-sm font-medium text-gray-700 mb-1">CTM Account ID</label>
                        <input type="text" name="ctm_account_id" id="ctm_account_id"
                            value="{{ config('ctm.account_id') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                </div>
        </div>

        <!-- OpenRouter Settings -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-black mb-4">AI Analysis (OpenRouter)</h2>
            <div class="space-y-4">
                <div>
                    <label for="openrouter_api_key" class="block text-sm font-medium text-gray-700 mb-1">OpenRouter API Key</label>
                    <input type="password" name="openrouter_api_key" id="openrouter_api_key"
                        value="{{ config('openrouter.api_key') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    <p class="text-sm text-gray-500 mt-1">Used for Claude AI-powered call analysis</p>
                </div>
            </div>
        </div>

        <!-- AssemblyAI Settings -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-black mb-4">AssemblyAI Settings</h2>
            <div class="space-y-4">
                <div>
                    <label for="assemblyai_api_key" class="block text-sm font-medium text-gray-700 mb-1">AssemblyAI API Key</label>
                    <input type="password" name="assemblyai_api_key" id="assemblyai_api_key"
                        value="{{ config('assemblyai.api_key') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-navy-900 hover:bg-navy-800 text-white px-6 py-2 rounded-lg font-medium">
                Save Settings
            </button>
        </div>
        </form>
    </div>
</div>
@endsection
