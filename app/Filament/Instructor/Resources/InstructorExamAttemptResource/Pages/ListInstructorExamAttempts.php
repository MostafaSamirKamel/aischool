<?php

namespace App\Filament\Instructor\Resources\InstructorExamAttemptResource\Pages;

use App\Filament\Instructor\Resources\InstructorExamAttemptResource\InstructorExamAttemptResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListInstructorExamAttempts extends ListRecords
{
    protected static string $resource = InstructorExamAttemptResource::class;

    protected static ?string $title = null;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل'),
            
            'passed' => Tab::make('ناجح')
                ->modifyQueryUsing(fn($query) => $query->where('passed', true))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('passed', true)->count())
                ->badgeColor('success'),
            
            'failed' => Tab::make('راسب')
                ->modifyQueryUsing(fn($query) => $query->where('passed', false))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('passed', false)->count())
                ->badgeColor('danger'),
            
            'today' => Tab::make('اليوم')
                ->modifyQueryUsing(fn($query) => $query->whereDate('completed_at', today()))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->whereDate('completed_at', today())->count())
                ->badgeColor('info'),

            'this_week' => Tab::make('هذا الأسبوع')
                ->modifyQueryUsing(fn($query) => $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('warning'),

            'high_scores' => Tab::make('درجات عالية')
                ->modifyQueryUsing(fn($query) => $query->where('percentage', '>=', 90))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('percentage', '>=', 90)->count())
                ->badgeColor('success'),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/instructor/list_instructor_exam_attempts.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/list_instructor_exam_attempts.title');
    }


}
