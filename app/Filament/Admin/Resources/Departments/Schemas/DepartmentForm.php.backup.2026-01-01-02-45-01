<?php

namespace App\Filament\Admin\Resources\Departments\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // tenant_id is set automatically by BelongsToBranch trait

                TextInput::make('name')
                    ->label(__('filament/admin/department_resource.name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignorable: fn($record) => $record, modifyRuleUsing: function ($rule) {
                        return $rule->where('tenant_id', auth()->user()->tenant_id);
                    }),

                RichEditor::make('description')
                    ->label(__('filament/admin/department_resource.description'))
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label(__('filament/admin/department_resource.is_active'))
                    ->default(true),
            ]);
    }
}
