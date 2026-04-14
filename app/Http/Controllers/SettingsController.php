<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'ctm_host' => Setting::getValue('ctm_host', config('ctm.host')),
            'ctm_access_key' => Setting::getValue('ctm_access_key', config('ctm.access_key')),
            'ctm_secret_key' => Setting::getValue('ctm_secret_key', config('ctm.secret_key')),
            'ctm_account_id' => Setting::getValue('ctm_account_id', config('ctm.account_id')),
            'ai_provider' => Setting::getValue('ai_provider', 'openai'),
            'openai_api_key' => Setting::getValue('openai_api_key', config('openai.api_key')),
            'openai_model' => Setting::getValue('openai_model', config('openai.default_model')),
            'anthropic_api_key' => Setting::getValue('anthropic_api_key', config('anthropic.api_key')),
            'anthropic_model' => Setting::getValue('anthropic_model', config('anthropic.default_model')),
            'openrouter_api_key' => Setting::getValue('openrouter_api_key', config('openrouter.api_key')),
            'openrouter_model' => Setting::getValue('openrouter_model', config('openrouter.default_model')),
            'assemblyai_api_key' => Setting::getValue('assemblyai_api_key', config('assemblyai.api_key')),
        ];

        return view('settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ctm_host' => 'nullable|string',
            'ctm_access_key' => 'nullable|string',
            'ctm_secret_key' => 'nullable|string',
            'ctm_account_id' => 'nullable|string',
            'ai_provider' => 'nullable|string|in:openrouter,openai,anthropic',
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'anthropic_api_key' => 'nullable|string',
            'anthropic_model' => 'nullable|string',
            'openrouter_api_key' => 'nullable|string',
            'openrouter_model' => 'nullable|string',
            'assemblyai_api_key' => 'nullable|string',
        ]);

        Setting::setValue('ctm_host', $validated['ctm_host'] ?? '');
        Setting::setValue('ctm_access_key', $validated['ctm_access_key'] ?? '');
        Setting::setValue('ctm_secret_key', $validated['ctm_secret_key'] ?? '');
        Setting::setValue('ctm_account_id', $validated['ctm_account_id'] ?? '');
        Setting::setValue('ai_provider', $validated['ai_provider'] ?? 'openai');
        Setting::setValue('openai_api_key', $validated['openai_api_key'] ?? '');
        Setting::setValue('openai_model', $validated['openai_model'] ?? 'gpt-4o-mini');
        Setting::setValue('anthropic_api_key', $validated['anthropic_api_key'] ?? '');
        Setting::setValue('anthropic_model', $validated['anthropic_model'] ?? 'claude-3-5-sonnet-20241022');
        Setting::setValue('openrouter_api_key', $validated['openrouter_api_key'] ?? '');
        Setting::setValue('openrouter_model', $validated['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet');
        Setting::setValue('assemblyai_api_key', $validated['assemblyai_api_key'] ?? '');

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
