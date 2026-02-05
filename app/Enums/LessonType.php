<?php

namespace App\Enums;

enum LessonType: string
{
    case VIDEO = 'video';
    case TEXT = 'text';
    case QUIZ = 'quiz';

    /**
     * Get the label for the lesson type.
     */
    public static function getLabel(string $type): string
    {
        return match ($type) {
            'video' => __('Video'),
            'text' => __('Text'),
            'quiz' => __('Quiz'),
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
