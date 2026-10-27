<?php

namespace App\Filament\Instructor\Resources\SessionSchedules;

use App\Filament\Instructor\Resources\SessionSchedules\Pages\CreateSessionSchedule;
use App\Filament\Instructor\Resources\SessionSchedules\Pages\EditSessionSchedule;
use App\Filament\Instructor\Resources\SessionSchedules\Pages\ListSessionSchedules;
use App\Filament\Instructor\Resources\SessionSchedules\Pages\ViewSessionSchedule;
use App\Filament\Instructor\Resources\SessionSchedules\Schemas\SessionScheduleForm;
use App\Filament\Instructor\Resources\SessionSchedules\Tables\SessionSchedulesTable;
use App\Models\SessionSchedule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SessionScheduleResource extends Resource
{
    protected static ?string $model = SessionSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar-days';
    }

    protected static ?string $navigationLabel = 'جدول السيشنات';

    protected static ?string $modelLabel = 'سيشن';

    protected static ?string $pluralModelLabel = 'جدول السيشنات';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/instructor/navigation.content_management');
    }

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user || !$user->instructor)
            return null;

        return SessionSchedule::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('instructor_id', $user->instructor->id)
            ->count();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if (!$user || !$user->instructor) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('tenant_id', $user->tenant_id)
            ->where('instructor_id', $user->instructor->id)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return SessionScheduleForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionSchedulesTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSessionSchedules::route('/'),
            'create' => CreateSessionSchedule::route('/create'),
            'edit' => EditSessionSchedule::route('/{record}/edit'),
            'view' => ViewSessionSchedule::route('/{record}'),
        ];
    }
}
