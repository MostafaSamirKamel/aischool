<?php

namespace App\Filament\Admin\Resources\Nationalities\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class NationalityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            // ->columns(2)
            ->components([
                // tenant_id is set automatically by BelongsToBranch trait

                Section::make('بيانات الجنسية')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament/admin/nationality_resource.name'))
                            ->unique()
                            ->required()
                            ->columnSpan(1),
                        // TextInput::make('code')
                        //     ->label(__('filament/admin/nationality_resource.code'))
                        //     ->required()
                        //     ->unique('nationalities', 'code', ignorable: fn($record) => $record)
                        //     ->columnSpan(1),
                    ])
                // ->columns(2),
            ]);
    }
}
