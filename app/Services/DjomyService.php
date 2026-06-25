<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DjomyService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $countryCode;

    public function __construct()
    {
        $this->baseUrl      = rtrim(config('djomy.base_url'), '/');
        $this->clientId     = config('djomy.client_id');
        $this->clientSecret = config('djomy.client_secret');
        $this->countryCode  = config('djomy.country_code', 'GN');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $null = fopen(PHP_OS_FAMILY === 'Windows' ? 'nul' : '/dev/null', 'w');
        return Http::withOptions([
            'debug' => false,
            'curl'  => [CURLOPT_VERBOSE => 0, CURLOPT_STDERR => $null],
        ]);
    }

    private function buildApiKey(): string
    {
        $sig = hash_hmac('sha256', $this->clientId, $this->clientSecret);
        return "{$this->clientId}:{$sig}";
    }

    public function getAccessToken(): string
    {
        return Cache::remember('djomy_access_token', 3300, function () {
            $response = $this->http()->withHeaders([
                'X-API-KEY'    => $this->buildApiKey(),
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])->withBody('{}', 'application/json')
              ->post("{$this->baseUrl}/v1/auth");

            if (!$response->successful()) {
                throw new \RuntimeException('Djomy auth failed: ' . $response->body());
            }

            $data = $response->json();
            Log::debug('Djomy auth response', ['body' => $data]);

            return $data['data']['accessToken']
                ?? throw new \RuntimeException('No token in Djomy auth response. Body: ' . $response->body());
        });
    }

    private function headers(): array
    {
        return [
            'X-API-KEY'     => $this->buildApiKey(),
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Create a gateway payment (redirect flow).
     * Returns the full Djomy response array.
     */
    public function createGatewayPayment(array $payload): array
    {
        $payload['countryCode'] ??= $this->countryCode;

        $response = $this->http()->withHeaders($this->headers())
            ->post("{$this->baseUrl}/v1/payments/gateway", $payload);

        // Token expired or rejected — clear cache and retry once with a fresh token.
        if (in_array($response->status(), [401, 403])) {
            Cache::forget('djomy_access_token');
            Log::warning('Djomy: token rejected, retrying with fresh token', ['status' => $response->status()]);
            $response = $this->http()->withHeaders($this->headers())
                ->post("{$this->baseUrl}/v1/payments/gateway", $payload);
        }

        Log::debug('Djomy gateway response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Djomy gateway error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get the status of a payment by Djomy transaction ID.
     */
    public function getPaymentStatus(string $transactionId): array
    {
        $response = $this->http()->withHeaders($this->headers())
            ->get("{$this->baseUrl}/v1/payments/{$transactionId}/status");

        if (!$response->successful()) {
            throw new \RuntimeException('Djomy status error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Verify the HMAC-SHA256 signature of a webhook payload.
     * Header format: "v1:<hex-digest>"
     */
    public function verifyWebhook(string $rawPayload, string $signatureHeader): bool
    {
        if (!str_starts_with($signatureHeader, 'v1:')) {
            return false;
        }

        $expected = 'v1:' . hash_hmac('sha256', $rawPayload, $this->clientSecret);
        return hash_equals($expected, $signatureHeader);
    }
}
