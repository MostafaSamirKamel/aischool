<?php

namespace App\Filament\Admin\Resources\AssignmentResource\ExamAttemptResource\Pages;

use App\Filament\Admin\Resources\AssignmentResource\ExamAttemptResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListExamAttempts extends ListRecords
{
    protected static string $resource = ExamAttemptResource::class;

    protected static ?string $title = 'نتائج الامتحانات';

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

            'high_scores' => Tab::make('درجات عالية')
                ->modifyQueryUsing(fn($query) => $query->where('percentage', '>=', 90))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('percentage', '>=', 90)->count())
                ->badgeColor('warning'),
        ];
    }
}
