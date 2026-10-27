<?php

namespace App\Filament\Admin\Resources\AttendanceSessions;

use App\Filament\Admin\Resources\AttendanceSessions\Pages\CreateAttendanceSession;
use App\Filament\Admin\Resources\AttendanceSessions\Pages\EditAttendanceSession;
use App\Filament\Admin\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Admin\Resources\AttendanceSessions\Schemas\AttendanceSessionForm;
use App\Filament\Admin\Resources\AttendanceSessions\Tables\AttendanceSessionsTable;
use App\Models\AttendanceSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceSessionResource extends Resource
{
    protected static ?string $model = AttendanceSession::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'attendance_date';

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/attendance_session_resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/admin/attendance_session_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/admin/attendance_session_resource.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/admin/attendance_session_resource.navigation_group');
    }

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

    public static function form(Schema $schema): Schema
    {
        return AttendanceSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceSessions::route('/'),
            'create' => CreateAttendanceSession::route('/create'),
            'edit' => EditAttendanceSession::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
