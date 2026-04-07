<?php

namespace App\Http\Controllers;

use App\Models\AiCourseKnowledgeBase;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const OPENAI_URL = 'https://api.openai.com/v1/chat/completions';
    private const MAX_HISTORY = 20;
    private const DEFAULT_OPENAI_BASE_URL = 'https://api.openai.com/v1/chat/completions';

    private function getRuntimeChatCompletionsUrl(): string
    {
        $provider = $this->getRuntimeProvider();
        $custom = trim((string) cache('ai_openai_base_url', ''));
        if ($custom !== '') {
            return $custom;
        }
        return match ($provider) {
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'together' => 'https://api.together.xyz/v1/chat/completions',
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'mistral' => 'https://api.mistral.ai/v1/chat/completions',
            'fireworks' => 'https://api.fireworks.ai/inference/v1/chat/completions',
            'xai' => 'https://api.x.ai/v1/chat/completions',
            default => self::DEFAULT_OPENAI_BASE_URL,
        };
    }

    /**
     * System prompt with knowledge about the app.
     */
    private function getSystemPrompt(?User $user = null, ?string $customKnowledgeBase = null): string
    {
        $base = <<<'PROMPT'
You are Reven, a friendly AI assistant for the RealtorOne real estate training app. You help users with:

- **Courses & training**: Enrolled courses, course progress, the Cold Calling Master program, Million Dirham Beliefs Program, and learning content
- **Daily tasks**: Tasks on the Dashboard, activity logging, momentum scores
- **Leaderboard & badges**: How to improve rank, earned badges, streaks
- **Account**: Profile updates, avatar, settings
- **Real estate tips**: Cold calling, prospecting, follow-ups, client meetings

Be concise, helpful, and encouraging. If asked about something outside this scope, politely say you focus on real estate training and the app. Use markdown for lists when helpful.
PROMPT;
        $name = trim((string) ($user?->name ?? ''));
        $tier = trim((string) ($user?->membership_tier ?? 'Consultant'));
        $email = trim((string) ($user?->email ?? ''));
        $city = trim((string) ($user?->city ?? ''));
        $context = "User context:\n".
            '- Name: '.($name !== '' ? $name : 'User')."\n".
            '- Tier: '.($tier !== '' ? $tier : 'Consultant')."\n".
            '- Email: '.($email !== '' ? $email : 'n/a')."\n".
            '- City: '.($city !== '' ? $city : 'n/a')."\n".
            "When appropriate, greet and refer to the user by name naturally.";

        $behavior = trim((string) cache('ai_behavior_instructions', ''));
        if ($behavior !== '') {
            $context .= "\n\nBehavior instructions (admin configured):\n".$behavior;
        }

        if ($this->getUseCustomKb() && $customKnowledgeBase && trim($customKnowledgeBase) !== '') {
            $context .= "\n\nCustom knowledge base (admin configured):\n".trim($customKnowledgeBase);
        }

        // Structured KB blocks (multiple datasets)
        if ($this->getUseCustomKb()) {
            $kbBlocksRaw = (string) cache('ai_kb_blocks', '[]');
            $kbBlocks = json_decode($kbBlocksRaw, true);
            if (is_array($kbBlocks)) {
                $enabled = array_values(array_filter($kbBlocks, fn ($b) => is_array($b) && (($b['enabled'] ?? true) === true)));
                if (! empty($enabled)) {
                    $joined = [];
                    foreach ($enabled as $b) {
                        $t = trim((string) ($b['title'] ?? ''));
                        $c = trim((string) ($b['content'] ?? ''));
                        if ($c === '') continue;
                        $joined[] = ($t !== '' ? "## ".$t."\n".$c : $c);
                        if (count($joined) >= 30) break; // keep prompt bounded
                    }
                    if (! empty($joined)) {
                        $context .= "\n\nKnowledge base datasets (admin configured):\n".implode("\n\n---\n\n", $joined);
                    }
                }
            }
        }

        return $base."\n\n".$context;
    }

    /**
     * Basic keyword replies when OPENAI_API_KEY is not set.
     * Returns ['reply' => string, 'courses' => array|null, 'commands' => array|null].
     */
    private function getBasicReply(string $message, ?User $user): array
    {
        $text = strtolower(trim($message));
        $displayName = trim((string) ($user?->name ?? ''));
        $displayName = $displayName !== '' ? $displayName : 'there';
        if (preg_match('/\b(hi|hello|hey|hola|howdy)\b/', $text)) {
            return ['reply' => "Hi {$displayName}! I\'m Reven, your assistant for the RealtorOne app. Type **help** to see what I can do.", 'courses' => null, 'commands' => null];
        }
        if (preg_match('/\b(what can you do|help|commands|capabilities)\b/', $text)) {
            $commands = $this->getHelpCommands();
            return [
                'reply' => 'Here\'s what I can do. Tap any command below or type it in the chat:',
                'courses' => null,
                'commands' => $commands,
            ];
        }
        if (preg_match('/\b(course|courses|training|learning|enrolled|what are the courses|list courses|show courses)\b/', $text)) {
            $courses = $this->fetchAiCourseListForUser($user);
            $reply = $courses->isEmpty()
                ? 'No courses available right now. Check the Courses tab for updates!'
                : 'Here are the courses available to you:';
            return [
                'reply' => $reply,
                'courses' => $courses->toArray(),
            ];
        }
        if (preg_match('/\b(task|tasks|today|daily|dashboard)\b/', $text)) {
            return ['reply' => 'Daily tasks are on the Dashboard. Log activities there to build your momentum score.', 'courses' => null, 'commands' => null];
        }
        if (preg_match('/\b(badge|badges|leaderboard|rank|streak)\b/', $text)) {
            return ['reply' => 'Head to the Badges tab for milestones and Leaderboard for your rank. Complete tasks and courses to climb!', 'courses' => null, 'commands' => null];
        }
        if (preg_match('/\b(profile|account|settings|update)\b/', $text)) {
            return ['reply' => 'Tap your avatar to open profile settings and update name, photo, and preferences.', 'courses' => null, 'commands' => null];
        }
        if (preg_match('/\b(cold call|cold calling|prospect|follow.?up)\b/i', $text)) {
            return ['reply' => 'Cold calling tips: great opener + active listening + clear objective. Check the Cold Calling Master course in your Courses tab!', 'courses' => null, 'commands' => null];
        }
        return ['reply' => "I\'m here to help, {$displayName}! Type **help** to see what I can do, or try: courses, tasks, badges, profile.", 'courses' => null, 'commands' => null];
    }

    private function getRuntimeOpenAiKey(): ?string
    {
        $cached = trim((string) cache('ai_openai_api_key', ''));
        if ($cached !== '') {
            return $cached;
        }
        $configKey = trim((string) config('services.openai.key'));
        return $configKey !== '' ? $configKey : null;
    }

    private function getRuntimeProvider(): string
    {
        $provider = trim((string) cache('ai_provider', 'openai'));
        return $provider !== '' ? $provider : 'openai';
    }

    private function getRuntimeOpenAiModel(): string
    {
        $model = trim((string) cache('ai_openai_model', 'gpt-4o-mini'));
        return $model !== '' ? $model : 'gpt-4o-mini';
    }

    private function getUseCustomKb(): bool
    {
        return (bool) cache('ai_use_custom_kb', true);
    }

    private function getUseCourseKb(): bool
    {
        return (bool) cache('ai_use_course_kb', true);
    }

    private function isAiAllowedForUser(?User $user): bool
    {
        if (! $user) return true;
        $tier = (string) ($user->membership_tier ?: 'Consultant');
        if ($tier === 'Titan') return (bool) cache('ai_allow_titan', true);
        if ($tier === 'Rainmaker') return (bool) cache('ai_allow_rainmaker', true);
        return (bool) cache('ai_allow_consultant', true);
    }

    private function appendUpgradeSuggestionIfBlocked(string $reply, ?User $user): string
    {
        if (! $user) return $reply;
        if ($this->isAiAllowedForUser($user)) return $reply;

        $tier = (string) ($user->membership_tier ?: 'Consultant');
        // Only upsell when access is blocked by tier (not when key/provider missing).
        return rtrim($reply)."\n\n**AI replies are not enabled for your tier ({$tier}).** Upgrade to unlock full AI coaching in the app (Subscriptions).";
    }

    private function getRuntimeKnowledgeBase(): ?string
    {
        $cached = trim((string) cache('ai_custom_knowledge_base', ''));
        return $cached !== '' ? $cached : null;
    }

    private function getHelpCommands(): array
    {
        return [
            ['keyword' => 'courses', 'label' => '📚 Courses', 'description' => 'See your available courses'],
            ['keyword' => 'tasks', 'label' => '📋 Tasks', 'description' => 'Daily tasks & dashboard'],
            ['keyword' => 'badges', 'label' => '🏆 Badges', 'description' => 'Leaderboard & milestones'],
            ['keyword' => 'profile', 'label' => '⚙️ Profile', 'description' => 'Account settings'],
            ['keyword' => 'cold calling tips', 'label' => '📞 Cold calling', 'description' => 'Tips & training'],
        ];
    }

    private function fetchCourseList()
    {
        return Course::orderBy('module_number')
            ->orderBy('sequence')
            ->get(['id', 'title', 'description', 'module_number']);
    }

    private function fetchAiCourseListForUser(?User $user)
    {
        $tier = $user?->membership_tier ?: 'Consultant';

        $hasAnyForTier = AiCourseKnowledgeBase::query()
            ->where('tier', $tier)
            ->count();

        // Backward compatibility: if the KB table isn't configured yet for this tier, fall back to all courses.
        if ($hasAnyForTier === 0) {
            return $this->fetchCourseList();
        }

        return Course::query()
            ->whereIn('id', AiCourseKnowledgeBase::query()
                ->select('course_id')
                ->where('tier', $tier)
                ->where('is_enabled', true)
            )
            ->orderBy('module_number')
            ->orderBy('sequence')
            ->get(['id', 'title', 'description', 'module_number']);
    }

    private function parseStoredContent(string $content): string
    {
        if ($content === '' || ($content[0] ?? '') !== '{') {
            return $content;
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) && isset($decoded['text']) ? (string) $decoded['text'] : $content;
    }

    public function send(Request $request): JsonResponse
    {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
        ]);

        $message = trim($validated['message']);
        $sessionId = $validated['session_id'] ?? null;

        // Get or create session
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)->where('user_id', $user->id)->first();
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Session not found'], 404);
            }
        } else {
            $session = ChatSession::create([
                'user_id' => $user->id,
                'title' => Str::limit($message, 50),
            ]);
        }

        // Save user message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $message,
        ]);

        // Build messages for OpenAI (system + recent history + new user message)
        $history = $session->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get();

        $systemContent = $this->getSystemPrompt($user, $this->getRuntimeKnowledgeBase());
        $coursesForResponse = null;
        if ($this->getUseCourseKb() && preg_match('/\b(course|courses|training|learning|enrolled|what are the courses|list courses|show courses)\b/i', $message)) {
            $coursesForResponse = $this->fetchAiCourseListForUser($user)->toArray();
            if (!empty($coursesForResponse)) {
                $list = collect($coursesForResponse)->map(fn ($c) => '- ' . ($c['title'] ?? '') . (isset($c['description']) ? ': ' . Str::limit($c['description'], 80) : ''))->implode("\n");
                $systemContent .= "\n\nCurrent courses in the system:\n" . $list;
            }
        }

        $openaiMessages = [
            ['role' => 'system', 'content' => $systemContent],
        ];

        // Lightweight structured “tool” data for common CRM questions
        $structuredContext = [];

        // 1) Active clients list (hot_lead results with a current/open status)
        if (preg_match('/\b(active client|active clients|current client|current deal|hot lead|pipeline)\b/i', $message)) {
            $activeClients = \App\Models\Result::query()
                ->where('user_id', $user->id)
                ->where('type', 'hot_lead')
                ->whereNotNull('client_name')
                ->orderByDesc('date')
                ->limit(50)
                ->get(['client_name', 'source', 'status', 'date', 'value']);

            if ($activeClients->isNotEmpty()) {
                $structuredContext['active_clients'] = $activeClients->map(function ($r) {
                    return [
                        'client_name' => $r->client_name,
                        'source' => $r->source,
                        'status' => $r->status,
                        'date' => optional($r->date)->toDateString(),
                        'value' => $r->value,
                    ];
                })->values()->all();
            }
        }

        // 2) Deals closed (this month) summary
        if (preg_match('/\b(deal[s]? closed|closed deal|commission|revenue)\b/i', $message)) {
            $monthStart = now()->startOfMonth();
            $deals = \App\Models\Result::query()
                ->where('user_id', $user->id)
                ->where('type', 'deal_closed')
                ->where('date', '>=', $monthStart)
                ->orderByDesc('date')
                ->limit(50)
                ->get(['client_name', 'value', 'date', 'source']);

            if ($deals->isNotEmpty()) {
                $structuredContext['deals_closed_this_month'] = [
                    'count' => $deals->count(),
                    'total_value' => (float) $deals->sum('value'),
                    'items' => $deals->map(function ($r) {
                        return [
                            'client_name' => $r->client_name,
                            'value' => $r->value,
                            'source' => $r->source,
                            'date' => optional($r->date)->toDateString(),
                        ];
                    })->values()->all(),
                ];
            }
        }

        if (! empty($structuredContext)) {
            $openaiMessages[] = [
                'role' => 'system',
                'content' => "Structured CRM data for this user (use this as ground truth when answering):\n".json_encode($structuredContext),
            ];
        }

        $recent = $history->take(-self::MAX_HISTORY);
        foreach ($recent as $m) {
            $parsed = $this->parseStoredContent($m->content);
            $openaiMessages[] = ['role' => $m->role, 'content' => $parsed];
        }

        $apiKey = $this->getRuntimeOpenAiKey();
        if (! $this->isAiAllowedForUser($user) || $this->getRuntimeProvider() === 'disabled' || !$apiKey) {
            $basic = $this->getBasicReply($message, $user);
            $reply = $basic['reply'];
            if (! $this->isAiAllowedForUser($user)) {
                $reply = $this->appendUpgradeSuggestionIfBlocked((string) $reply, $user);
            }
            $courses = $basic['courses'] ?? null;
            $commands = $basic['commands'] ?? null;
            $contentToStore = $reply;
            if (($courses !== null && !empty($courses)) || ($commands !== null && !empty($commands))) {
                $contentToStore = json_encode(array_filter(['text' => $reply, 'courses' => $courses, 'commands' => $commands]));
            }
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $contentToStore,
            ]);
            $payload = [
                'success' => true,
                'reply' => $reply,
                'session_id' => $session->id,
            ];
            if ($courses !== null) {
                $payload['courses'] = $courses;
            }
            if ($commands !== null) {
                $payload['commands'] = $commands;
            }
            return response()->json($payload);
        }

        try {
            $modelToUse = $this->getRuntimeOpenAiModel();
            $url = $this->getRuntimeChatCompletionsUrl();
            /** @var HttpResponse $response */
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post($url, [
                    'model' => $modelToUse,
                    'messages' => $openaiMessages,
                    'max_tokens' => 1024,
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                $err = $body['error']['message'] ?? $response->body();
                Log::error('OpenAI error', ['status' => $response->status(), 'body' => $body]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI service error: ' . $err,
                ], 502);
            }

            $data = $response->json();
            $reply = trim($data['choices'][0]['message']['content'] ?? '');
            $usage = is_array($data['usage'] ?? null) ? $data['usage'] : null;
            $totalTokens = is_array($usage) ? ($usage['total_tokens'] ?? null) : null;
            $promptTokens = is_array($usage) ? ($usage['prompt_tokens'] ?? null) : null;
            $completionTokens = is_array($usage) ? ($usage['completion_tokens'] ?? null) : null;
            $model = is_string($data['model'] ?? null) ? $data['model'] : $modelToUse;

            $contentToStore = $reply;
            if ($coursesForResponse !== null && !empty($coursesForResponse)) {
                $contentToStore = json_encode(['text' => $reply, 'courses' => $coursesForResponse]);
            }
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $contentToStore,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'model' => $model,
            ]);

            $payload = [
                'success' => true,
                'reply' => $reply,
                'session_id' => $session->id,
            ];
            if ($coursesForResponse !== null) {
                $payload['courses'] = $coursesForResponse;
            }
            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Chat error', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    private function getAuthAdmin(Request $request): ?User
    {
        $admin = getAuthUser($request);
        if (! $admin || $admin->email !== 'admin@realtorone.com') {
            return null;
        }
        return $admin;
    }

    public function adminSendForUser(Request $request, int $userId): JsonResponse
    {
        $admin = $this->getAuthAdmin($request);
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = User::query()->find($userId);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
        ]);

        $message = trim((string) $validated['message']);
        $sessionId = $validated['session_id'] ?? null;

        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)->where('user_id', $user->id)->first();
            if (! $session) {
                return response()->json(['success' => false, 'message' => 'Session not found'], 404);
            }
        } else {
            $session = ChatSession::create([
                'user_id' => $user->id,
                'title' => Str::limit($message, 50),
            ]);
        }

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $message,
        ]);

        $history = $session->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get();

        $systemContent = $this->getSystemPrompt($user, $this->getRuntimeKnowledgeBase());
        $openaiMessages = [
            ['role' => 'system', 'content' => $systemContent],
        ];

        $recent = $history->take(-self::MAX_HISTORY);
        foreach ($recent as $m) {
            $parsed = $this->parseStoredContent($m->content);
            $openaiMessages[] = ['role' => $m->role, 'content' => $parsed];
        }

        $apiKey = $this->getRuntimeOpenAiKey();
        if (! $this->isAiAllowedForUser($user) || $this->getRuntimeProvider() === 'disabled' || ! $apiKey) {
            $basic = $this->getBasicReply($message, $user);
            $reply = (string) ($basic['reply'] ?? '');
            if (! $this->isAiAllowedForUser($user)) {
                $reply = $this->appendUpgradeSuggestionIfBlocked($reply, $user);
            }
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $reply,
            ]);
            return response()->json(['success' => true, 'reply' => $reply, 'session_id' => $session->id]);
        }

        try {
            $modelToUse = $this->getRuntimeOpenAiModel();
            $url = $this->getRuntimeChatCompletionsUrl();
            /** @var HttpResponse $response */
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post($url, [
                    'model' => $modelToUse,
                    'messages' => $openaiMessages,
                    'max_tokens' => 1024,
                ]);

            if (! $response->successful()) {
                $body = $response->json();
                $err = $body['error']['message'] ?? $response->body();
                Log::error('OpenAI error (adminSendForUser)', ['status' => $response->status(), 'body' => $body]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI service error: '.$err,
                ], 502);
            }

            $data = $response->json();
            $reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            $usage = is_array($data['usage'] ?? null) ? $data['usage'] : null;
            $totalTokens = is_array($usage) ? ($usage['total_tokens'] ?? null) : null;
            $promptTokens = is_array($usage) ? ($usage['prompt_tokens'] ?? null) : null;
            $completionTokens = is_array($usage) ? ($usage['completion_tokens'] ?? null) : null;
            $model = is_string($data['model'] ?? null) ? $data['model'] : $modelToUse;

            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $reply,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'model' => $model,
            ]);

            return response()->json(['success' => true, 'reply' => $reply, 'session_id' => $session->id]);
        } catch (\Throwable $e) {
            Log::error('Chat error (adminSendForUser)', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function history(Request $request, ?int $sessionId = null): JsonResponse
    {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)->where('user_id', $user->id)->first();
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Session not found'], 404);
            }
            $messages = $session->messages()->orderBy('id')->get(['id', 'role', 'content', 'created_at']);
            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'messages' => $messages,
            ]);
        }

        // List sessions
        $sessions = ChatSession::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'title', 'created_at', 'updated_at']);
        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function deleteSession(Request $request, int $sessionId): JsonResponse
    {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $session = ChatSession::where('id', $sessionId)->where('user_id', $user->id)->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        $session->delete();
        return response()->json(['success' => true, 'message' => 'Chat deleted']);
    }
}
