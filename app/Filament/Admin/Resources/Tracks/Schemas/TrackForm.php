<?php

namespace App\Filament\Admin\Resources\Tracks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;

class TrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('filament/admin/track_resource.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label(__('filament/admin/track_resource.description'))
                    ->columnSpanFull(),
                    
                TextInput::make('order')
                    ->label(__('filament/admin/track_resource.order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                    
                Select::make('status')
                    ->label(__('filament/admin/track_resource.status'))
                    ->options(['active' => __('filament/admin/track_resource.status.active'), 'inactive' => __('filament/admin/track_resource.status.inactive')])
                    ->default(__('filament/admin/track_resource.status_default'))
                    ->required(),
            ]);
    }
}
