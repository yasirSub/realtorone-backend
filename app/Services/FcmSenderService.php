<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmSenderService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    public function isConfigured(): bool
    {
        $projectId = config('firebase.project_id');
        if (! is_string($projectId) || $projectId === '') {
            return false;
        }

        $path = config('firebase.credentials');
        if (is_string($path) && $path !== '' && is_readable($path)) {
            return true;
        }

        $json = config('firebase.credentials_json');

        return is_string($json) && $json !== '';
    }

    /**
     * @return array{access_token: string}|null
     */
    private function fetchAccessToken(): ?array
    {
        $path = config('firebase.credentials');
        $jsonString = config('firebase.credentials_json');

        $source = 'none';
        if (is_string($path) && $path !== '' && is_readable($path)) {
            $source = 'path';
            $content = (string) file_get_contents($path);
            if (str_starts_with($content, "\xEF\xBB\xBF")) {
                $content = substr($content, 3);
            }
            $keyFile = json_decode($content, true);
        } elseif (is_string($jsonString) && $jsonString !== '') {
            $content = $jsonString;
            if (str_starts_with($content, "\xEF\xBB\xBF")) {
                $content = substr($content, 3);
            }
            $keyFile = json_decode($content, true);
        } elseif (config('firebase.private_key')) {
            $source = 'env';
            $keyFile = [
                'type' => 'service_account',
                'project_id' => config('firebase.project_id'),
                'private_key' => config('firebase.private_key'),
                'client_email' => config('firebase.client_email'),
            ];
        } else {
            return null;
        }

        if (! is_array($keyFile)) {
            return null;
        }

        // Debug only: helps confirm the credentials are decoded correctly.
        // We intentionally do NOT log the private key itself.
        $privateKey = (string) ($keyFile['private_key'] ?? '');
        if ($privateKey !== '') {
            // Normalize common newline encodings to a proper PEM format.
            // This prevents malformed JWT signatures when keys are loaded from env/files
            // with different newline escaping.
            $privateKey = trim($privateKey);
            $privateKey = str_replace(["\r\n", "\r"], "\n", $privateKey);
            $privateKey = str_replace('\\n', "\n", $privateKey);
            $keyFile['private_key'] = $privateKey;
        }
        Log::info('FCM OAuth credentials loaded', [
            'source' => $source,
            'project_id' => (string) config('firebase.project_id'),
            'private_key_prefix' => substr(trim($privateKey), 0, 25),
            'private_key_len' => strlen($privateKey),
            'private_key_has_newline' => str_contains($privateKey, "\n"),
        ]);

        $credentials = new ServiceAccountCredentials(self::SCOPE, $keyFile);
        $handler = HttpHandlerFactory::build();

        try {
            return $credentials->fetchAuthToken($handler);
        } catch (\Exception $e) {
            Log::error('FCM OAuth fetchAuthToken exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return null;
        }
    }

    /**
     * Send one FCM message. Returns true if HTTP success.
     * Removes invalid token from DB when FCM reports unregistered.
     */
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): bool {
        if (! $this->isConfigured()) {
            Log::warning('FCM not configured: set FIREBASE_PROJECT_ID and credentials.');

            return false;
        }

        $auth = $this->fetchAccessToken();
        if (! isset($auth['access_token'])) {
            Log::error('FCM OAuth token fetch failed.');

            return false;
        }

        $projectId = config('firebase.project_id');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map(static fn ($v) => (string) $v, $data),
            ],
        ];

        $response = Http::withToken($auth['access_token'])
            ->acceptJson()
            ->post($url, $payload);

        if ($response->successful()) {
            return true;
        }

        $body = $response->json();
        $errorCode = $body['error']['details'][0]['errorCode'] ?? null;
        if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)
            || str_contains($response->body(), 'NOT_FOUND')
            || str_contains($response->body(), 'UNREGISTERED')) {
            \App\Models\UserPushToken::where('token', $token)->delete();
        }

        Log::warning('FCM send failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }
}
