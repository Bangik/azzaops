<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private ?array $credentials = null;

    public function __construct()
    {
        $path = storage_path(env('FIREBASE_CREDENTIALS', 'app/firebase-service-account.json'));
        if (file_exists($path)) {
            $this->credentials = json_decode(file_get_contents($path), true);
        }
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->credentials) {
            Log::warning('FCM: Firebase credentials JSON file not found or not configured.');
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('FCM: Failed to retrieve Google OAuth2 access token.');
            return false;
        }

        $projectId = $this->credentials['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Convert all data array values to strings as required by Firebase HTTP v1 API
        $stringData = [];
        foreach ($data as $key => $value) {
            $stringData[(string) $key] = is_array($value) ? json_encode($value) : (string) $value;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->withToken($accessToken)
            ->acceptJson()
            ->post($url, $payload);

        if ($response->successful()) {
            return true;
        }

        Log::error('FCM Send Error: ' . $response->body());
        return false;
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (!$this->credentials) {
            Log::warning('FCM: Firebase credentials not configured.');
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $projectId = $this->credentials['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $stringData = [];
        foreach ($data as $key => $value) {
            $stringData[(string) $key] = is_array($value) ? json_encode($value) : (string) $value;
        }

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
            ]
        ];

        $response = Http::timeout(3)
            ->connectTimeout(2)
            ->withToken($accessToken)
            ->acceptJson()
            ->post($url, $payload);

        return $response->successful();
    }

    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 3000, function () {
            if (!$this->credentials) {
                return null;
            }

            $clientEmail = $this->credentials['client_email'];
            $privateKey = $this->credentials['private_key'];
            $tokenUri = $this->credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claimSet = json_encode([
                'iss' => $clientEmail,
                'sub' => $clientEmail,
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlClaimSet = $this->base64UrlEncode($claimSet);

            $signatureInput = $base64UrlHeader . '.' . $base64UrlClaimSet;

            $signature = '';
            if (!openssl_sign($signatureInput, $signature, $privateKey, 'SHA256')) {
                Log::error('FCM: OpenSSL sign failed.');
                return null;
            }

            $base64UrlSignature = $this->base64UrlEncode($signature);
            $assertion = $signatureInput . '.' . $base64UrlSignature;

            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->asForm()
                ->post($tokenUri, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $assertion
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('FCM Token Retrieve Error: ' . $response->body());
            return null;
        });
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
