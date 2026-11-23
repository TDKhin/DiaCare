<?php

namespace App\Support;

class GlucoseRange
{
    public static function forContext(?string $context): ?array
    {
        $aliases = [
            'fasting' => 'fasting', 'fasting / before breakfast' => 'fasting',
            'before meal' => 'before_meal', 'after meal' => 'after_meal', '2 hours after meal' => 'after_meal',
            'bedtime' => 'bedtime', 'before exercise' => 'exercise_before', 'after exercise' => 'exercise_after',
        ];
        $key = $aliases[strtolower(trim((string) $context))] ?? null;

        return $key ? config('glucose.time_slots.'.$key) : null;
    }

    public static function status(?float $value, ?string $context): string
    {
        $range = self::forContext($context);
        if ($value === null || ! $range) return 'Recorded';

        return $value < $range['min'] ? 'Low' : ($value > $range['max'] ? 'High' : 'In range');
    }
}
