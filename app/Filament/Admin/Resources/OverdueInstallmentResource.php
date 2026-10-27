<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OverdueInstallmentResource\Pages;
use App\Models\Installment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OverdueInstallmentResource extends Resource
{
    protected static ?string $model = Installment::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-exclamation-triangle';
    }

    protected static ?string $navigationLabel = 'الأقساط المتأخرة';

    protected static ?string $modelLabel = 'قسط متأخر';

    protected static ?string $pluralModelLabel = 'الأقساط المتأخرة';

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير المالية';
    }

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->overdue()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->orderBy('due_date', 'asc');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل القسط')
                    ->schema([
                        TextInput::make('enrollment.student.user.name')
                            ->label('اسم الطالب')
                            ->disabled(),

                        TextInput::make('enrollment.enrollment_code')
                            ->label('كود التسجيل')
                            ->disabled(),

                        TextInput::make('installment_number')
                            ->label('رقم القسط')
                            ->disabled(),

                        TextInput::make('amount')
                            ->label('المبلغ المستحق')
                            ->prefix('ج.م')
                            ->disabled(),

                        DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->disabled(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'paid' => 'مدفوع',
                                'overdue' => 'متأخر',
                                'partial' => 'مدفوع جزئياً',
                                'cancelled' => 'ملغي',
                            ])
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('تفاصيل الدفع (للقراءة فقط)')
                    ->description('تُسجل وتُحدث المدفوعات حصرياً من شاشة: مدفوعات الطلاب → إضافة دفعة')
                    ->schema([
                        TextInput::make('paid_amount')
                            ->label('المبلغ المدفوع')
                            ->numeric()
                            ->prefix('ج.م')
                            ->disabled(),

                        DatePicker::make('paid_date')
                            ->label('تاريخ الدفع')
                            ->disabled(),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment.student.student_code')
                    ->label('كود الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('enrollment.student.user.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('enrollment.student.status')
                    ->label('حالة الطالب')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'suspended',
                        'info' => 'graduated',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                        'graduated' => 'متخرج',
                        default => $state,
                    }),

                TextColumn::make('enrollment.groups.course.name')
                    ->label('الكورس')
                    ->formatStateUsing(function ($record) {
                        return $record->enrollment->groups->pluck('course.name')->join(', ');
                    })
                    ->limit(30),

                TextColumn::make('installment_number')
                    ->label('رقم القسط')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('EGP')
                    ->sortable()
                    ->color('danger')
                    ->weight('bold'),

                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('danger')
                    ->weight('bold'),

                TextColumn::make('days_overdue')
                    ->label('أيام التأخير')
                    ->getStateUsing(function (Installment $record): int {
                        return max(0, now()->startOfDay()->diffInDays($record->due_date, false) * -1);
                    })
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 30 => 'danger',
                        $state >= 15 => 'warning',
                        default => 'info',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('due_date', $direction === 'asc' ? 'desc' : 'asc');
                    }),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'danger' => 'overdue',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'secondary' => 'pending',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'overdue' => 'متأخر',
                        'partial' => 'مدفوع جزئياً',
                        'paid' => 'مدفوع',
                        'pending' => 'قيد الانتظار',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->getStateUsing(function (Installment $record): string {
                        return $record->getActualStatus();
                    }),
            ])
            ->filters([
                SelectFilter::make('enrollment.student_id')
                    ->label('الطالب')
                    ->relationship('enrollment.student.user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('student_status')
                    ->label('حالة الطالب')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                        'graduated' => 'متخرج',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && $data['value'] !== '') {
                            return $query->whereHas('enrollment.student', function ($q) use ($data) {
                                $q->where('status', $data['value']);
                            });
                        }
                        return $query;
                    })
                    ->default(null) // لا نفلتر افتراضياً - نعرض الكل
                    ->placeholder('الكل'),

                Filter::make('days_overdue')
                    ->form([
                        Select::make('days')
                            ->label('أيام التأخير')
                            ->options([
                                '7' => 'أكثر من 7 أيام',
                                '15' => 'أكثر من 15 يوم',
                                '30' => 'أكثر من 30 يوم',
                                '60' => 'أكثر من 60 يوم',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['days'] ?? null,
                            fn (Builder $query, $days): Builder => $query->where('due_date', '<=', now()->subDays($days))
                        );
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('عرض التفاصيل'),
            ])
            ->bulkActions([
                BulkAction::make('send_reminder')
                    ->label('إرسال تذكير')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            // إرسال إشعار للطالب وولي الأمر
                            // يمكن تطوير هذا لاحقاً
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('تم إرسال التذكيرات')
                            ->send();
                    }),
            ])
            ->emptyStateHeading('لا توجد أقساط متأخرة')
            ->emptyStateDescription('جميع الطلاب ملتزمون بمواعيد الأقساط')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOverdueInstallments::route('/'),
            'edit' => Pages\EditOverdueInstallment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::overdue()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::overdue()->count();
        
        return match (true) {
            $count >= 10 => 'danger',
            $count >= 5 => 'warning',
            default => 'info',
        };
    }
}
