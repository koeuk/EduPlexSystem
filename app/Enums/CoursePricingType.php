<?php

namespace App\Enums;

enum CoursePricingType: string
{
    case FREE = 'free';
    case PAID = 'paid';

    /**
     * Get the label for the pricing type.
     */
    public static function getLabel(string $type): string
    {
        return match ($type) {
            'free' => __('Free'),
            'paid' => __('Paid'),
            default => __('Unknown'),
        };
    }

    /**
     * Get all type options for dropdowns.
     */
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => self::getLabel($case->value),
        ], self::cases());
    }

    /**
     * Get all valid values for validation.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
