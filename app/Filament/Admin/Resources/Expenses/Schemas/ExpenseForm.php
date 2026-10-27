<?php

namespace App\Filament\Admin\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use App\Models\ExpenseCategory;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/admin/expense_resource.basic_info'))
                    ->schema([
                        Group::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('expense_code')
                                    ->label(__('filament/admin/expense_resource.expense_code'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default('سيتم توليده تلقائياً')
                                    ->helperText(__('filament/admin/expense_resource.auto_generated')),
                                
                                Select::make('expense_category_id')
                                    ->label(__('filament/admin/expense_resource.expense_category_id'))
                                    ->options(fn() => ExpenseCategory::where('is_active', true)
                                        ->pluck('name', 'id'))
                                    ->required()
                                    // ->searchable()
                                    ->preload()
                                ,
                                
                                TextInput::make('title')
                                    ->label(__('filament/admin/expense_resource.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),
                        
                        Textarea::make('description')
                            ->label(__('filament/admin/expense_resource.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                Section::make(__('filament/admin/expense_resource.financial_details'))
                    ->schema([
                        Group::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label(__('filament/admin/expense_resource.amount'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('جنيه')
                                    ->minValue(0)
                                    ->step(0.01),
                                
                                DatePicker::make('expense_date')
                                    ->label(__('filament/admin/expense_resource.expense_date'))
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y'),

                                Select::make('vault_id')
                                    ->label('الخزنة المصروف منها')
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
                                    ->required(fn(callable $get) => $get('status') === 'paid')
                                    ->helperText('إلزامي عند الصرف الفعلي (الحالة: مدفوع). لا ترحل نقدية من الخزنة إلا بعد الدفع.'),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make(__('filament/admin/expense_resource.vendor_info'))
                    ->schema([
                        Group::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('vendor_name')
                                    ->label(__('filament/admin/expense_resource.vendor_name'))
                                    ->maxLength(255),
                                
                                TextInput::make('invoice_number')
                                    ->label(__('filament/admin/expense_resource.invoice_number'))
                                    ->maxLength(255),
                            ]),
                        
                        Textarea::make('notes')
                            ->label(__('filament/admin/expense_resource.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
                
                Section::make(__('filament/admin/expense_resource.approval'))
                    ->schema([
                        Select::make('status')
                            ->label(__('filament/admin/expense_resource.status'))
                            ->options([
                                'pending' => __('filament/admin/expense_resource.statuses.pending'),
                                'approved' => __('filament/admin/expense_resource.statuses.approved'),
                                'paid' => __('filament/admin/expense_resource.statuses.paid'),
                                'cancelled' => __('filament/admin/expense_resource.statuses.cancelled'),
                            ])
                            ->default('pending')
                            ->required()
                            ->reactive(),
                    ])
                    ->collapsible(),
            ]);
    }
}
