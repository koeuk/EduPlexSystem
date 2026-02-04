<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';       // User account is active
    case INACTIVE = 'inactive';   // User account is inactive
    case SUSPENDED = 'suspended'; // User account is suspended

    /**
     * Get the label for the user status.
     *
     * @param string $status
     * @return string
     */
    public static function getLabel(string $status): string
    {
        return match ($status) {
            'active' => __('Active'),
            'inactive' => __('Inactive'),
            'suspended' => __('Suspended'),
            default => __('Unknown'),
        };
    }

    /**
     * Get the color for the user status.
     *
     * @param string $status
     * @return string
     */
    public static function getColor(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'inactive' => 'gray',
            'suspended' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get all status options for dropdowns.
     *
     * @return array
     */
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => self::getLabel($case->value),
        ], self::cases());
    }
}
