<?php

namespace App\Filament\Admin\Resources\VaultTransfers\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;

class VaultTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات التحويل')
                    ->schema([
                        TextInput::make('transfer_code')
                            ->label('كود التحويل')
                            ->default(fn () => \App\Models\VaultTransfer::generateTransferCode())
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        DatePicker::make('transfer_date')
                            ->label('تاريخ التحويل')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('حالة التحويل')
                            ->options([
                                'pending' => '⏳ قيد الانتظار',
                                'completed' => '✅ مكتمل',
                                'cancelled' => '❌ ملغي',
                            ])
                            ->default('pending')
                            ->required()
                            ->columnSpan(1)
                            ->disabled(fn ($record) => $record?->isCompleted() || $record?->isCancelled()),
                    ])
                    ->columns(3),

                Section::make('تفاصيل التحويل')
                    ->schema([
                        Select::make('from_vault_id')
                            ->label('من خزنة')
                            ->relationship('fromVault', 'name', function ($query) {
                                return $query
                                    ->where('is_active', true)
                                    ->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('vaults.tenant_id', $tid))
                                    ->select('vaults.*');
                            })
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getDisplayNameWithBalance())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // منع اختيار نفس الخزينة
                                if ($state === $get('to_vault_id')) {
                                    $set('to_vault_id', null);
                                }
                            })
                            ->columnSpan(1)
                            ->helperText('اختر الخزينة التي سيتم التحويل منها')
                            ->disabled(fn ($record) => $record?->isCompleted()),

                        Select::make('to_vault_id')
                            ->label('إلى خزنة')
                            ->relationship('toVault', 'name', function ($query, callable $get) {
                                $fromVaultId = $get('from_vault_id');
                                return $query
                                    ->where('is_active', true)
                                    ->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('vaults.tenant_id', $tid))
                                    ->when($fromVaultId, fn ($q) => $q->where('id', '!=', $fromVaultId))
                                    ->select('vaults.*');
                            })
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getDisplayNameWithBalance())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpan(1)
                            ->helperText('اختر الخزينة التي سيتم التحويل إليها')
                            ->disabled(fn ($record) => $record?->isCompleted()),

                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0.01)
                            ->step(0.01)
                            ->columnSpan(1)
                            ->disabled(fn ($record) => $record?->isCompleted())
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $status = $get('status');
                                    $fromVaultId = $get('from_vault_id');
                                    if ($status === 'completed' && $fromVaultId && is_numeric($value)) {
                                        $vault = \App\Models\Vault::find($fromVaultId);
                                        if ($vault && (float) $vault->getCurrentBalance() < (float) $value) {
                                            $canView = auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_balances'));
                                            if ($canView) {
                                                $fail("رصيد الخزنة المحول منها غير كافٍ. الرصيد المتاح: " . number_format($vault->getCurrentBalance(), 2) . " ج.م");
                                            } else {
                                                $fail("لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية.");
                                            }
                                        }
                                    }
                                },
                            ]),
                    ])
                    ->columns(3),

                Section::make('ملاحظات وتفاصيل إضافية')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record?->isCompleted()),

                        Textarea::make('cancellation_reason')
                            ->label('سبب الإلغاء')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->isCancelled())
                            ->disabled(),
                    ]),

                Section::make('معلومات التتبع')
                    ->schema([
                        Placeholder::make('transferred_by')
                            ->label('تم التحويل بواسطة')
                            ->content(fn ($record) => $record?->transferredBy?->name ?? 'غير محدد'),

                        Placeholder::make('approved_by')
                            ->label('تم الاعتماد بواسطة')
                            ->content(fn ($record) => $record?->approvedBy?->name ?? 'لم يعتمد بعد'),

                        Placeholder::make('approved_at')
                            ->label('تاريخ الاعتماد')
                            ->content(fn ($record) => $record?->approved_at?->format('d/m/Y h:i A') ?? 'لم يعتمد بعد'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}
