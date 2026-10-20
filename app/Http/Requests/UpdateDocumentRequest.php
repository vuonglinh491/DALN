<?php

namespace App\Http\Requests;

class UpdateDocumentRequest extends DocumentRequest
{
    public function rules(): array
    {
        return $this->baseRules() + ['file' => $this->fileRules(false)];
    }
}
