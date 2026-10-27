<?php

namespace App\Filament\Parent\Pages;

use BackedEnum;
use Filament\Tables;
use App\Models\Payment;
use Filament\Pages\Page;
use App\Models\Enrollment;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Concerns\InteractsWithTable;

class MyPayments extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.parent.pages.my-payments';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return __('مدفوعاتي');
    }

    public function getTitle(): string
    {
        return __('مدفوعاتي واشتراكاتي');
    }

    public function getHeading(): string
    {
        return __('مدفوعاتي واشتراكاتي');
    }

    public function table(Table $table): Table
    {
        $selectedStudentId = \App\Services\Parent\ParentStudentAccessService::getSelectedStudentId();
        
        // Get enrollment IDs for this student
        $enrollmentIds = $selectedStudentId 
            ? Enrollment::where('student_id', $selectedStudentId)->pluck('id')
            : collect();

        return $table
            ->query(
                Payment::query()
                    ->when($selectedStudentId, fn($q) => $q->whereIn('enrollment_id', $enrollmentIds), fn($q) => $q->whereRaw('1 = 0'))
                    ->with(['enrollment.groups.course'])
                    ->orderBy('payment_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('payment_code')
                    ->label(__('رقم الدفعة'))
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-o-hashtag')
                    ->copyable()
                    ->copyMessage(__('تم النسخ'))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('enrollment.groups.course.name')
                    ->label(__('الكورس'))
                    ->formatStateUsing(function ($record) {
                        return $record->enrollment->groups->pluck('course.name')->join(', ');
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('enrollment.groups.name')
                    ->label(__('المجموعة'))
                    ->formatStateUsing(function ($record) {
                        return $record->enrollment->groups->pluck('name')->join(', ');
                    })
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('المبلغ'))
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-banknotes'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label(__('تاريخ الدفع'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('طريقة الدفع'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'نقدي',
                        'card' => 'بطاقة',
                        'bank_transfer' => 'تحويل بنكي',
                        'online' => 'دفع إلكتروني',
                        default => $state,
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('الحالة'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'completed' => 'مكتمل',
                        'pending' => 'قيد الانتظار',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'completed' => 'مكتمل',
                        'pending' => 'قيد الانتظار',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),
                    
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('طريقة الدفع'))
                    ->options([
                        'cash' => 'نقدي',
                        'card' => 'بطاقة',
                        'bank_transfer' => 'تحويل بنكي',
                        'online' => 'دفع إلكتروني',
                    ]),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label(__('من تاريخ')),
                        DatePicker::make('until')
                            ->label(__('إلى تاريخ')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('payment_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('payment_date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Action::make('view_receipt')
                    ->label(__('filament/student/my_payments.view_receipt'))
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'completed')
                    ->modalHeading(fn ($record) => __('إيصال دفع رقم') . ' ' . $record->payment_code)
                    ->modalContent(fn ($record) => view('filament.parent.pages.payment-receipt-modal', [
                        'payment' => $record,
                    ]))
                    ->modalWidth('3xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('إغلاق')),

                // Download receipt action - disabled until route is created
                // Action::make('download_receipt')
                //     ->label(__('filament/student/my_payments.download_receipt'))
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->color('success')
                //     ->visible(fn ($record) => $record->status === 'completed')
                //     ->url(fn ($record) => route('student.payment.receipt', $record))
                //     ->openUrlInNewTab(),
            ])
            ->emptyStateHeading(__('لا توجد مدفوعات'))
            ->emptyStateDescription(__('لم تقم بأي مدفوعات بعد'))
            ->emptyStateIcon('heroicon-o-credit-card');
    }
}
