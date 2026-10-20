<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'subject' => 'Môn học',
            'original_filename' => 'tai-lieu.txt',
            'storage_path' => 'documents/test/' . Str::uuid() . '.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size_bytes' => 100,
            'checksum' => hash('sha256', Str::random(20)),
            'visibility' => 'private',
            'moderation_status' => 'draft',
            'processing_status' => 'pending',
            'current_version' => 1,
        ];
    }

    public function approved()
    {
        return $this->state(fn () => ['visibility' => 'public', 'moderation_status' => 'approved', 'approved_at' => now(), 'submitted_at' => now()]);
    }

    public function pending()
    {
        return $this->state(fn () => ['visibility' => 'public', 'moderation_status' => 'pending', 'submitted_at' => now()]);
    }

    public function indexed()
    {
        return $this->state(fn () => ['processing_status' => 'completed']);
    }
}
