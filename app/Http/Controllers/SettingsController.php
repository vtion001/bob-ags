<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'ctm_access_key' => Setting::getValue('ctm_access_key', config('ctm.access_key')),
            'ctm_secret_key' => Setting::getValue('ctm_secret_key', config('ctm.secret_key')),
            'ctm_account_id' => Setting::getValue('ctm_account_id', config('ctm.account_id')),
            'openrouter_api_key' => Setting::getValue('openrouter_api_key', config('openrouter.api_key')),
            'assemblyai_api_key' => Setting::getValue('assemblyai_api_key', config('assemblyai.api_key')),
        ];

        return view('settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ctm_access_key' => 'nullable|string',
            'ctm_secret_key' => 'nullable|string',
            'ctm_account_id' => 'nullable|string',
            'openrouter_api_key' => 'nullable|string',
            'assemblyai_api_key' => 'nullable|string',
        ]);

        Setting::setValue('ctm_access_key', $validated['ctm_access_key'] ?? '');
        Setting::setValue('ctm_secret_key', $validated['ctm_secret_key'] ?? '');
        Setting::setValue('ctm_account_id', $validated['ctm_account_id'] ?? '');
        Setting::setValue('openrouter_api_key', $validated['openrouter_api_key'] ?? '');
        Setting::setValue('assemblyai_api_key', $validated['assemblyai_api_key'] ?? '');

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
