<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Client\PendingRequest;

class AIClientController extends Controller
{
    private const DEFAULT_OPENAI_BASE_URL = 'https://api.openai.com/v1/chat/completions';

    public function generateMessage(Request $request, $id)
    {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $client = Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->findOrFail($id);

        $validated = $request->validate([
            'mode' => 'required|string|in:whatsapp,email,sms',
            'tone' => 'nullable|string|in:professional,friendly,urgent,short',
        ]);

        $mode = $validated['mode'];
        $tone = $validated['tone'] ?? 'professional';

        if (!$this->isAiAllowedForUser($user)) {
             return response()->json([
                'success' => false, 
                'message' => 'AI features are not enabled for your membership tier.'
            ], 403);
        }

        $apiKey = $this->getRuntimeOpenAiKey();
        if (!$apiKey) {
            return response()->json([
                'success' => false, 
                'message' => 'Coming Soon!'
            ], 503);
        }

        $prompt = $this->buildPrompt($user, $client, $mode, $tone);

        try {
            $response = $this->applyProviderHeaders(Http::withToken($apiKey))
                ->timeout(60)
                ->post($this->getRuntimeChatCompletionsUrl(), [
                    'model' => $this->getRuntimeOpenAiModel(),
                    'messages' => [
                        ['role' => 'system', 'content' => "You are a professional real estate assistant helping a realtor draft messages to clients. Keep messages concise and formatted for " . strtoupper($mode) . "."],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

            if (!$response->successful()) {
                $provider = (string) cache('ai_provider', 'openai');
                $model = $this->getRuntimeOpenAiModel();
                $err = $this->extractProviderErrorMessage($response);
                Log::error('AI Generation Error', [
                    'provider' => $provider,
                    'model' => $model,
                    'url' => $this->getRuntimeChatCompletionsUrl(),
                    'status' => $response->status(),
                    'body' => $response->json(),
                    'raw' => Str::limit((string) $response->body(), 1200),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI service error ['.$provider.' | '.$model.' | HTTP '.$response->status().']: '.$err,
                ], 502);
            }

            $data = $response->json();
            $generatedText = trim($data['choices'][0]['message']['content'] ?? '');

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $generatedText,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('AI Generation Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    private function buildPrompt(User $user, Result $client, string $mode, string $tone): string
    {
        $clientName = $client->client_name ?? 'the client';
        $source = $client->source ? "sourced from {$client->source}" : "";
        $stage = $this->deriveStage($client);
        
        $toneDesc = match($tone) {
            'friendly' => 'warm, personal, and encouraging',
            'urgent' => 'slightly urgent, professional, and direct',
            'short' => 'extremely brief and to the point',
            default => 'professional, polite, and helpful',
        };

        return "Draft a follow-up message for my real estate client.
Context:
- Client Name: {$clientName}
- How we met: {$source}
- Current Status: {$stage}
- Communication Channel: {$mode}
- Desired Tone: {$toneDesc}

Please write a high-converting message that encourages a reply. If it's for WhatsApp, you can use emojis sparingly. Do not include placeholders like '[My Name]', use my name '{$user->name}' if needed. Do not include subject lines for WhatsApp/SMS.";
    }

    private function deriveStage(Result $client): string
    {
        // Simple logic based on progress percentage or notes
        $prog = $client->today_progress['percentage'] ?? 0;
        if ($prog >= 80) return "Active negotiation / Closing phase";
        if ($prog >= 40) return "Follow-up / Meeting phase";
        return "Initial contact / Cold calling phase";
    }

    private function getRuntimeChatCompletionsUrl(): string
    {
        $provider = cache('ai_provider', 'openai');
        $custom = trim((string) cache('ai_openai_base_url', ''));
        if ($custom !== '') return $custom;

        return match ($provider) {
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            default => self::DEFAULT_OPENAI_BASE_URL,
        };
    }

    private function getRuntimeOpenAiKey(): ?string
    {
        $cached = trim((string) cache('ai_openai_api_key', ''));
        return $cached !== '' ? $cached : config('services.openai.key');
    }

    private function getRuntimeOpenAiModel(): string
    {
        $model = trim((string) cache('ai_openai_model', 'gpt-4o-mini'));
        $model = $model !== '' ? $model : 'gpt-4o-mini';

        if (cache('ai_provider', 'openai') === 'openrouter') {
            $normalized = strtolower($model);
            if ($normalized === 'gpt-4o-mini') {
                return 'openai/gpt-4o-mini';
            }
            if ($normalized === 'gpt-4o') {
                return 'openai/gpt-4o';
            }
            if ($normalized === 'gpt-4.1-mini') {
                return 'openai/gpt-4.1-mini';
            }
            if ($normalized === 'gpt-4.1') {
                return 'openai/gpt-4.1';
            }
        }

        return $model;
    }

    private function applyProviderHeaders(PendingRequest $request): PendingRequest
    {
        if (cache('ai_provider', 'openai') !== 'openrouter') {
            return $request;
        }

        return $request->withHeaders([
            'HTTP-Referer' => config('app.url', 'https://realtorone.app'),
            'X-Title' => config('app.name', 'RealtorOne'),
        ]);
    }

    private function extractProviderErrorMessage($response): string
    {
        $body = $response->json();
        if (is_array($body)) {
            $candidates = [
                $body['error']['message'] ?? null,
                $body['message'] ?? null,
                $body['error'] ?? null,
            ];
            foreach ($candidates as $candidate) {
                if (is_string($candidate) && trim($candidate) !== '') {
                    return trim($candidate);
                }
            }
            $encoded = json_encode($body);
            if (is_string($encoded) && $encoded !== '') {
                return $encoded;
            }
        }

        $raw = trim((string) $response->body());
        if ($raw !== '') {
            return Str::limit(preg_replace('/\s+/', ' ', $raw) ?? $raw, 300);
        }

        return 'Unknown provider error';
    }

    private function isAiAllowedForUser(User $user): bool
    {
        $tier = (string) ($user->membership_tier ?: 'Consultant');
        if ($tier === 'Titan') return (bool) cache('ai_allow_titan', true);
        if ($tier === 'Rainmaker') return (bool) cache('ai_allow_rainmaker', true);
        return (bool) cache('ai_allow_consultant', true);
    }
}
