<?php

namespace App\Filament\Supervisor\Resources\AttendanceSessions;

use App\Filament\Supervisor\Resources\AttendanceSessions\Pages\CreateAttendanceSession;
use App\Filament\Supervisor\Resources\AttendanceSessions\Pages\EditAttendanceSession;
use App\Filament\Supervisor\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Supervisor\Resources\AttendanceSessions\Pages\ViewAttendanceSession;
use App\Filament\Supervisor\Resources\AttendanceSessions\Schemas\AttendanceSessionForm;
use App\Filament\Supervisor\Resources\AttendanceSessions\Schemas\AttendanceSessionInfolist;
use App\Filament\Supervisor\Resources\AttendanceSessions\Tables\AttendanceSessionsTable;
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

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $recordTitleAttribute = 'attendance_date';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'الإشراف';
    }

    public static function form(Schema $schema): Schema
    {
        return AttendanceSessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceSessionInfolist::configure($schema);
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
            'view' => ViewAttendanceSession::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $supervisor = $user?->supervisor;
        $supervisorIds = array_filter([$user?->id, $supervisor?->id]);
        
        // Get IDs of groups supervised by this supervisor
        $groupIds = \App\Models\Group::where(function ($q) use ($supervisorIds, $user) {
            $q->whereIn('supervisor_id', $supervisorIds)
                ->orWhereHas('assignedSupervisors', fn ($sq) => $sq->where('users.id', $user?->id));
        })->pluck('id');
        
        return parent::getEloquentQuery()
            ->whereIn('group_id', $groupIds)
            ->with(['group', 'instructor.user', 'attendances']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/supervisor/attendance_session_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/supervisor/attendance_session_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/supervisor/attendance_session_resource.plural_model_label');
    }



}
