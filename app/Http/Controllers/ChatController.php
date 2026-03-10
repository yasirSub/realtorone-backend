<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $openaiMessages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt()],
        ];

        $recent = $history->take(-self::MAX_HISTORY);
        foreach ($recent as $m) {
            $openaiMessages[] = ['role' => $m->role, 'content' => $m->content];
        }

        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'AI service not configured. Add OPENAI_API_KEY to .env',
            ], 503);
        }

        try {
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
                \Log::error('OpenAI error', ['status' => $response->status(), 'body' => $body]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI service error: ' . $err,
                ], 502);
            }

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? '';

            // Save assistant reply
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            return response()->json([
                'success' => true,
                'reply' => trim($reply),
                'session_id' => $session->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Chat error', ['exception' => $e->getMessage()]);
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
}
