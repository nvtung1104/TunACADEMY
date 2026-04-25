<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AiChatSession, AiChatLog, AiChatRateLimit};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    private const DAILY_LIMIT = 50;
    private const MODEL = 'claude-haiku-4-5-20251001';
    private const MAX_TOKENS = 1024;
    private const MAX_HISTORY = 20;

    public function history(Request $request)
    {
        $sessions = $request->user()->aiChatSessions()
            ->withCount('logs')
            ->with(['logs' => fn($q) => $q->latest()->limit(1)])
            ->latest()
            ->paginate(20);

        return $this->success($sessions);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:2000',
            'session_id' => 'nullable|exists:ai_chat_sessions,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $user = $request->user();

        $rateLimit = AiChatRateLimit::firstOrCreate(
            ['student_id' => $user->id, 'date' => today()],
            ['message_count' => 0, 'token_count' => 0]
        );
        abort_if($rateLimit->message_count >= self::DAILY_LIMIT, 429, 'Bạn đã đạt giới hạn ' . self::DAILY_LIMIT . ' tin nhắn hôm nay');

        $session = $request->session_id
            ? AiChatSession::where('student_id', $user->id)->findOrFail($request->session_id)
            : AiChatSession::create([
                'student_id' => $user->id,
                'subject_id' => $request->subject_id,
                'title'      => mb_substr($request->message, 0, 50),
            ]);

        AiChatLog::create(['session_id' => $session->id, 'role' => 'user', 'content' => $request->message]);

        $history = $session->logs()
            ->orderBy('created_at')
            ->latest()
            ->limit(self::MAX_HISTORY)
            ->get()
            ->sortBy('created_at')
            ->map(fn($log) => ['role' => $log->role, 'content' => $log->content])
            ->values()
            ->toArray();

        $aiResponse = $this->callClaude($history, $request->subject_id);

        AiChatLog::create(['session_id' => $session->id, 'role' => 'assistant', 'content' => $aiResponse]);
        $rateLimit->increment('message_count');

        return $this->success([
            'session_id'       => $session->id,
            'reply'            => $aiResponse,
            'messages_left'    => self::DAILY_LIMIT - $rateLimit->fresh()->message_count,
        ]);
    }

    public function clearHistory(Request $request)
    {
        $request->user()->aiChatSessions()->delete();

        return $this->success(null, 'Đã xóa lịch sử chat');
    }

    private function callClaude(array $messages, ?int $subjectId): string
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            return 'Chức năng AI chưa được cấu hình. Vui lòng liên hệ quản trị viên.';
        }

        $systemPrompt = 'Bạn là trợ lý học tập thông minh dành cho học sinh K-12 tại Việt Nam. '
            . 'Hãy giải thích rõ ràng, ngắn gọn và phù hợp với lứa tuổi học sinh. '
            . 'Luôn khuyến khích học sinh tự suy nghĩ và khám phá. '
            . 'Ưu tiên trả lời bằng tiếng Việt trừ khi được yêu cầu dùng ngôn ngữ khác.';

        if ($subjectId) {
            $subject = \App\Models\Subject::find($subjectId);
            if ($subject) {
                $systemPrompt .= " Môn học hiện tại: {$subject->name}.";
            }
        }

        try {
            $client = new Client(['timeout' => 30, 'connect_timeout' => 10]);

            $response = $client->post('https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ],
                'json' => [
                    'model'      => self::MODEL,
                    'max_tokens' => self::MAX_TOKENS,
                    'system'     => $systemPrompt,
                    'messages'   => $messages,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['content'][0]['text'] ?? 'Không thể nhận phản hồi từ AI.';
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            if ($statusCode === 429) {
                return 'Hệ thống AI đang bận, vui lòng thử lại sau vài giây.';
            }
            return 'Có lỗi kết nối đến AI. Vui lòng thử lại sau.';
        } catch (\Exception) {
            return 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
}
