<?php

namespace App\Filament\Instructor\Resources\InstructorPayments;

use App\Filament\Instructor\Resources\InstructorPayments\Pages\ListInstructorPayments;
use App\Filament\Instructor\Resources\InstructorPayments\Pages\ViewInstructorPayment;
use App\Filament\Instructor\Resources\InstructorPayments\Tables\InstructorPaymentsTable;
use App\Models\InstructorPayment;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstructorPaymentResource extends Resource
{
    protected static ?string $model = InstructorPayment::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-banknotes';
    }

    protected static ?string $navigationLabel = 'أجوري';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'أجر';

    protected static ?string $pluralModelLabel = 'الأجور';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/instructor/navigation.management');
    }

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user || !$user->instructor)
            return null;

        return InstructorPayment::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('instructor_id', $user->instructor->id)
            ->whereIn('status', ['pending', 'approved'])
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
            ->orderByDesc('period_start');
    }

    public static function table(Table $table): Table
    {
        return InstructorPaymentsTable::table($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorPayments::route('/'),
            'view' => ViewInstructorPayment::route('/{record}'),
        ];
    }
}
