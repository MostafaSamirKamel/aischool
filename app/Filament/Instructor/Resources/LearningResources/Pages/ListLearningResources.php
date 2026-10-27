<?php

namespace App\Filament\Instructor\Resources\LearningResources\Pages;

use App\Filament\Instructor\Resources\LearningResources\LearningResourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningResources extends ListRecords
{
    protected static string $resource = LearningResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('filament/instructor/learning_resource_resource.action_create')),
        ];
    }
    public function getTitle(): string
    {
        return __('filament/instructor/learning_resource_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/instructor/learning_resource_resource.plural_model_label');
    }

}
