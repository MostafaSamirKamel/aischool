<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CARD = 'card';
    case ONLINE = 'online';
    case CHEQUE = 'cheque';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'نقدي',
            self::BANK_TRANSFER => 'تحويل بنكي',
            self::CARD => 'بطاقة',
            self::ONLINE => 'أونلاين',
            self::CHEQUE => 'شيك',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CASH => 'heroicon-o-banknotes',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::CARD => 'heroicon-o-credit-card',
            self::ONLINE => 'heroicon-o-globe-alt',
            self::CHEQUE => 'heroicon-o-document-text',
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
