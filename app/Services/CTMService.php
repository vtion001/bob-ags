<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

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

    protected function getAuthHeader(): string
    {
        return 'Basic ' . base64_encode($this->accessKey . ':' . $this->secretKey);
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

            if (!isset($params['start_date']) && !isset($params['end_date'])) {
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
            'end_date'   => now()->endOfDay()->toIso8601String(),
            'source'     => $source,
            'limit'      => 1000,
        ]);

        if (!$data || !isset($data['calls'])) {
            return null;
        }

        $agents = [];
        foreach ($data['calls'] as $call) {
            $agentId = $call['agent_id'] ?? null;
            if ($agentId && !isset($agents[$agentId])) {
                $agents[$agentId] = [
                    'ctm_agent_id'    => $agentId,
                    'ctm_agent_name'  => $call['agent']['name'] ?? $call['agent_name'] ?? 'Unknown',
                    'ctm_agent_email' => $call['agent']['email'] ?? null,
                    'source'          => $source,
                ];
            }
        }

        return array_values($agents);
    }

    public function getCTMUsers(?string $filterName = null): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/users.json");

            if (!$response->successful()) {
                return null;
            }

            $data  = $response->json();
            $users = $data['users'] ?? (is_array($data) ? $data : []);

            if ($filterName !== null) {
                $needle = strtolower($filterName);
                $users  = array_values(array_filter($users, function ($user) use ($needle) {
                    $name  = strtolower($user['name']       ?? '');
                    $group = strtolower($user['user_group'] ?? $user['group'] ?? '');
                    return str_contains($name, $needle) || str_contains($group, $needle);
                }));
            }

            return array_map(fn($u) => [
                'ctm_agent_id'    => $u['id']        ?? null,
                'ctm_agent_name'  => $u['name']       ?? 'Unknown',
                'ctm_agent_email' => $u['email']      ?? null,
                'user_group'      => $u['user_group'] ?? $u['group'] ?? null,
            ], $users);
        } catch (\Exception $e) {
            Log::error('CTM getCTMUsers exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getCall(string $callId): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/calls/{$callId}");

            if ($response->successful()) {
                return $response->json();
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

    public function getCallTranscript(string $callId): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->get("https://{$this->host}/api/v1/accounts/{$this->accountId}/calls/{$callId}/transcript");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CTM getCallTranscript error', [
                'call_id' => $callId,
                'status' => $response->status(),
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
