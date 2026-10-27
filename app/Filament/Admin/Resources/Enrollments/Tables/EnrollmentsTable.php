<?php

namespace App\Filament\Admin\Resources\Enrollments\Tables;

use App\Filament\Exports\EnrollmentExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment_code')
                    ->label(__('filament/admin/enrollment_resource.enrollment_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/enrollment_resource.tenant.name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),
                    
                TextColumn::make('student.student_code')
                     ->label(__('filament/admin/enrollment_resource.student.student_code'))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('student.user.name')
                    ->label(__('filament/admin/enrollment_resource.student.user.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('track.name')
                    ->label('المسار')
                    ->searchable()
                    ->sortable()
                    ->placeholder('غير محدد')
                    ->toggleable(),

                TextColumn::make('courses_count')
                    ->label('عدد الكورسات')
                    ->counts('courses')
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('total_amount')
                    ->label(__('filament/admin/enrollment_resource.total_amount'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('الخصم')
                    ->money('EGP')
                    ->sortable()
                    ->default(0)
                    ->color('success')
                    ->visible(fn($record) => $record && $record->discount > 0),

                TextColumn::make('paid_amount')
                    ->label(__('filament/admin/enrollment_resource.paid_amount'))
                    ->money('EGP')
                    ->sortable(),
                    
                TextColumn::make('remaining_balance')
                    ->label(__('filament/admin/enrollment_resource.remaining_balance'))
                    ->money('EGP')
                    ->getStateUsing(fn ($record) => $record->getRemainingBalance())
                    ->color(fn ($record) => $record->isFullyPaid() ? 'success' : 'warning'),
                    
                TextColumn::make('payment_progress')
                    ->label(__('filament/admin/enrollment_resource.payment_progress'))
                    ->formatStateUsing(fn ($record) => $record->getPaymentProgress() . '%')
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->getPaymentProgress() >= 100 => 'success',
                        $record->getPaymentProgress() >= 50 => 'warning',
                        default => 'danger',
                    }),
                    
                TextColumn::make('payment_plan')
                    ->label(__('filament/admin/enrollment_resource.payment_plan'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'full' => 'دفعة واحدة',
                        'installments' => 'أقساط',
                        default => $state,
                    }),
                    
                TextColumn::make('status')
                    ->label(__('filament/admin/enrollment_resource.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'active' => 'نشط',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        'suspended' => 'موقوف',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        'suspended' => 'gray',
                        default => 'gray',
                    }),
                    
                TextColumn::make('enrollment_date')
                    ->label(__('filament/admin/enrollment_resource.enrollment_date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('tenant_id')
                    ->label('الفرع')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn() => auth()->user()?->is_super_admin ?? false),

                SelectFilter::make('track_id')
                    ->label('المسار')
                    ->relationship('track', 'name', fn($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_plan')
                    ->label('خطة الدفع')
                    ->options([
                        'full' => 'دفعة واحدة',
                        'installments' => 'أقساط',
                    ])
                    ->native(false),
                    
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'active' => 'نشط',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        'suspended' => 'موقوف',
                    ])
                    ->native(false),
                    
                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),
                \App\Filament\Admin\Resources\Enrollments\Actions\CancelSubscriptionAction::make(),
                \App\Filament\Admin\Resources\Enrollments\Actions\ExecuteRefundAction::make(),
                EditAction::make()
                    ->label('تعديل')
                    ->visible(fn($record) => !auth()->user()->is_super_admin && $record->status !== 'cancelled'),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(EnrollmentExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي'),
                    RestoreBulkAction::make()
                        ->label('استعادة'),
                ]),
            ])
            ->defaultSort('enrollment_date', 'desc');
    }
}
