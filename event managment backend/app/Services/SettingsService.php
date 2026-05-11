<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    public function all(): Collection
    {
        if (! Schema::hasTable('settings')) {
            return collect();
        }

        return Cache::remember('settings.all', now()->addMinutes(30), function () {
            return Setting::query()->get()->mapWithKeys(function (Setting $setting) {
                return [$setting->key => $this->castValue($setting->value, $setting->type)];
            });
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key, $default);
    }

    public function putMany(string $group, array $payload): void
    {
        foreach ($payload as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $group.'.'.$key],
                [
                    'group' => $group,
                    'type' => is_array($value) ? 'json' : gettype($value),
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                ]
            );
        }

        Cache::forget('settings.all');
    }

    public function grouped(): array
    {
        return $this->all()->undot()->toArray();
    }

    private function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'double', 'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => $value ? json_decode($value, true) : [],
            default => $value,
        };
    }
}
