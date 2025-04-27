<?php

namespace App\Helpers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AbstractApi
{
    protected string $service;

    protected ?string $apiKey;

    protected string $version;

    /**
     * @param  string  $service  The Abstract API service name (e.g., 'phonevalidation')
     */
    public function __construct(string $service, ?string $version = 'v1', ?string $apiKey = null)
    {
        $this->service = $service;
        $this->apiKey = $apiKey ?? config("services.abstract_api.{$service}_api_key");
        $this->version = $version;
    }

    /**
     * Build the base URL for the given service.
     */
    protected function getBaseUrl(): string
    {
        return "https://{$this->service}.abstractapi.com/{$this->version}/";
    }

    /**
     * Send a GET request to the Abstract API.
     *
     * @throws \Exception When API request fails
     */
    public function get(string $endpoint, array $params = []): ?array
    {
        $params['api_key'] = $this->apiKey;
        $url = $this->getBaseUrl().ltrim($endpoint, '/');

        try {
            $response = Http::timeout(15)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Abstract API error', [
                'service' => $this->service,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new \Exception("API request failed with status {$response->status()}: {$response->body()}");
        } catch (ConnectionException $e) {
            Log::error('Abstract API connection error', [
                'service' => $this->service,
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            throw new \Exception("API connection error: {$e->getMessage()}");
        }
    }

    /**
     * Send a POST request to the Abstract API.
     *
     * @throws \Exception When API request fails
     */
    public function post(string $endpoint, array $data = []): ?array
    {
        $data['api_key'] = $this->apiKey;
        $url = $this->getBaseUrl().ltrim($endpoint, '/');

        try {
            $response = Http::timeout(15)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Abstract API error', [
                'service' => $this->service,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new \Exception("API request failed with status {$response->status()}: {$response->body()}");
        } catch (ConnectionException $e) {
            Log::error('Abstract API connection error', [
                'service' => $this->service,
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            throw new \Exception("API connection error: {$e->getMessage()}");
        }
    }

    /**
     * Validate a phone number using Abstract API.
     *
     * @throws \Exception When validation fails
     */
    public function validatePhone(string $phoneNumber, ?string $countryCode = null): ?array
    {
        $params = [
            'phone' => $phoneNumber,
        ];

        if ($countryCode) {
            $params['country'] = $countryCode;
        }

        return $this->get('', $params);
    }
}
