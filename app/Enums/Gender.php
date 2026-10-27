<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string
    {
        return match($this) {
            self::MALE => 'ذكر',
            self::FEMALE => 'أنثى',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::MALE => '♂',
            self::FEMALE => '♀',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
