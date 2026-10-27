<?php

namespace App\Filament\Admin\Resources\Classrooms\Pages;

use App\Filament\Admin\Resources\Classrooms\ClassroomResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('availability')
                ->label('جدول توفر وإشغال القاعات')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->url(ClassroomResource::getUrl('availability')),
        ];

        if (!auth()->user()->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_classrooms.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_classrooms.title');
    }

}
