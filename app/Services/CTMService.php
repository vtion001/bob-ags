<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CTMService
{
    protected string $host;

    protected string $accessKey;

    protected string $secretKey;

    protected string $accountId;

    public function __construct()
    {
        $this->host = Setting::getValue('ctm_host', config('ctm.host'));
        $this->accessKey = Setting::getValue('ctm_access_key', config('ctm.access_key'));
        $this->secretKey = Setting::getValue('ctm_secret_key', config('ctm.secret_key'));
        $this->accountId = Setting::getValue('ctm_account_id', config('ctm.account_id'));
    }

    public function getAuthHeader(): string
    {
        return 'Basic '.base64_encode($this->accessKey.':'.$this->secretKey);
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => $this->getAuthHeader(),
            'Content-Type' => 'application/json',
        ];
    }

    public function getAccounts(): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CTM getAccounts error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM getAccounts exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getCalls(array $params = []): ?array
    {
        try {
            $defaultParams = [
                'limit' => 100,
            ];

            if (! isset($params['start_date']) && ! isset($params['end_date'])) {
                $defaultParams['hours'] = 24;
            }

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/calls.json", array_merge($defaultParams, $params));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CTM getCalls error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM getCalls exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getCallsByDateRange(string $startDate, string $endDate, int $limit = 100): ?array
    {
        return $this->getCalls([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
        ]);
    }

    public function getAgentsBySource(string $source, int $lookbackDays = 90): ?array
    {
        $data = $this->getCalls([
            'start_date' => now()->subDays($lookbackDays)->startOfDay()->toIso8601String(),
            'end_date' => now()->endOfDay()->toIso8601String(),
            'source' => $source,
            'limit' => 1000,
        ]);

        if (! $data || ! isset($data['calls'])) {
            return null;
        }

        $agents = [];
        foreach ($data['calls'] as $call) {
            $agentId = $call['agent_id'] ?? null;
            if ($agentId && ! isset($agents[$agentId])) {
                $agents[$agentId] = [
                    'ctm_agent_id' => $agentId,
                    'ctm_agent_name' => $call['agent']['name'] ?? $call['agent_name'] ?? 'Unknown',
                    'ctm_agent_email' => $call['agent']['email'] ?? null,
                    'source' => $source,
                ];
            }
        }

        return array_values($agents);
    }

    public function getUserGroups(): ?array
    {
        try {
            $allUsers = [];
            $page = 1;
            $perPage = 500;

            do {
                $response = Http::withHeaders($this->getHeaders())
                    ->withoutVerifying()
                    ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/users.json", [
                        'page' => $page,
                        'limit' => $perPage,
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $data = $response->json();
                $users = $data['users'] ?? (is_array($data) ? $data : []);
                $allUsers = array_merge($allUsers, $users);

                $page++;
                $hasMore = isset($data['next_page']) && $data['next_page']
                    || isset($data['has_more']) && $data['has_more']
                    || count($users) === $perPage;
            } while ($hasMore);

            $groups = [];
            foreach ($allUsers as $user) {
                $group = $user['user_group'] ?? $user['group'] ?? null;
                if ($group && ! in_array($group, $groups)) {
                    $groups[] = $group;
                }
            }

            sort($groups);

            return $groups;
        } catch (\Exception $e) {
            Log::error('CTM getUserGroups exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getAgentById(string $agentId): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/users/{$agentId}.json");

            if (! $response->successful()) {
                return null;
            }

            $user = $response->json();
            $firstName = $user['first_name'] ?? null;
            $lastName = $user['last_name'] ?? null;
            $fullName = trim("{$firstName} {$lastName}");

            return [
                'ctm_agent_id' => $user['id'] ?? null,
                'ctm_agent_name' => $fullName ?: 'Unknown',
                'ctm_agent_email' => $user['email'] ?? null,
                'user_group' => $user['user_group'] ?? $user['group'] ?? null,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ];
        } catch (\Exception $e) {
            Log::error('CTM getAgentById exception', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getCTMUsers(?string $filterName = null): ?array
    {
        try {
            $allUsers = [];
            $page = 1;
            $perPage = 500;

            do {
                $response = Http::withHeaders($this->getHeaders())
                    ->withoutVerifying()
                    ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/users.json", [
                        'page' => $page,
                        'limit' => $perPage,
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $data = $response->json();
                $users = $data['users'] ?? (is_array($data) ? $data : []);
                $allUsers = array_merge($allUsers, $users);

                $page++;
                $hasMore = isset($data['next_page']) && $data['next_page']
                    || isset($data['has_more']) && $data['has_more']
                    || count($users) === $perPage;
            } while ($hasMore);

            $users = $allUsers;

            if ($filterName !== null) {
                $needle = strtolower($filterName);
                $users = array_values(array_filter($users, function ($user) use ($needle) {
                    $name = strtolower($user['name'] ?? '');
                    $group = strtolower($user['user_group'] ?? $user['group'] ?? '');

                    return str_contains($name, $needle) || str_contains($group, $needle);
                }));
            }

            return array_map(function ($u) {
                $firstName = $u['first_name'] ?? null;
                $lastName = $u['last_name'] ?? null;
                $fullName = trim("{$firstName} {$lastName}");

                return [
                    'ctm_agent_id' => $u['id'] ?? null,
                    'ctm_agent_name' => $fullName ?: 'Unknown',
                    'ctm_agent_email' => $u['email'] ?? null,
                    'user_group' => $u['user_group'] ?? $u['group'] ?? null,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ];
            }, $users);
        } catch (\Exception $e) {
            Log::error('CTM getCTMUsers exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getCall(string $callId): ?array
    {
        try {
            $url = "https://{$this->host}/api/v1/accounts/{$this->accountId}/calls/{$callId}";

            Log::debug('CTM getCall request', [
                'call_id' => $callId,
                'url' => $url,
            ]);

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                // Extract all keys including nested objects
                $allKeys = $this->extractAllKeys($data);
                $potentialFields = $this->findPotentialRecordingFields($data);

                // Get actual values of potential recording fields
                $fieldValues = [];
                foreach ($potentialFields as $field) {
                    $value = $this->getNestedValue($data, $field);
                    if ($value !== null) {
                        $fieldValues[$field] = is_array($value) ? json_encode($value) : $value;
                    }
                }

                Log::debug('CTM getCall response', [
                    'call_id' => $callId,
                    'has_data' => ! empty($data),
                    'top_level_fields' => array_keys($data ?? []),
                    'all_nested_keys' => $allKeys,
                    'potential_recording_fields' => $potentialFields,
                    'field_values' => $fieldValues,
                    'audio_field' => $data['audio'] ?? 'NOT PRESENT',
                    'is_s3_link_field' => $data['is_s3_link'] ?? 'NOT PRESENT',
                ]);

                return $data;
            }

            Log::error('CTM getCall error', [
                'call_id' => $callId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM getCall exception', [
                'call_id' => $callId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function extractAllKeys(array $data, string $prefix = ''): array
    {
        $keys = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
            $keys[] = $fullKey;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->extractAllKeys($value, $fullKey));
            }
        }

        return $keys;
    }

    protected function findPotentialRecordingFields(array $data): array
    {
        $potentialFields = [];
        $recordingPatterns = ['recording', 'record', 'audio', 'media', 'url', 'link', 'path', 'file', 'src', 'href'];

        $allKeys = $this->extractAllKeys($data);
        foreach ($allKeys as $key) {
            $keyLower = strtolower($key);
            foreach ($recordingPatterns as $pattern) {
                if (str_contains($keyLower, $pattern)) {
                    $potentialFields[] = $key;
                    break;
                }
            }
        }

        return $potentialFields;
    }

    protected function getNestedValue(array $data, string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (! is_array($value) || ! array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function getCallRecording(string $callId): ?array
    {
        $recordingEndpoints = [
            "/api/v1/accounts/{$this->accountId}/calls/{$callId}/recording",
            "/api/v1/accounts/{$this->accountId}/calls/{$callId}/recordings",
            "/api/v1/accounts/{$this->accountId}/calls/{$callId}/media",
            "/api/v1/accounts/{$this->accountId}/recordings/call/{$callId}",
        ];

        Log::debug('CTM getCallRecording: Testing endpoints', [
            'call_id' => $callId,
            'endpoints' => $recordingEndpoints,
        ]);

        foreach ($recordingEndpoints as $endpoint) {
            try {
                $url = "https://{$this->host}{$endpoint}";

                Log::debug('CTM getCallRecording: Testing endpoint', [
                    'call_id' => $callId,
                    'endpoint' => $endpoint,
                    'url' => $url,
                ]);

                $response = Http::withHeaders($this->getHeaders())
                    ->withoutVerifying()
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    Log::debug('CTM getCallRecording: Endpoint returned data', [
                        'call_id' => $callId,
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'response_keys' => array_keys($data ?? []),
                    ]);

                    // Check if response contains recording data
                    if ($this->hasRecordingData($data)) {
                        $recordingUrl = $this->extractRecordingUrl($data);

                        Log::info('CTM getCallRecording: Found recording', [
                            'call_id' => $callId,
                            'endpoint' => $endpoint,
                            'recording_url' => $recordingUrl ? substr($recordingUrl, 0, 100).'...' : null,
                        ]);

                        return [
                            'url' => $recordingUrl,
                            'source_endpoint' => $endpoint,
                            'raw_response' => $data,
                        ];
                    }
                } else {
                    Log::debug('CTM getCallRecording: Endpoint not found', [
                        'call_id' => $callId,
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('CTM getCallRecording: Endpoint error', [
                    'call_id' => $callId,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::debug('CTM getCallRecording: No recording found on any endpoint', [
            'call_id' => $callId,
        ]);

        return null;
    }

    protected function hasRecordingData(?array $data): bool
    {
        if (! $data || empty($data)) {
            return false;
        }

        $recordingKeys = ['recording_url', 'recording', 'url', 'audio_url', 'media_url', 'link', 'file_url', 'download_url', 'mp3', 'wav'];

        foreach ($recordingKeys as $key) {
            if (isset($data[$key]) && ! empty($data[$key])) {
                return true;
            }
        }

        // Check for URL-like values
        foreach ($data as $value) {
            if (is_string($value) && $this->isAudioUrl($value)) {
                return true;
            }
        }

        return false;
    }

    protected function extractRecordingUrl(array $data): ?string
    {
        $urlKeys = ['recording_url', 'url', 'audio_url', 'media_url', 'link', 'file_url', 'download_url'];

        foreach ($urlKeys as $key) {
            if (isset($data[$key]) && ! empty($data[$key])) {
                $url = $data[$key];
                if ($this->isAudioUrl($url)) {
                    return $url;
                }
            }
        }

        // Look for URL-like values
        foreach ($data as $value) {
            if (is_string($value) && $this->isAudioUrl($value)) {
                return $value;
            }
        }

        return null;
    }

    protected function isAudioUrl(string $value): bool
    {
        $audioExtensions = ['.mp3', '.wav', '.aac', '.ogg', '.m4a', '.flac', '.wma'];
        $valueLower = strtolower($value);

        foreach ($audioExtensions as $ext) {
            if (str_contains($valueLower, $ext)) {
                return true;
            }
        }

        // Also check for common audio hosting patterns
        if (str_contains($valueLower, 'recording') ||
            str_contains($valueLower, 'audio') ||
            str_contains($valueLower, 'media')) {
            return true;
        }

        // Check if it's a valid URL with audio content type pattern
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        }

        return false;
    }

    public function getCallTranscript(string $callId): ?array
    {
        try {
            $url = "https://{$this->host}/api/v1/accounts/{$this->accountId}/calls/{$callId}/transcript";

            Log::debug('CTM getCallTranscript request', [
                'call_id' => $callId,
                'url' => $url,
            ]);

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                Log::debug('CTM getCallTranscript response', [
                    'call_id' => $callId,
                    'has_data' => ! empty($data),
                    'available_fields' => array_keys($data ?? []),
                    'has_transcript' => isset($data['transcript']),
                    'transcript_length' => isset($data['transcript']) ? strlen($data['transcript']) : 0,
                ]);

                return $data;
            }

            Log::error('CTM getCallTranscript error', [
                'call_id' => $callId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM getCallTranscript exception', [
                'call_id' => $callId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getSources(): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/sources");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CTM getSources error', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM getSources exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getNumbers(): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/numbers");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CTM getNumbers error', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM getNumbers exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function searchNumbers(array $params = []): ?array
    {
        try {
            $defaultParams = [
                'country' => 'US',
                'searchby' => 'area',
                'areacode' => '443',
            ];

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/numbers/search.json", array_merge($defaultParams, $params));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CTM searchNumbers error', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('CTM searchNumbers exception', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
