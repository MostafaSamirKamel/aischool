<?php

namespace App\Filament\Admin\Resources\Classrooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('filament/admin/classroom_resource.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                    
                TextInput::make('capacity')
                    ->label(__('filament/admin/classroom_resource.capacity'))
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),
                    
                TextInput::make('location')
                    ->label(__('filament/admin/classroom_resource.location'))
                    ->maxLength(255),
                    
                Select::make('status')
                    ->label(__('filament/admin/classroom_resource.status'))
                    ->options(['active' => __('filament/admin/classroom_resource.status.active'), 'inactive' => __('filament/admin/classroom_resource.status.inactive'), 'maintenance' => __('filament/admin/classroom_resource.status.maintenance')])
                    ->default(__('filament/admin/classroom_resource.status_default'))
                    ->required(),

                RichEditor::make('description')
                    ->label(__('filament/admin/classroom_resource.description'))
                    ->columnSpanFull(),

                RichEditor::make('equipment')
                    ->label(__('filament/admin/classroom_resource.equipment'))
                    ->columnSpanFull(),
            ]);
    }
}
