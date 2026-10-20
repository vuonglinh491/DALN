<?php

namespace App\Services\Documents;

/** PDF không có lớp văn bản (bản quét) -> cần OCR, hệ thống chưa tích hợp OCR. */
class NeedsOcrException extends ExtractionException
{
}
