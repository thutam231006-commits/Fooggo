<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Support\Arr;

class FoodCustomizationService
{
    public const RICE_TYPES = [
        'regular' => ['label' => 'Cơm tấm truyền thống', 'price' => 0],
        'garlic' => ['label' => 'Cơm gạo lứt đảo', 'price' => 5000],
        'brown' => ['label' => 'Ít cơm / Giảm bột', 'price' => 0],
    ];

    public const EXTRAS = [
        'egg' => ['label' => 'Trứng ốp la lòng đào', 'price' => 7000],
        'meatloaf' => ['label' => 'Chả trứng hấp truyền thống', 'price' => 8000],
        'soup' => ['label' => 'Bí hầm hải sản thanh nhiệt', 'price' => 6000],
        'vegetables' => ['label' => 'Canh rong biển thịt bằm', 'price' => 5000],
        'extra_rice' => ['label' => 'Thêm cơm', 'price' => 12000],
    ];

    public function normalize(array $input): array
    {
        $riceType = array_key_exists($input['rice_type'] ?? '', self::RICE_TYPES) ? $input['rice_type'] : 'regular';
        $extras = array_values(array_intersect(array_keys(self::EXTRAS), Arr::wrap($input['extras'] ?? [])));
        sort($extras);

        return array_filter([
            'rice_type' => $riceType,
            'extras' => $extras,
            'sauce' => in_array($input['sauce'] ?? '', ['default', 'spicy', 'mild'], true) ? $input['sauce'] : 'default',
            'spice_level' => in_array($input['spice_level'] ?? '', ['none', 'medium', 'hot'], true) ? $input['spice_level'] : 'none',
            'note' => filled($input['note'] ?? null) ? trim($input['note']) : null,
        ], fn ($value) => $value !== null);
    }

    public function unitPrice(Food $food, array $options): float
    {
        $ricePrice = self::RICE_TYPES[$options['rice_type'] ?? 'regular']['price'] ?? 0;
        $extrasPrice = collect($options['extras'] ?? [])->sum(fn ($extra) => self::EXTRAS[$extra]['price'] ?? 0);

        return (float) $food->price + $ricePrice + $extrasPrice;
    }

    public function signature(array $options): string
    {
        return hash('sha256', json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function labels(array $options): array
    {
        return array_values(array_filter([
            self::RICE_TYPES[$options['rice_type'] ?? 'regular']['label'] ?? null,
            ...array_map(fn ($extra) => self::EXTRAS[$extra]['label'] ?? null, $options['extras'] ?? []),
            filled($options['note'] ?? null) ? 'Ghi chú: '.$options['note'] : null,
        ]));
    }
}
