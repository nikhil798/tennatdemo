<?php

namespace App\Models;

class GlobalSetting extends LandlordModel
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'autoload',
    ];

    protected function casts(): array
    {
        return [
            'autoload' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting?->castStoredValue() ?? $default;
    }

    public static function putValue(string $key, mixed $value, string $type = 'string', bool $autoload = true): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => static::serializeValue($value),
                'type' => $type,
                'autoload' => $autoload,
            ],
        );
    }

    public function castStoredValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOL),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => $this->value ? json_decode($this->value, true) : null,
            default => $this->value,
        };
    }

    protected static function serializeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_array($value) || is_object($value)
            ? json_encode($value, JSON_THROW_ON_ERROR)
            : (string) $value;
    }
}
