<?php

namespace App\Filament\Admin\Resources\Revenues\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use App\Models\RevenueCategory;

class RevenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/admin/revenue_resource.basic_info'))
                    ->schema([
                        Group::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('revenue_code')
                                    ->label(__('filament/admin/revenue_resource.revenue_code'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default('سيتم توليده تلقائياً')
                                    ->helperText(__('filament/admin/revenue_resource.auto_generated')),
                                
                                Select::make('revenue_category_id')
                                    ->label(__('filament/admin/revenue_resource.revenue_category_id'))
                                    ->options(fn() => RevenueCategory::where('is_active', true)
                                        ->pluck('name', 'id'))
                                    ->required()
                                    // ->searchable()
                                    ->preload()
                                ,
                                
                                TextInput::make('title')
                                    ->label(__('filament/admin/revenue_resource.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),
                        
                        Textarea::make('description')
                            ->label(__('filament/admin/revenue_resource.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                Section::make(__('filament/admin/revenue_resource.financial_details'))
                    ->schema([
                        Group::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label(__('filament/admin/revenue_resource.amount'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('جنيه')
                                    ->minValue(0)
                                    ->step(0.01),
                                
                                DatePicker::make('revenue_date')
                                    ->label(__('filament/admin/revenue_resource.revenue_date'))
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y'),
                                
                                Select::make('vault_id')
                                    ->label('الخزنة المستلمة')
                                    ->relationship('vault', 'name', function ($query) {
                                        $user = auth()->user();
                                        return $query->active()
                                            ->when(
                                                !($user->is_super_admin && is_null($user->tenant_id)),
                                                fn($q) => $q->where('tenant_id', $user->tenant_id)
                                            );
                                    })
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getDisplayNameWithBalance())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('اختر الخزنة التي تم التحصيل من خلالها'),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make(__('filament/admin/revenue_resource.customer_info'))
                    ->schema([
                        Group::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('customer_name')
                                    ->label(__('filament/admin/revenue_resource.customer_name'))
                                    ->maxLength(255),
                                
                                TextInput::make('invoice_number')
                                    ->label(__('filament/admin/revenue_resource.invoice_number'))
                                    ->maxLength(255),
                                
                                Select::make('payment_id')
                                    ->label(__('filament/admin/revenue_resource.payment_id'))
                                    ->relationship('payment', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('filament/admin/revenue_resource.linked_payment')),
                            ]),
                        
                        Textarea::make('notes')
                            ->label(__('filament/admin/revenue_resource.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
                
                Section::make(__('filament/admin/revenue_resource.status_section'))
                    ->schema([
                        Select::make('status')
                            ->label(__('filament/admin/revenue_resource.status'))
                            ->options([
                                'pending' => __('filament/admin/revenue_resource.statuses.pending'),
                                'received' => __('filament/admin/revenue_resource.statuses.received'),
                                'cancelled' => __('filament/admin/revenue_resource.statuses.cancelled'),
                            ])
                            ->default('received')
                            ->required()
                            ->reactive(),
                    ])
                    ->collapsible(),
            ]);
    }
}
