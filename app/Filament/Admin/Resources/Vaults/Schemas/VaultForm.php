<?php

namespace App\Filament\Admin\Resources\Vaults\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class VaultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('معلومات الخزينة الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament/admin/vault_resource.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: خزينة نقدي رئيسية')
                            ->columnSpan(1),

                        Select::make('type')
                            ->label(__('filament/admin/vault_resource.type'))
                            ->options(['cash' => __('filament/admin/vault_resource.type.cash'), 'vodafone' => __('filament/admin/vault_resource.type.vodafone'), 'instapay' => __('filament/admin/vault_resource.type.instapay')])
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('opening_balance')
                            ->label(__('filament/admin/vault_resource.opening_balance'))
                            ->numeric()
                            ->default(0)
                            ->prefix('ج.م')
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('الرصيد عند بداية استخدام الخزينة')
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label(__('filament/admin/vault_resource.is_active'))
                            ->default(true)
                            ->helperText('فقط الخزائن النشطة تظهر في قوائم الاختيار')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('تفاصيل إضافية')
                    ->schema([
                        Textarea::make('description')
                            ->label(__('filament/admin/vault_resource.description'))
                            ->rows(3)
                            ->placeholder('معلومات إضافية عن الخزينة...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
