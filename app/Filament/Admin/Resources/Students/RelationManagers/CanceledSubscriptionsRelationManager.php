<?php

namespace App\Filament\Admin\Resources\Students\RelationManagers;

use App\Filament\Admin\Resources\Enrollments\Actions\ExecuteRefundAction;
use App\Models\Enrollment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class CanceledSubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'canceledEnrollments';

    protected static ?string $title = 'الاشتراكات الملغاة — Canceled Subscriptions';

    protected static ?string $modelLabel = 'اشتراك ملغي';

    protected static ?string $pluralModelLabel = 'الاشتراكات الملغاة';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('enrollment_code')
            ->columns([
                Tables\Columns\TextColumn::make('enrollment_code')
                    ->label('رقم التسجيل')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('course_display')
                    ->label('الكورس / المسار')
                    ->getStateUsing(function (Enrollment $record) {
                        $courses = $record->courses->pluck('name')->implode(', ');
                        if (empty($courses) && $record->track) {
                            return 'مسار: ' . $record->track->name;
                        }
                        return $courses ?: 'غير محدد';
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('تاريخ التسجيل')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cancelled_at')
                    ->label('تاريخ الإلغاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('cancellation_reason')
                    ->label('سبب الإلغاء')
                    ->limit(30)
                    ->tooltip(fn($state) => $state),

                Tables\Columns\TextColumn::make('cancelledBy.name')
                    ->label('موظف الإلغاء')
                    ->placeholder('غير محدد'),

                Tables\Columns\TextColumn::make('settlement_type_label')
                    ->label('نوع التسوية')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المسدد أصلاً')
                    ->getStateUsing(fn(Enrollment $record) => $record->getEffectivePaidAmount())
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('المديونية المتبقية')
                    ->getStateUsing(fn(Enrollment $record) => $record->getRemainingBalance())
                    ->money('EGP')
                    ->color(fn(Enrollment $record) => $record->getRemainingBalance() > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('waived_amount')
                    ->label('المبلغ المسقط')
                    ->money('EGP')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('refundable_amount')
                    ->label('المستحق للاسترداد')
                    ->money('EGP')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('refunded_amount')
                    ->label('المصروف فعلياً')
                    ->money('EGP')
                    ->color('success'),

                Tables\Columns\TextColumn::make('settlement_status_label')
                    ->label('حالة التسوية')
                    ->badge()
                    ->color(fn(Enrollment $record) => match ($record->settlement_status) {
                        'completed' => 'success',
                        'pending_refund' => 'warning',
                        default => 'info',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('settlement_type')
                    ->label('نوع التسوية')
                    ->options([
                        'keep_debt' => 'بقاء المديونية',
                        'waive_debt' => 'إسقاط المتبقي',
                        'session_based' => 'حسب الجلسات',
                        'full_refund' => 'استرداد كامل',
                        'partial_refund' => 'استرداد جزئي',
                        'custom' => 'مخصصة',
                    ]),
                Tables\Filters\SelectFilter::make('settlement_status')
                    ->label('حالة التسوية')
                    ->options([
                        'settled' => 'تمت التسوية',
                        'pending_refund' => 'قيد انتظار الاسترداد',
                        'completed' => 'مكتمل بالكامل',
                    ]),
            ])
            ->actions([
                Action::make('view_cancellation_details')
                    ->label('تفاصيل التسوية')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn(Enrollment $record) => 'تفاصيل إلغاء الاشتراك #' . $record->enrollment_code)
                    ->modalContent(function (Enrollment $record) {
                        $cancellation = $record->cancellation;
                        $studentName = $record->student?->user?->name ?? 'طالب';
                        $courses = $record->courses->pluck('name')->implode(', ') ?: ($record->track ? 'مسار: ' . $record->track->name : 'غير محدد');

                        return new HtmlString("
                            <div class='space-y-4 text-sm' dir='rtl'>
                                <div class='grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800'>
                                    <div><strong>الطالب:</strong> {$studentName}</div>
                                    <div><strong>الكورس / المسار:</strong> {$courses}</div>
                                    <div><strong>تاريخ التسجيل:</strong> " . ($record->enrollment_date?->format('Y-m-d') ?? '-') . "</div>
                                    <div><strong>تاريخ الإلغاء:</strong> " . ($record->cancelled_at?->format('Y-m-d H:i') ?? '-') . "</div>
                                    <div><strong>الموظف المسؤول:</strong> " . ($record->cancelledBy?->name ?? 'غير محدد') . "</div>
                                    <div><strong>نوع التسوية:</strong> {$record->settlement_type_label}</div>
                                </div>
                                <div class='p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg'>
                                    <strong class='text-red-700 dark:text-red-300'>سبب الإلغاء:</strong>
                                    <p class='mt-1 text-slate-700 dark:text-slate-300'>{$record->cancellation_reason}</p>
                                    " . ($record->cancellation_notes ? "<p class='mt-2 text-xs text-slate-500'><strong>ملاحظات إضافية:</strong> {$record->cancellation_notes}</p>" : "") . "
                                </div>
                                <div class='grid grid-cols-2 md:grid-cols-4 gap-3 text-center'>
                                    <div class='p-3 bg-slate-100 dark:bg-slate-800 rounded-lg'>
                                        <span class='block text-xs text-slate-500'>المسدد أصلاً</span>
                                        <span class='font-bold text-slate-800 dark:text-white'>" . number_format($record->getEffectivePaidAmount(), 2) . " ج.م</span>
                                    </div>
                                    <div class='p-3 bg-slate-100 dark:bg-slate-800 rounded-lg'>
                                        <span class='block text-xs text-slate-500'>المحتجز للمدرسة</span>
                                        <span class='font-bold text-slate-800 dark:text-white'>" . number_format($record->retained_amount, 2) . " ج.م</span>
                                    </div>
                                    <div class='p-3 bg-slate-100 dark:bg-slate-800 rounded-lg'>
                                        <span class='block text-xs text-slate-500'>المبلغ المسقط</span>
                                        <span class='font-bold text-amber-600'>" . number_format($record->waived_amount, 2) . " ج.م</span>
                                    </div>
                                    <div class='p-3 bg-slate-100 dark:bg-slate-800 rounded-lg'>
                                        <span class='block text-xs text-slate-500'>المديونية المتبقية</span>
                                        <span class='font-bold text-red-600'>" . number_format($record->getRemainingBalance(), 2) . " ج.م</span>
                                    </div>
                                </div>
                                <div class='grid grid-cols-2 gap-3 text-center'>
                                    <div class='p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-lg'>
                                        <span class='block text-xs text-emerald-600 dark:text-emerald-400'>المستحق للاسترداد</span>
                                        <span class='font-bold text-emerald-700 dark:text-emerald-300'>" . number_format($record->refundable_amount, 2) . " ج.م</span>
                                    </div>
                                    <div class='p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-lg'>
                                        <span class='block text-xs text-emerald-600 dark:text-emerald-400'>المصروف فعلياً</span>
                                        <span class='font-bold text-emerald-700 dark:text-emerald-300'>" . number_format($record->refunded_amount, 2) . " ج.م</span>
                                    </div>
                                </div>
                            </div>
                        ");
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->modalWidth(\Filament\Support\Enums\Width::FourExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter(),
                ExecuteRefundAction::make(),
            ])
            ->defaultSort('cancelled_at', 'desc')
            ->emptyStateHeading('لا توجد اشتراكات ملغاة')
            ->emptyStateDescription('لا يوجد سجل لاشتراكات ملغاة لهذا الطالب.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
