<?php

namespace App\Http\Controllers;

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

    /**
     * System prompt with knowledge about the app.
     */
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are Reven, a friendly AI assistant for the RealtorOne real estate training app. You help users with:

- **Courses & training**: Enrolled courses, course progress, the Cold Calling Master program, Million Dirham Beliefs Program, and learning content
- **Daily tasks**: Tasks on the Dashboard, activity logging, momentum scores
- **Leaderboard & badges**: How to improve rank, earned badges, streaks
- **Account**: Profile updates, avatar, settings
- **Real estate tips**: Cold calling, prospecting, follow-ups, client meetings

Be concise, helpful, and encouraging. If asked about something outside this scope, politely say you focus on real estate training and the app. Use markdown for lists when helpful.
PROMPT;
    }

    /**
     * Basic keyword replies when OPENAI_API_KEY is not set.
     * Returns ['reply' => string, 'courses' => array|null, 'commands' => array|null].
     */
    private function getBasicReply(string $message, ?User $user): array
    {
        $text = strtolower(trim($message));
        if (preg_match('/\b(hi|hello|hey|hola|howdy)\b/', $text)) {
            return ['reply' => 'Hi! I\'m Reven, your assistant for the RealtorOne app. Type **help** to see what I can do.', 'courses' => null, 'commands' => null];
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
            $courses = $this->fetchCourseList();
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
        return ['reply' => 'I\'m here to help! Type **help** to see what I can do, or try: courses, tasks, badges, profile.', 'courses' => null, 'commands' => null];
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

        $systemContent = $this->getSystemPrompt();
        $coursesForResponse = null;
        if (preg_match('/\b(course|courses|training|learning|enrolled|what are the courses|list courses|show courses)\b/i', $message)) {
            $coursesForResponse = $this->fetchCourseList()->toArray();
            if (!empty($coursesForResponse)) {
                $list = collect($coursesForResponse)->map(fn ($c) => '- ' . ($c['title'] ?? '') . (isset($c['description']) ? ': ' . Str::limit($c['description'], 80) : ''))->implode("\n");
                $systemContent .= "\n\nCurrent courses in the system:\n" . $list;
            }
        }

        $openaiMessages = [
            ['role' => 'system', 'content' => $systemContent],
        ];

        $recent = $history->take(-self::MAX_HISTORY);
        foreach ($recent as $m) {
            $parsed = $this->parseStoredContent($m->content);
            $openaiMessages[] = ['role' => $m->role, 'content' => $parsed];
        }

        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            $basic = $this->getBasicReply($message, $user);
            $reply = $basic['reply'];
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
            /** @var HttpResponse $response */
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post(self::OPENAI_URL, [
                    'model' => 'gpt-4o-mini',
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

            $contentToStore = $reply;
            if ($coursesForResponse !== null && !empty($coursesForResponse)) {
                $contentToStore = json_encode(['text' => $reply, 'courses' => $coursesForResponse]);
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
