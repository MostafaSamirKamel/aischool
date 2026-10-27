<?php

namespace App\Filament\Admin\Resources\SmsLogResource;

use App\Filament\Admin\Resources\SmsLogResource\Pages;
use App\Filament\Admin\Resources\SmsLogResource\Schemas\SmsLogForm;
use App\Filament\Admin\Resources\SmsLogResource\Tables\SmsLogsTable;
use App\Models\SmsLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    protected static ?int $navigationSort = 51;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getModel()::query();

        if (!($user->is_super_admin && is_null($user->tenant_id))) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/admin/navigation.students_and_enrollment');
    }

    public static function form(Schema $schema): Schema
    {
        return SmsLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
        ];
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/sms_log_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/admin/sms_log_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/sms_log_resource.plural_model_label');
    }



}
