<?php

namespace App\Models;

use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    public const DEFAULT_COLORS = [
        'sidebar' => '#020617',
        'background' => '#f1f5f9',
        'primary' => '#059669',
        'header' => '#ffffff',
    ];

    protected $fillable = ['name', 'trade_name', 'tax_id', 'phone', 'email', 'address', 'tax_rate', 'theme_colors'];

    protected $casts = ['theme_colors' => 'array'];

    public function layoutColors(): array
    {
        $colors = self::DEFAULT_COLORS;
        foreach ($colors as $key => $default) {
            $value = $this->theme_colors[$key] ?? $default;
            $colors[$key] = is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $default;
        }

        return $colors;
    }

    public static function contrastColor(string $color): string
    {
        $channels = array_map(function ($offset) use ($color) {
            $value = hexdec(substr($color, $offset, 2)) / 255;
            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, [1, 3, 5]);
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

        return $luminance > 0.179 ? '#020617' : '#ffffff';
    }
}
