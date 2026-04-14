<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
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

        foreach ($validated as $key => $value) {
            if (in_array($key, ['ctm_access_key', 'ctm_secret_key', 'ctm_account_id'])) {
                config(['ctm.' . $key => $value]);
            } elseif ($key === 'openrouter_api_key') {
                config(['openrouter.' . $key => $value]);
            } elseif ($key === 'assemblyai_api_key') {
                config(['assemblyai.' . $key => $value]);
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
