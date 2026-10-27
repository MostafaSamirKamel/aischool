<?php

namespace App\Filament\Student\Resources;

use BackedEnum;
use App\Models\InstructorReview;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Student\Resources\InstructorReviewResource\Pages\EditInstructorReview;
use App\Filament\Student\Resources\InstructorReviewResource\Schemas\InstructorReviewForm;
use App\Filament\Student\Resources\InstructorReviewResource\Tables\InstructorReviewsTable;
use App\Filament\Student\Resources\InstructorReviewResource\Pages\ListInstructorReviews;
use App\Filament\Student\Resources\InstructorReviewResource\Pages\CreateInstructorReview;

class InstructorReviewResource extends Resource
{
    protected static ?string $model = InstructorReview::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = '';

    protected static ?string $navigationLabel = 'تقييمات المدرسين';

    protected static ?string $modelLabel = 'تقييم مدرس';

    protected static ?string $pluralModelLabel = 'تقييمات المدرسين';

    public static function getNavigationLabel(): string
    {
        return 'تقييمات المدرسين';
    }

    public static function form(Schema $schema): Schema
    {
        return InstructorReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstructorReviewsTable::configure($table);
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
            'index' => ListInstructorReviews::route('/'),
            'create' => CreateInstructorReview::route('/create'),
            'edit' => EditInstructorReview::route('/{record}/edit'),
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
