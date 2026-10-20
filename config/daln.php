<?php

return [
    // Tệp tải lên
    'upload' => [
        'extensions' => ['pdf', 'docx', 'txt'],
        'mimes' => [
            'pdf' => ['application/pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
            'txt' => ['text/plain'],
        ],
    ],

    // Giá trị mặc định của các cấu hình admin chỉnh được (bảng settings ghi đè)
    'settings' => [
        'upload_max_kb' => (int) env('UPLOAD_MAX_KB', 20480),
        'ai_daily_limit' => 30,          // số câu hỏi AI / người dùng / ngày
        'ai_max_question_chars' => 1000, // độ dài tối đa câu hỏi
        'ai_max_output_tokens' => 1500,  // giới hạn đầu ra của Gemini
        'preview_chars' => 1500,         // độ dài phần xem trước công khai
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 40),
        'retries' => (int) env('GEMINI_RETRIES', 2),
    ],

    // Truy xuất ngữ cảnh cho RAG
    'rag' => [
        'top_k' => 6,
        'context_chars' => 9000,
        'chunk_size' => 1200,
        'chunk_overlap' => 150,
        'history_messages' => 6,
    ],

    'modes' => [
        'ask' => 'Hỏi đáp',
        'summarize' => 'Tóm tắt',
        'explain' => 'Giải thích khái niệm',
        'keypoints' => 'Tìm ý chính',
        'quiz' => 'Tạo câu hỏi ôn tập',
    ],
];
