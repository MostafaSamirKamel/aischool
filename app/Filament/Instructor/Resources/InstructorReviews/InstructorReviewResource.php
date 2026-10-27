<?php

namespace App\Filament\Instructor\Resources\InstructorReviews;

use App\Filament\Instructor\Resources\InstructorReviews\Pages\ListInstructorReviews;
use App\Filament\Instructor\Resources\InstructorReviews\Pages\ViewInstructorReview;
use App\Filament\Instructor\Resources\InstructorReviews\Tables\InstructorReviewsTable;
use App\Models\InstructorReview;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstructorReviewResource extends Resource
{
    protected static ?string $model = InstructorReview::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-star';
    }

    protected static ?string $navigationLabel = 'تقييماتي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'التقييمات';

    public static function getNavigationGroup(): ?string
    {
        return __('filament/instructor/navigation.management');
    }

    protected static ?int $navigationSort = 50;

    public static function getNavigationBadge(): ?string
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) return null;

        return InstructorReview::where('instructor_id', $instructor->id)
            ->where('status', 'approved')
            ->count();
    }

    public static function getEloquentQuery(): Builder
    {
        $instructor = auth()->user()->instructor;
        
        if (!$instructor) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('instructor_id', $instructor->id)
            ->where('status', 'approved')
            ->orderByDesc('created_at');
    }

    public static function table(Table $table): Table
    {
        return InstructorReviewsTable::table($table);
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
            'index' => ListInstructorReviews::route('/'),
            'view' => ViewInstructorReview::route('/{record}'),
        ];
    }
}
