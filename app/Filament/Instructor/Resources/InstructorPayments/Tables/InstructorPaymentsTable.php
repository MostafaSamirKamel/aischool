<?php

namespace App\Filament\Instructor\Resources\InstructorPayments\Tables;

use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class InstructorPaymentsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period_label')
                    ->label('الفترة')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->weight('bold')
                    ->size('base'),

                TextColumn::make('period_start')
                    ->label('من')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('period_end')
                    ->label('إلى')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_sessions')
                    ->label('السيشنات المكتملة')
                    ->numeric()
                    ->sortable()
                    ->suffix(' سيشن')
                    ->icon('heroicon-o-check-circle')
                    ->color('success'),

                TextColumn::make('total_sessions')
                    ->label('إجمالي السيشنات')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->suffix(' سيشن')
                    ->icon('heroicon-o-academic-cap'),

                TextColumn::make('rate_per_session')
                    ->label('سعر السيشن')
                    ->money('EGP')
                    ->sortable()
                    ->icon('heroicon-o-currency-dollar'),

                TextColumn::make('sessions_amount')
                    ->label('قيمة السيشنات')
                    ->money('EGP')
                    ->sortable()
                    ->icon('heroicon-o-calculator')
                    ->color('info'),

                TextColumn::make('base_amount')
                    ->label('الراتب الأساسي')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-briefcase'),

                TextColumn::make('bonus_amount')
                    ->label('المكافآت')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-gift')
                    ->color('success'),

                TextColumn::make('deduction_amount')
                    ->label('الخصومات')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger'),

                TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->size('lg')
                    ->icon('heroicon-o-banknotes')
                    ->color('success'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'قيد المراجعة',
                        'approved' => 'معتمد',
                        'paid' => 'مدفوع',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->icon(fn($state) => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'approved' => 'heroicon-o-check-badge',
                        'paid' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-calendar-days')
                    ->placeholder('لم يتم الدفع بعد'),

                TextColumn::make('vault.name')
                    ->label('الخزنة المصروف منها')
                    ->badge()
                    ->default('غير محدد')
                    ->toggleable(),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'check' => 'شيك',
                        'online' => 'دفع إلكتروني',
                        default => $state ?? 'غير محدد',
                    })
                    ->icon(fn($state) => match ($state) {
                        'cash' => 'heroicon-o-banknotes',
                        'bank_transfer' => 'heroicon-o-building-library',
                        'check' => 'heroicon-o-document-text',
                        'online' => 'heroicon-o-globe-alt',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'معتمد',
                        'paid' => 'مدفوع',
                        'cancelled' => 'ملغي',
                    ])
                    ->multiple()
                    ->default(['pending', 'approved', 'paid']),

                \Filament\Tables\Filters\Filter::make('period_start')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('من تاريخ')
                            ->placeholder('اختر التاريخ'),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('إلى تاريخ')
                            ->placeholder('اختر التاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->where('period_start', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->where('period_end', '<=', $data['to']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from']) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('من: ' . \Carbon\Carbon::parse($data['from'])->format('Y-m-d'))
                                ->removeField('from');
                        }
                        if ($data['to']) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('إلى: ' . \Carbon\Carbon::parse($data['to'])->format('Y-m-d'))
                                ->removeField('to');
                        }
                        return $indicators;
                    }),

                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'check' => 'شيك',
                        'online' => 'دفع إلكتروني',
                    ])
                    ->multiple(),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->persistFiltersInSession()
            ->actions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('period_start', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateHeading('لا توجد دفعات')
            ->emptyStateDescription('لم يتم إنشاء أي دفعات بعد')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
