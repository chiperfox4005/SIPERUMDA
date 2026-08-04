<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    /**
     * Membaca file JSON konfigurasi dengan caching
     */
    public static function get(string $file, ?string $key = null): mixed
    {
        $cacheKey = "config.{$file}";
        
        return Cache::remember($cacheKey, 3600, function () use ($file, $key) {
            $path = config_path("templates/{$file}.json");
            
            if (!File::exists($path)) {
                return [];
            }

            $data = json_decode(File::get($path), true);

            if ($key === null) {
                return $data;
            }

            return $data[$key] ?? null;
        });
    }

    /**
     * Helper khusus untuk mendapatkan menu
     */
    public static function getMenu(string $section = 'main_menu'): array
    {
        return self::get('menu', $section) ?? [];
    }
}