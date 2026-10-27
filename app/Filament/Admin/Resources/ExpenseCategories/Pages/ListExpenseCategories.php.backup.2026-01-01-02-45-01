<?php

namespace App\Filament\Admin\Resources\ExpenseCategories\Pages;

use App\Filament\Admin\Resources\ExpenseCategories\ExpenseCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // المستخدم الرئيسي لا يمكنه إضافة فئات مصروفات
        if (!$user->is_super_admin) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }
    public function getTitle(): string
    {
        return __('filament/admin/list_expense_categories.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/list_expense_categories.title');
    }

}
