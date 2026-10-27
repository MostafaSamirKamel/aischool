<?php

namespace App\Filament\Admin\Resources\Groups\Pages;

use App\Filament\Admin\Resources\Groups\GroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // Instructor لا يمكنه إنشاء مجموعات
        if (!$user->is_super_admin && !$user->hasRole('instructor')) {
            $actions[] = CreateAction::make()
                ->label(__('filament/admin/list_groups.create_button'));
        }

        return $actions;
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getTableQuery();

        // Instructor: يرى مجموعاته فقط
        if ($user->hasRole('instructor')) {
            return $query->where('instructor_id', $user->id);
        }

        return $query;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_groups.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_groups.title');
    }

}
