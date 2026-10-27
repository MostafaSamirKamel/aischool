<?php

namespace App\Filament\Admin\Resources\Instructors\Pages;

use App\Filament\Admin\Resources\Instructors\InstructorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstructors extends ListRecords
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة مدرسين
        if (!$user->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_instructors.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_instructors.title');
    }

}
