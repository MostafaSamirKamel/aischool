<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions;

use App\Filament\Instructor\Resources\AttendanceSessions\Pages\CreateAttendanceSession;
use App\Filament\Instructor\Resources\AttendanceSessions\Pages\EditAttendanceSession;
use App\Filament\Instructor\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Instructor\Resources\AttendanceSessions\Schemas\AttendanceSessionForm;
use App\Filament\Instructor\Resources\AttendanceSessions\Tables\AttendanceSessionsTable;
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

    protected static ?string $recordTitleAttribute = 'date';
    
    public static function getNavigationGroup(): string
    {
        return __('filament/instructor/navigation.groups_management');
    }
    
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if (!$user || !$user->instructor) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        return parent::getEloquentQuery()
            ->whereHas('group', function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor->id);
            })
            ->where('tenant_id', $user->tenant_id);
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
            'take' => Pages\TakeAttendance::route('/take-attendance'),
            'reports' => Pages\AttendanceReports::route('/reports'),
        ];
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
        return __('filament/instructor/attendance_session_resource.navigation_label');
    }
    public static function getModelLabel(): string
    {
        return __('filament/instructor/attendance_session_resource.model_label');
    }
    public static function getPluralModelLabel(): string
    {
        return __('filament/instructor/attendance_session_resource.plural_model_label');
    }



}
