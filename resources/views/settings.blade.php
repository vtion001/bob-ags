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

        <form method="POST" action="{{ route('settings.store') }}">
            @csrf

            <!-- CTM Settings -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-black mb-4">CTM API Settings</h2>
                <div class="space-y-4">
                    <div>
                        <label for="ctm_host" class="block text-sm font-medium text-gray-700 mb-1">CTM Host</label>
                        <input type="text" name="ctm_host" id="ctm_host" 
                            value="{{ $settings['ctm_host'] }}"
                            placeholder="api.calltrackingmetrics.com"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="ctm_access_key" class="block text-sm font-medium text-gray-700 mb-1">CTM Access Key</label>
                        <input type="text" name="ctm_access_key" id="ctm_access_key" 
                            value="{{ $settings['ctm_access_key'] }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="ctm_secret_key" class="block text-sm font-medium text-gray-700 mb-1">CTM Secret Key</label>
                        <input type="password" name="ctm_secret_key" id="ctm_secret_key"
                            value="{{ $settings['ctm_secret_key'] }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="ctm_account_id" class="block text-sm font-medium text-gray-700 mb-1">CTM Account ID</label>
                        <input type="text" name="ctm_account_id" id="ctm_account_id"
                            value="{{ $settings['ctm_account_id'] }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                </div>
            </div>

            <!-- AI Provider Selection -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-black mb-4">AI Provider</h2>
                <div>
                    <label for="ai_provider" class="block text-sm font-medium text-gray-700 mb-1">Select AI Provider</label>
                    <select name="ai_provider" id="ai_provider"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                        <option value="openai" {{ ($settings['ai_provider'] ?? 'openai') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                        <option value="anthropic" {{ ($settings['ai_provider'] ?? 'openai') == 'anthropic' ? 'selected' : '' }}>Anthropic</option>
                        <option value="openrouter" {{ ($settings['ai_provider'] ?? 'openai') == 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Select which AI provider to use for call analysis and suggestions</p>
                </div>
            </div>

            <!-- OpenAI Settings -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-black mb-4">OpenAI Direct</h2>
                <div class="space-y-4">
                    <div>
                        <label for="openai_api_key" class="block text-sm font-medium text-gray-700 mb-1">OpenAI API Key</label>
                        <input type="password" name="openai_api_key" id="openai_api_key"
                            value="{{ $settings['openai_api_key'] }}"
                            placeholder="sk-..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="openai_model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <select name="openai_model" id="openai_model"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                            <option value="gpt-4o-2024-11-20" {{ ($settings['openai_model'] ?? '') == 'gpt-4o-2024-11-20' ? 'selected' : '' }}>GPT-4o (2024-11-20)</option>
                            <option value="gpt-4o-mini-2024-07-18" {{ ($settings['openai_model'] ?? '') == 'gpt-4o-mini-2024-07-18' ? 'selected' : '' }}>GPT-4o Mini (2024-07-18)</option>
                            <option value="gpt-4o" {{ ($settings['openai_model'] ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                            <option value="gpt-4o-mini" {{ ($settings['openai_model'] ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini</option>
                            <option value="gpt-4-turbo-2024-04-09" {{ ($settings['openai_model'] ?? '') == 'gpt-4-turbo-2024-04-09' ? 'selected' : '' }}>GPT-4 Turbo (2024-04-09)</option>
                            <option value="gpt-4-0613" {{ ($settings['openai_model'] ?? '') == 'gpt-4-0613' ? 'selected' : '' }}>GPT-4 (June 2023)</option>
                            <option value="gpt-3.5-turbo-0125" {{ ($settings['openai_model'] ?? '') == 'gpt-3.5-turbo-0125' ? 'selected' : '' }}>GPT-3.5 Turbo (2025-01)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Anthropic Settings -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-black mb-4">Anthropic Direct</h2>
                <div class="space-y-4">
                    <div>
                        <label for="anthropic_api_key" class="block text-sm font-medium text-gray-700 mb-1">Anthropic API Key</label>
                        <input type="password" name="anthropic_api_key" id="anthropic_api_key"
                            value="{{ $settings['anthropic_api_key'] }}"
                            placeholder="sk-ant-..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                    </div>
                    <div>
                        <label for="anthropic_model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <select name="anthropic_model" id="anthropic_model"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                            <option value="claude-3-5-sonnet-20241022" {{ ($settings['anthropic_model'] ?? '') == 'claude-3-5-sonnet-20241022' ? 'selected' : '' }}>Claude 3.5 Sonnet (Oct 2024)</option>
                            <option value="claude-3-5-sonnet-20240620" {{ ($settings['anthropic_model'] ?? '') == 'claude-3-5-sonnet-20240620' ? 'selected' : '' }}>Claude 3.5 Sonnet (June 2024)</option>
                            <option value="claude-3-5-haiku-20241022" {{ ($settings['anthropic_model'] ?? '') == 'claude-3-5-haiku-20241022' ? 'selected' : '' }}>Claude 3.5 Haiku (Oct 2024)</option>
                            <option value="claude-3-opus-20240229" {{ ($settings['anthropic_model'] ?? '') == 'claude-3-opus-20240229' ? 'selected' : '' }}>Claude 3 Opus</option>
                            <option value="claude-3-sonnet-20240229" {{ ($settings['anthropic_model'] ?? '') == 'claude-3-sonnet-20240229' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                            <option value="claude-3-haiku-20240307" {{ ($settings['anthropic_model'] ?? '') == 'claude-3-haiku-20240307' ? 'selected' : '' }}>Claude 3 Haiku</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- OpenRouter Settings -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-black mb-4">OpenRouter</h2>
                <div class="space-y-4">
                    <div>
                        <label for="openrouter_api_key" class="block text-sm font-medium text-gray-700 mb-1">OpenRouter API Key</label>
                        <input type="password" name="openrouter_api_key" id="openrouter_api_key"
                            value="{{ $settings['openrouter_api_key'] }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                        <p class="text-sm text-gray-500 mt-1">OpenRouter provides access to multiple AI models through a unified API</p>
                    </div>
                    <div>
                        <label for="openrouter_model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                        <select name="openrouter_model" id="openrouter_model"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy-900 focus:ring-navy-900 text-black">
                            <optgroup label="Anthropic">
                                <option value="anthropic/claude-3.5-sonnet" {{ ($settings['openrouter_model'] ?? '') == 'anthropic/claude-3.5-sonnet' ? 'selected' : '' }}>Claude 3.5 Sonnet</option>
                                <option value="anthropic/claude-3.5-haiku" {{ ($settings['openrouter_model'] ?? '') == 'anthropic/claude-3.5-haiku' ? 'selected' : '' }}>Claude 3.5 Haiku</option>
                                <option value="anthropic/claude-3-opus" {{ ($settings['openrouter_model'] ?? '') == 'anthropic/claude-3-opus' ? 'selected' : '' }}>Claude 3 Opus</option>
                                <option value="anthropic/claude-3-sonnet" {{ ($settings['openrouter_model'] ?? '') == 'anthropic/claude-3-sonnet' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                                <option value="anthropic/claude-3-haiku" {{ ($settings['openrouter_model'] ?? '') == 'anthropic/claude-3-haiku' ? 'selected' : '' }}>Claude 3 Haiku</option>
                            </optgroup>
                            <optgroup label="OpenAI">
                                <option value="openai/gpt-4o" {{ ($settings['openrouter_model'] ?? '') == 'openai/gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                                <option value="openai/gpt-4o-mini" {{ ($settings['openrouter_model'] ?? '') == 'openai/gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini</option>
                                <option value="openai/gpt-4-turbo" {{ ($settings['openrouter_model'] ?? '') == 'openai/gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="google/gemini-2.0-flash-exp" {{ ($settings['openrouter_model'] ?? '') == 'google/gemini-2.0-flash-exp' ? 'selected' : '' }}>Gemini 2.0 Flash</option>
                                <option value="meta-llama/llama-3-70b-instruct" {{ ($settings['openrouter_model'] ?? '') == 'meta-llama/llama-3-70b-instruct' ? 'selected' : '' }}>Llama 3 70B</option>
                                <option value="mistralai/mistral-large" {{ ($settings['openrouter_model'] ?? '') == 'mistralai/mistral-large' ? 'selected' : '' }}>Mistral Large</option>
                                <option value="cohere/command-r-plus" {{ ($settings['openrouter_model'] ?? '') == 'cohere/command-r-plus' ? 'selected' : '' }}>Command R+</option>
                            </optgroup>
                        </select>
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
                            value="{{ $settings['assemblyai_api_key'] }}"
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
