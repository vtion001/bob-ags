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

    public function getCall(string $callId): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
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
