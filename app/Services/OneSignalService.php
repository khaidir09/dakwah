<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected string $appId;
    protected string $restApiKey;
    protected string $apiUrl = 'https://onesignal.com/api/v1/notifications';

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->restApiKey = config('services.onesignal.rest_api_key');
    }

    /**
     * Send a notification to specific users via External ID (mapped from Auth ID).
     *
     * @param string $title
     * @param string $message
     * @param array $userIds Array of user IDs that match the auth()->id() used in frontend login.
     * @param string|null $url Optional URL to open.
     * @param array $data Additional data.
     * @return array|bool Response from OneSignal or false on failure.
     */
    public function sendNotification(string $title, string $message, array $userIds = [], ?string $url = null, array $data = [])
    {
        if (empty($this->appId) || empty($this->restApiKey)) {
            Log::warning('OneSignal credentials not set.');
            return false;
        }

        $payload = [
            'app_id' => $this->appId,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data,
        ];

        if (!empty($userIds)) {
            // Using include_aliases to target users by the external_id set via OneSignal.login()
            $payload['include_aliases'] = [
                'external_id' => array_map('strval', $userIds)
            ];
            $payload['target_channel'] = 'push';
        } else {
             // Fallback or explicit "Send to All" logic could go here.
             // For safety, we return false if no target is specified.
             return false;
        }

        if ($url) {
            $payload['url'] = $url;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->restApiKey,
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('OneSignal Error: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('OneSignal Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send a notification to All Subscribed Users.
     *
     * @param string $title
     * @param string $message
     * @param string|null $url
     * @param array $data
     * @return array|bool
     */
    public function sendToAll(string $title, string $message, ?string $url = null, array $data = [])
    {
        if (empty($this->appId) || empty($this->restApiKey)) {
            Log::warning('OneSignal credentials not set.');
            return false;
        }

        $payload = [
            'app_id' => $this->appId,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'included_segments' => ['Total Subscriptions'],
            'data' => $data,
        ];

        if ($url) {
            $payload['url'] = $url;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->restApiKey,
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('OneSignal Error: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('OneSignal Exception: ' . $e->getMessage());
            return false;
        }
    }
}
