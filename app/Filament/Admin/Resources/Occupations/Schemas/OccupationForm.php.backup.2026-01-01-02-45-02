<?php

namespace App\Filament\Admin\Resources\Occupations\Schemas;

use Filament\Schemas\Schema;

class OccupationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label(__('filament/admin/occupation_resource.name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(),

                \Filament\Forms\Components\RichEditor::make('description')
                    ->label(__('filament/admin/occupation_resource.description'))
                    ->columnSpanFull(),

                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label(__('filament/admin/occupation_resource.is_active'))
                    ->default(true),
            ]);
    }
}
