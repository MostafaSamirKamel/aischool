<?php

namespace App\Filament\Admin\Resources\SmsLogResource\Pages;

use App\Filament\Admin\Resources\SmsLogResource\SmsLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListSmsLogs extends ListRecords
{
    protected static string $resource = SmsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('settings')
                ->label('إعدادات SMS')
                ->icon('heroicon-o-cog-6-tooth')
                ->url(fn () => \App\Filament\Admin\Resources\SmsConfigs\SmsConfigResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
