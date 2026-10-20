<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gọi Gemini REST API từ backend. Khóa API chỉ đọc từ .env (config daln.gemini.key), không bao giờ ghi vào log/DB/giao diện.
 */
class GeminiService
{
    /**
     * @param array $contents danh sách [['role' => 'user'|'model', 'parts' => [['text' => '...']]], ...]
     * @return array{text:string, finish:?string}
     */
    public function generate(string $system, array $contents, ?int $maxTokens = null): array
    {
        $key = config('daln.gemini.key');
        if (!$key) {
            throw new GeminiException('Chưa cấu hình GEMINI_API_KEY trên máy chủ.', 'config');
        }
        $url = rtrim(config('daln.gemini.base_url'), '/') . '/models/' . config('daln.gemini.model') . ':generateContent';
        $payload = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => $maxTokens ?? (int) config('daln.settings.ai_max_output_tokens'),
            ],
        ];

        $attempts = 1 + max(0, (int) config('daln.gemini.retries'));
        $last = null;

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $res = Http::withHeaders(['x-goog-api-key' => $key])
                    ->acceptJson()
                    ->timeout((int) config('daln.gemini.timeout'))
                    ->post($url, $payload);
            } catch (ConnectionException $e) {
                $last = new GeminiException('Không kết nối được tới Gemini hoặc đã quá thời gian chờ.', 'timeout');
                $this->pause($i, $attempts);
                continue;
            }

            if ($res->successful()) {
                return $this->parse($res->json() ?? []);
            }

            $status = $res->status();
            Log::warning('Gemini trả lỗi', ['status' => $status]); // không log key/payload
            if ($status === 429) {
                $last = new GeminiException('Đã vượt hạn mức sử dụng Gemini (quota). Vui lòng thử lại sau.', 'quota');
            } elseif ($status >= 500) {
                $last = new GeminiException('Dịch vụ Gemini đang gặp sự cố tạm thời.', 'server');
            } elseif (in_array($status, [401, 403], true)) {
                throw new GeminiException('Gemini từ chối khóa API. Quản trị viên cần kiểm tra GEMINI_API_KEY.', 'auth');
            } else {
                throw new GeminiException('Gemini không xử lý được yêu cầu (HTTP ' . $status . ').', 'http');
            }
            $this->pause($i, $attempts);
        }

        throw $last ?? new GeminiException('Lỗi không xác định khi gọi Gemini.', 'http');
    }

    private function pause(int $attempt, int $max): void
    {
        if ($attempt < $max) {
            usleep(400000 * $attempt);
        }
    }

    private function parse(array $json): array
    {
        if (!empty($json['promptFeedback']['blockReason'])) {
            throw new GeminiException('Gemini từ chối xử lý yêu cầu này vì lý do an toàn.', 'blocked');
        }
        $cand = $json['candidates'][0] ?? null;
        $text = '';
        foreach (($cand['content']['parts'] ?? []) as $part) {
            $text .= $part['text'] ?? '';
        }
        $text = trim($text);
        if ($text === '') {
            throw new GeminiException('Gemini không trả về nội dung.', 'empty');
        }

        return ['text' => $text, 'finish' => $cand['finishReason'] ?? null];
    }
}
