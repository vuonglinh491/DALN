<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    /** Đọc cấu hình: bảng settings ghi đè, nếu không có thì dùng mặc định trong config/daln.php */
    public static function get(string $key, $default = null)
    {
        try {
            $all = Cache::remember('daln.settings', 300, fn () => static::query()->pluck('setting_value', 'setting_key')->all());
        } catch (\Throwable $e) {
            $all = [];
        }
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }
        return $default ?? config('daln.settings.' . $key);
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['setting_key' => $key], ['setting_value' => (string) $value]);
        Cache::forget('daln.settings');
    }
}
