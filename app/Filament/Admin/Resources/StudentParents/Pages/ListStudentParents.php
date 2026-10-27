<?php

namespace App\Filament\Admin\Resources\StudentParents\Pages;

use App\Filament\Admin\Resources\StudentParents\StudentParentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListStudentParents extends ListRecords
{
    protected static string $resource = StudentParentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        
        if (!auth()->user()->is_super_admin) {
            $actions[] = CreateAction::make();
        }
        
        return $actions;
    }

    public function getTitle(): string
    {
        return __('filament/admin/list_student_parents.title');
    }

    public function getHeading(): string
    {
        return __('filament/admin/list_student_parents.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_student_parents.title');
    }

}
