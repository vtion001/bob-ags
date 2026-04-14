<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public const TYPE_STRING = 'string';
    public const TYPE_JSON = 'json';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_PASSWORD = 'password';

    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            self::TYPE_JSON => json_decode($this->value, true),
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            default => $this->value,
        };
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->typed_value : $default;
    }

    public static function setValue(string $key, mixed $value, string $type = self::TYPE_STRING): void
    {
        $stringValue = match ($type) {
            self::TYPE_JSON => json_encode($value),
            self::TYPE_BOOLEAN => $value ? 'true' : 'false',
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stringValue, 'type' => $type]
        );
    }
}
