<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'app.settings.all';

    /** All settings as a key=>value array (cached). */
    public static function allValues(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    /** Read a single setting with a fallback default. */
    public static function get(string $key, $default = null)
    {
        $all = static::allValues();
        $val = $all[$key] ?? null;
        return ($val === null || $val === '') ? $default : $val;
    }

    /** Write one setting and bust the cache. */
    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /** Write many settings at once. */
    public static function putMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }
}
