<?php

namespace App\Enums;

enum StudentStatus: string
{
    case ACTIVE = 'active';       // Student is actively enrolled
    case INACTIVE = 'inactive';   // Student account is inactive
    case SUSPENDED = 'suspended'; // Student is suspended
    case GRADUATED = 'graduated'; // Student has graduated

    /**
     * Get the label for the student status.
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
            'graduated' => __('Graduated'),
            default => __('Unknown'),
        };
    }

    /**
     * Get the color for the student status.
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
            'graduated' => 'info',
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
