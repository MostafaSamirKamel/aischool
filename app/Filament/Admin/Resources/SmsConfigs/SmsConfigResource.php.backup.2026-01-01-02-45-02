<?php

namespace App\Filament\Admin\Resources\SmsConfigs;

use App\Filament\Admin\Resources\SmsConfigs\Pages\CreateSmsConfig;
use App\Filament\Admin\Resources\SmsConfigs\Pages\EditSmsConfig;
use App\Filament\Admin\Resources\SmsConfigs\Pages\ListSmsConfigs;
use App\Filament\Admin\Resources\SmsConfigs\Schemas\SmsConfigForm;
use App\Filament\Admin\Resources\SmsConfigs\Tables\SmsConfigsTable;
use App\Models\SmsConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmsConfigResource extends Resource
{
    protected static ?string $model = SmsConfig::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?int $navigationSort = 50;

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
        return 'البيانات الأساسية';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/sms_settings.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/sms_settings.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/sms_settings.heading');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Super admin بدون tenant_id يرى جميع الإعدادات
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            return parent::getEloquentQuery();
        }

        // المستخدمون الآخرون يرون إعدادات tenant الخاص بهم فقط
        return parent::getEloquentQuery()
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Schema $schema): Schema
    {
        return SmsConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsConfigsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSmsConfigs::route('/'),
            'create' => CreateSmsConfig::route('/create'),
            'edit' => EditSmsConfig::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        
        // Check if tenant already has a config
        $query = static::getModel()::query();
        
        if ($user->is_super_admin && is_null($user->tenant_id)) {
            // Super admin can create for any tenant that doesn't have config
            return true;
        }
        
        // Regular users can only create if their tenant doesn't have config
        return !$query->where('tenant_id', $user->tenant_id)->exists();
    }

    public static function canDelete($record): bool
    {
        return false; // لا يمكن حذف الإعدادات
    }
}
