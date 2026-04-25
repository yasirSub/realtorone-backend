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

- Courses and training: Enrolled courses, course progress, the Cold Calling Master program, Million Dirham Beliefs Program, and learning content
- Daily tasks: Tasks on the Dashboard, activity logging, momentum scores
- Leaderboard and badges: How to improve rank, earned badges, streaks
- Webinars and events: Upcoming live sessions, special guest masterclasses, and how to join via Zoom links
- Account: Profile updates, avatar, settings
- Real estate tips: Cold calling, prospecting, follow-ups, client meetings
- Client and CRM insights: Active clients, hot leads, pipeline status, and deal activity available in the app for the authenticated user

Be concise, helpful, and encouraging. Keep replies short. Avoid bold markdown unless absolutely necessary.
Prefer action buttons/commands for navigable app sections instead of long explanations.
When structured CRM/app data is provided in context, treat it as trusted in-app data and answer from it.
Do not claim you cannot access CRM/client data if it is provided in context for this user.
If data is not present in context, say what is missing and ask a short clarifying follow-up.
If the user asks about Dashboard, Courses, Badges, Profile, Deal Room, or similar sections, return a short reply plus the matching navigation command.
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
     * Returns ['reply' => string, 'courses' => array|null, 'commands' => array|null, 'clients' => array|null].
     */
    private function getBasicReply(string $message, ?User $user): array
    {
        $text = strtolower(trim($message));
        $displayName = trim((string) ($user?->name ?? ''));
        $displayName = $displayName !== '' ? $displayName : 'there';
        if (preg_match('/\b(hi|hello|hey|hola|howdy)\b/', $text)) {
            return ['reply' => "Hi {$displayName}. Tap a button below or type help.", 'courses' => null, 'commands' => null];
        }
        if (preg_match('/\b(what can you do|help|commands|capabilities)\b/', $text)) {
            $commands = $this->getHelpCommands();
            return [
                'reply' => 'Tap a button below.',
                'courses' => null,
                'commands' => $commands,
            ];
        }
        if (preg_match('/\b(course|courses|training|learning|enrolled|what are the courses|list courses|show courses)\b/', $text)) {
            $courses = $this->fetchAiCourseListForUser($user);
            $reply = $courses->isEmpty()
                ? 'No courses available right now.'
                : 'Open Courses below.';
            return [
                'reply' => $reply,
                'courses' => $courses->toArray(),
                'commands' => [$this->getNavigationCommand('courses', 'Open Courses', 'courses', 'Go to your courses')],
            ];
        }
        if (preg_match('/\b(task|tasks|today|daily|dashboard)\b/', $text)) {
            return [
                'reply' => 'Open Dashboard below.',
                'courses' => null,
                'commands' => [$this->getNavigationCommand('dashboard', 'Open Dashboard', 'dashboard', 'Go to today\'s plan')],
            ];
        }
        if (preg_match('/\b(badge|badges|leaderboard|rank|streak)\b/', $text)) {
            return [
                'reply' => 'Open Badges below.',
                'courses' => null,
                'commands' => [$this->getNavigationCommand('badges', 'Open Badges', 'badges', 'Go to badges and leaderboard')],
            ];
        }
        if (preg_match('/\b(profile|account|settings|update)\b/', $text)) {
            return [
                'reply' => 'Open Profile below.',
                'courses' => null,
                'commands' => [$this->getNavigationCommand('profile', 'Open Profile', 'profile', 'Go to your profile')],
            ];
        }
        if (preg_match('/\b(cold call|cold calling|prospect|follow.?up)\b/i', $text)) {
            return [
                'reply' => 'Open Courses below.',
                'courses' => null,
                'commands' => [$this->getNavigationCommand('courses', 'Open Courses', 'courses', 'Go to training')],
            ];
        }
        if (preg_match('/\b(webinar|webinars|live session|zoom link|when is the next|meeting)\b/i', $text)) {
            $webinars = \App\Models\Webinar::where('is_active', true)->where('scheduled_at', '>', now())->limit(5)->get();
            if ($webinars->isEmpty()) {
                return ['reply' => 'No webinars are scheduled right now.', 'courses' => null, 'commands' => null];
            }
            $reply = 'Open Webinars below.';
            return ['reply' => $reply, 'courses' => null, 'commands' => null];
        }
        return ['reply' => "I\'m here to help, {$displayName}. Tap a button below.", 'courses' => null, 'commands' => $this->getHelpCommands()];
    }

    private function isClientListRequest(string $message): bool
    {
        return (bool) preg_match('/\b(show|list|all|my|view|display|open)\b.*\b(client|clients|client name|client names|lead|leads|hot lead|hot leads|crm|deal room)\b/i', $message)
            || (bool) preg_match('/\b(client name|client names|client list|clients list|my clients|active clients|active client|lead list|lead lists)\b/i', $message);
    }

    private function getOpenDealRoomCommand(): array
    {
        return [
            'keyword' => 'open deal room',
            'label' => 'Open Deal Room',
            'description' => 'Jump to your client workspace',
            'target' => 'client-list',
        ];
    }

    private function getNavigationCommand(string $keyword, string $label, string $target, string $description): array
    {
        return [
            'keyword' => $keyword,
            'label' => $label,
            'description' => $description,
            'target' => $target,
        ];
    }

    private function buildNavigationResponse(string $message): ?array
    {
        $text = strtolower(trim($message));

        if (preg_match('/\b(dashboard|today\'?s plan|today plan|plan for today|daily plan|my day)\b/i', $text)) {
            return [
                'reply' => 'Open Dashboard below.',
                'commands' => [$this->getNavigationCommand('dashboard', 'Open Dashboard', 'dashboard', 'Go to today\'s plan')],
            ];
        }

        if (preg_match('/\b(task|tasks|todo|to-do|today|daily)\b/i', $text)) {
            return [
                'reply' => 'Open Dashboard below.',
                'commands' => [$this->getNavigationCommand('tasks', 'Open Dashboard', 'dashboard', 'Go to today\'s tasks')],
            ];
        }

        if (preg_match('/\b(course|courses|training|learning|lesson|lessons)\b/i', $text)) {
            return [
                'reply' => 'Open Courses below.',
                'commands' => [$this->getNavigationCommand('courses', 'Open Courses', 'courses', 'Go to your courses')],
            ];
        }

        if (preg_match('/\b(badge|badges|leaderboard|rank|streak)\b/i', $text)) {
            return [
                'reply' => 'Open Badges below.',
                'commands' => [$this->getNavigationCommand('badges', 'Open Badges', 'badges', 'Go to badges and leaderboard')],
            ];
        }

        if (preg_match('/\b(profile|account|settings|avatar)\b/i', $text)) {
            return [
                'reply' => 'Open Profile below.',
                'commands' => [$this->getNavigationCommand('profile', 'Open Profile', 'profile', 'Go to your profile')],
            ];
        }

        if (preg_match('/\b(deal room|crm|client list|clients list|hot leads|pipeline)\b/i', $text)) {
            return [
                'reply' => 'Open Deal Room below.',
                'commands' => [$this->getOpenDealRoomCommand()],
            ];
        }

        return null;
    }

    private function buildCrmClientListResponse(User $user, string $message, array $structuredContext): ?array
    {
        if (! $this->isClientListRequest($message)) {
            return null;
        }

        $clients = $structuredContext['active_clients'] ?? [];
        $clients = is_array($clients) ? array_values($clients) : [];

        $reply = !empty($clients)
            ? 'Here are your active clients:'
            : 'I could not find any active clients in your CRM yet. Open Deal Room to add or review leads.';

        return [
            'reply' => $reply,
            'clients' => $clients,
            'commands' => [$this->getOpenDealRoomCommand()],
        ];
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
        $model = $model !== '' ? $model : 'gpt-4o-mini';

        // OpenRouter expects provider-prefixed model IDs for many models.
        if ($this->getRuntimeProvider() === 'openrouter') {
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

    private function applyProviderHeaders($requestBuilder)
    {
        if ($this->getRuntimeProvider() !== 'openrouter') {
            return $requestBuilder;
        }

        return $requestBuilder->withHeaders([
            'HTTP-Referer' => config('app.url', 'https://realtorone.app'),
            'X-Title' => config('app.name', 'RealtorOne'),
        ]);
    }

    private function extractProviderErrorMessage(HttpResponse $response): string
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
        return rtrim($reply)."\n\nAI replies are not enabled for your tier ({$tier}). Upgrade to unlock full AI coaching in the app (Subscriptions).";
    }

    private function getRuntimeKnowledgeBase(): ?string
    {
        $cached = trim((string) cache('ai_custom_knowledge_base', ''));
        return $cached !== '' ? $cached : null;
    }

    private function getHelpCommands(): array
    {
        return [
            $this->getNavigationCommand('dashboard', 'Dashboard', 'dashboard', 'Open today\'s plan'),
            $this->getNavigationCommand('courses', 'Courses', 'courses', 'Open learning content'),
            $this->getNavigationCommand('badges', 'Badges', 'badges', 'Open badges and leaderboard'),
            $this->getNavigationCommand('profile', 'Profile', 'profile', 'Open your profile'),
            $this->getNavigationCommand('open deal room', 'Deal Room', 'client-list', 'Open your CRM workspace'),
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

    private function getOrCreateSession(User $user, string $message, ?int $sessionId): ChatSession
    {
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)->where('user_id', $user->id)->first();
            if ($session) {
                return $session;
            }
        }
        return ChatSession::create([
            'user_id' => $user->id,
            'title' => Str::limit($message, 50),
        ]);
    }

    private function getWebinarContext(string $message): array
    {
        if (!preg_match('/\b(webinar|webinars|live session|event|zoom|schedule|when|time)\b/i', $message)) {
            return [];
        }

        $webinars = \App\Models\Webinar::where('is_active', true)
            ->where('scheduled_at', '>', now()->subHours(2))
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get(['title', 'description', 'zoom_link', 'scheduled_at', 'target_tier', 'is_promotional']);

        return $webinars->isNotEmpty() ? ['upcoming_webinars' => $webinars->map(fn($w) => [
            'title' => $w->title,
            'description' => $w->description,
            'zoom_link' => $w->zoom_link,
            'scheduled_at' => optional($w->scheduled_at)->toDateTimeString(),
            'tier' => $w->target_tier ?: 'All',
            'is_promotional' => (bool)$w->is_promotional
        ])->values()->all()] : [];
    }

    private function getLeadContext(User $user, string $message): array
    {
        if (!preg_match('/\b(active client|active clients|client|clients|client name|client names|lead|leads|hot lead|pipeline|crm|database|contact|contacts|follow\s?-?up)\b/i', $message)) {
            return [];
        }

        $activeClients = \App\Models\Result::query()
            ->where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->whereNotNull('client_name')
            ->orderByDesc('date')
            ->limit(50)
            ->get(['client_name', 'source', 'status', 'date', 'value']);

        return $activeClients->isNotEmpty() ? ['active_clients' => $activeClients->map(fn($r) => [
            'client_name' => $r->client_name,
            'source' => $r->source,
            'status' => $r->status,
            'date' => optional($r->date)->toDateString(),
            'value' => $r->value,
        ])->values()->all()] : [];
    }

    private function getSalesContext(User $user, string $message): array
    {
        if (!preg_match('/\b(deal[s]? closed|closed deal|commission|revenue)\b/i', $message)) {
            return [];
        }

        $deals = \App\Models\Result::query()
            ->where('user_id', $user->id)
            ->where('type', 'deal_closed')
            ->where('date', '>=', now()->startOfMonth())
            ->orderByDesc('date')
            ->limit(50)
            ->get(['client_name', 'value', 'date', 'source']);

        return $deals->isNotEmpty() ? ['deals_closed_this_month' => [
            'count' => $deals->count(),
            'total_value' => (float) $deals->sum('value'),
            'items' => $deals->map(fn($r) => [
                'client_name' => $r->client_name,
                'value' => $r->value,
                'source' => $r->source,
                'date' => optional($r->date)->toDateString(),
            ])->values()->all(),
        ]] : [];
    }

    private function getCrmContext(User $user, string $message): array
    {
        return array_merge(
            $this->getWebinarContext($message),
            $this->getLeadContext($user, $message),
            $this->getSalesContext($user, $message)
        );
    }

    private function getCrmFormattingInstruction(array $structuredContext): ?string
    {
        $hasActiveClients = !empty($structuredContext['active_clients']) && is_array($structuredContext['active_clients']);
        $hasDeals = !empty($structuredContext['deals_closed_this_month']) && is_array($structuredContext['deals_closed_this_month']);

        if (!$hasActiveClients && !$hasDeals) {
            return null;
        }

        return <<<'PROMPT'
Formatting requirement for CRM replies:
- Keep reply short and practical.
- If active clients are present, include a markdown table with columns: Client | Status | Source | Value.
- Show up to 10 rows, ordered by most recent data already provided.
- If deals data is present, add a second short markdown table with columns: Client | Date | Source | Value.
- If any field is missing, show '-' in that cell.
- Do not claim lack of access to CRM data when it is provided in structured context.
PROMPT;
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
        $session = $this->getOrCreateSession($user, $message, $sessionId);

        // Save user message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $message,
        ]);

        $structuredContext = $this->getCrmContext($user, $message);
        $crmClientList = $this->buildCrmClientListResponse($user, $message, $structuredContext);
        if ($crmClientList !== null) {
            $contentToStore = json_encode(array_filter([
                'text' => $crmClientList['reply'],
                'clients' => $crmClientList['clients'] ?? null,
                'commands' => $crmClientList['commands'] ?? null,
            ]));

            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $contentToStore,
            ]);

            $payload = [
                'success' => true,
                'reply' => $crmClientList['reply'],
                'session_id' => $session->id,
            ];
            if (!empty($crmClientList['clients'])) {
                $payload['clients'] = $crmClientList['clients'];
            }
            if (!empty($crmClientList['commands'])) {
                $payload['commands'] = $crmClientList['commands'];
            }
            return response()->json($payload);
        }

        $navResponse = $this->buildNavigationResponse($message);
        if ($navResponse !== null) {
            $contentToStore = json_encode(array_filter([
                'text' => $navResponse['reply'],
                'commands' => $navResponse['commands'] ?? null,
            ]));

            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $contentToStore,
            ]);

            $payload = [
                'success' => true,
                'reply' => $navResponse['reply'],
                'session_id' => $session->id,
            ];
            if (!empty($navResponse['commands'])) {
                $payload['commands'] = $navResponse['commands'];
            }
            return response()->json($payload);
        }

        // Build messages for OpenAI
        $history = $session->messages()->whereIn('role', ['user', 'assistant'])->orderBy('id')->get();
        $systemContent = $this->getSystemPrompt($user, $this->getRuntimeKnowledgeBase());
        $coursesForResponse = null;

        if ($this->getUseCourseKb() && preg_match('/\b(course|courses|training|learning|enrolled|what are the courses|list courses|show courses)\b/i', $message)) {
            $coursesForResponse = $this->fetchAiCourseListForUser($user)->toArray();
            if (!empty($coursesForResponse)) {
                $list = collect($coursesForResponse)->map(fn ($c) => '- ' . ($c['title'] ?? '') . (isset($c['description']) ? ': ' . Str::limit($c['description'], 80) : ''))->implode("\n");
                $systemContent .= "\n\nCurrent courses in the system:\n" . $list;
            }
        }

        $openaiMessages = [['role' => 'system', 'content' => $systemContent]];

        if (!empty($structuredContext)) {
            $openaiMessages[] = [
                'role' => 'system',
                'content' => "Structured CRM data for this user (use this as ground truth when answering):\n".json_encode($structuredContext),
            ];
            $crmFormatInstruction = $this->getCrmFormattingInstruction($structuredContext);
            if ($crmFormatInstruction !== null) {
                $openaiMessages[] = [
                    'role' => 'system',
                    'content' => $crmFormatInstruction,
                ];
            }
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
            $clients = $basic['clients'] ?? null;
            $contentToStore = $reply;
            if (($courses !== null && !empty($courses)) || ($commands !== null && !empty($commands)) || ($clients !== null && !empty($clients))) {
                $contentToStore = json_encode(array_filter(['text' => $reply, 'courses' => $courses, 'commands' => $commands, 'clients' => $clients]));
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
            if ($clients !== null) {
                $payload['clients'] = $clients;
            }
            return response()->json($payload);
        }

        try {
            $modelToUse = $this->getRuntimeOpenAiModel();
            $url = $this->getRuntimeChatCompletionsUrl();
            /** @var HttpResponse $response */
            $response = $this->applyProviderHeaders(Http::withToken($apiKey))
                ->timeout(60)
                ->post($url, [
                    'model' => $modelToUse,
                    'messages' => $openaiMessages,
                    'max_tokens' => 1024,
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                $provider = $this->getRuntimeProvider();
                $err = $this->extractProviderErrorMessage($response);
                Log::error('AI provider error', [
                    'provider' => $provider,
                    'model' => $modelToUse,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $body,
                    'raw' => Str::limit((string) $response->body(), 1200),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI service error ['.$provider.' | '.$modelToUse.' | HTTP '.$response->status().']: '.$err,
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

        $structuredContext = $this->getCrmContext($user, $message);
        $crmClientList = $this->buildCrmClientListResponse($user, $message, $structuredContext);
        if ($crmClientList !== null) {
            $contentToStore = json_encode(array_filter([
                'text' => $crmClientList['reply'],
                'clients' => $crmClientList['clients'] ?? null,
                'commands' => $crmClientList['commands'] ?? null,
            ]));

            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $contentToStore,
            ]);

            $payload = [
                'success' => true,
                'reply' => $crmClientList['reply'],
                'session_id' => $session->id,
            ];
            if (!empty($crmClientList['clients'])) {
                $payload['clients'] = $crmClientList['clients'];
            }
            if (!empty($crmClientList['commands'])) {
                $payload['commands'] = $crmClientList['commands'];
            }
            return response()->json($payload);
        }

        $navResponse = $this->buildNavigationResponse($message);
        if ($navResponse !== null) {
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => json_encode(array_filter([
                    'text' => $navResponse['reply'],
                    'commands' => $navResponse['commands'] ?? null,
                ])),
            ]);

            $payload = [
                'success' => true,
                'reply' => $navResponse['reply'],
                'session_id' => $session->id,
            ];
            if (!empty($navResponse['commands'])) {
                $payload['commands'] = $navResponse['commands'];
            }
            return response()->json($payload);
        }

        $history = $session->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get();

        $systemContent = $this->getSystemPrompt($user, $this->getRuntimeKnowledgeBase());
        $openaiMessages = [
            ['role' => 'system', 'content' => $systemContent],
        ];

        if (!empty($structuredContext)) {
            $openaiMessages[] = [
                'role' => 'system',
                'content' => "Structured CRM data for this user (use this as ground truth when answering):\n".json_encode($structuredContext),
            ];
            $crmFormatInstruction = $this->getCrmFormattingInstruction($structuredContext);
            if ($crmFormatInstruction !== null) {
                $openaiMessages[] = [
                    'role' => 'system',
                    'content' => $crmFormatInstruction,
                ];
            }
        }

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
            $response = $this->applyProviderHeaders(Http::withToken($apiKey))
                ->timeout(60)
                ->post($url, [
                    'model' => $modelToUse,
                    'messages' => $openaiMessages,
                    'max_tokens' => 1024,
                ]);

            if (! $response->successful()) {
                $body = $response->json();
                $provider = $this->getRuntimeProvider();
                $err = $this->extractProviderErrorMessage($response);
                Log::error('AI provider error (adminSendForUser)', [
                    'provider' => $provider,
                    'model' => $modelToUse,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $body,
                    'raw' => Str::limit((string) $response->body(), 1200),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI service error ['.$provider.' | '.$modelToUse.' | HTTP '.$response->status().']: '.$err,
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
