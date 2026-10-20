<?php

namespace App\Http\Requests;

class StoreDocumentRequest extends DocumentRequest
{
    public function rules(): array
    {
        return $this->baseRules() + ['file' => $this->fileRules(true)];
    }
}
