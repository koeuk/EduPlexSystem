<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class PriceRangeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $min = null;
        $max = null;

        // Value can be "min,max" string format
        if (is_string($value)) {
            $parts = explode(',', $value);
            $min = isset($parts[0]) && $parts[0] !== '' ? (float) $parts[0] : null;
            $max = isset($parts[1]) && $parts[1] !== '' ? (float) $parts[1] : null;
        }
        // Value can be numeric array [0 => min, 1 => max] from URL parsing
        elseif (is_array($value) && isset($value[0])) {
            $min = $value[0] !== '' ? (float) $value[0] : null;
            $max = isset($value[1]) && $value[1] !== '' ? (float) $value[1] : null;
        }
        // Value can be associative array ['min' => x, 'max' => y]
        elseif (is_array($value)) {
            $min = isset($value['min']) && $value['min'] !== '' ? (float) $value['min'] : null;
            $max = isset($value['max']) && $value['max'] !== '' ? (float) $value['max'] : null;
        }

        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
    }
}
