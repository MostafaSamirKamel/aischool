<?php

namespace App\Filament\Admin\Resources\RevenueCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RevenueCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('filament/admin/revenue_category_resource.name'))
                    ->required(),
                TextInput::make('name_en')
                    ->label(__('filament/admin/revenue_category_resource.name_en'))
                    ->default(null),
                Textarea::make('description')
                    ->label(__('filament/admin/revenue_category_resource.description'))
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('code')
                    ->label(__('filament/admin/revenue_category_resource.code'))
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('filament/admin/revenue_category_resource.is_active'))
                    ->required(),
            ]);
    }
}
