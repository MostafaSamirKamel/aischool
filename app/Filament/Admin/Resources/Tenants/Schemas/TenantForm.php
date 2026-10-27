<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('filament/admin/tenant_resource.name'))
                    ->required()
                    ->maxLength(255),

                FileUpload::make('image')
                    ->label(__('filament/admin/tenant_resource.image'))
                    ->image()
                    ->directory('tenants')
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1', '4:3', '16:9'])
                    ->maxSize(2048)
                    ->columnSpan(2),

                TextInput::make('slug')
                    ->label(__('filament/admin/tenant_resource.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique('tenants', 'slug', modifyRuleUsing: function ($rule, $get, $record) {
                        return $record ? $rule->ignore($record->id) : $rule;
                    }),
                TextInput::make('domain')
                    ->label(__('filament/admin/tenant_resource.domain'))
                    ->maxLength(255)
                    ->url(),
                Toggle::make('is_active')
                    ->label(__('filament/admin/tenant_resource.is_active'))
                    ->default(true),
                RichEditor::make('description')
                    ->label(__('filament/admin/tenant_resource.description'))
                    ->columnSpan('full')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'link',
                    ]),
                KeyValue::make('settings')
                    ->label(__('filament/admin/tenant_resource.settings'))
                    ->columnSpan('full'),
            ]);
    }
}
