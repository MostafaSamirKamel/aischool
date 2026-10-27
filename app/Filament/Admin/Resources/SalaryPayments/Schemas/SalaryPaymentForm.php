<?php

namespace App\Filament\Admin\Resources\SalaryPayments\Schemas;

use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Vault;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalaryPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الراتب')
                    ->schema([
                        Select::make('employee_type')
                            ->label('نوع الموظف')
                            ->options([
                                'instructor' => 'مهندس/مدرس',
                                'admin' => 'سكرتارية/إداري',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('instructor_id', null);
                                $set('admin_id', null);
                                $set('amount', 0);
                            })
                            ->columnSpan(1),

                        DatePicker::make('period_start')
                            ->label('بداية الفترة')
                            ->default(now()->startOfMonth())
                            ->required()
                            ->visible(fn($get) => $get('employee_type') === 'instructor')
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $instructorId = $get('instructor_id');
                                if ($instructorId) {
                                    self::recalculateInstructorAmount((int) $instructorId, $get, $set);
                                }
                            })
                            ->dehydrated()
                            ->columnSpan(1),

                        DatePicker::make('period_end')
                            ->label('نهاية الفترة')
                            ->default(now()->endOfMonth())
                            ->required()
                            ->visible(fn($get) => $get('employee_type') === 'instructor')
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $instructorId = $get('instructor_id');
                                if ($instructorId) {
                                    self::recalculateInstructorAmount((int) $instructorId, $get, $set);
                                }
                            })
                            ->dehydrated()
                            ->columnSpan(1),

                        Select::make('instructor_id')
                            ->label('المهندس/المدرس')
                            ->options(function () {
                                return Instructor::query()
                                    ->with('user')
                                    ->where('status', 'active')
                                    ->get()
                                    ->mapWithKeys(fn ($instructor) => [$instructor->id => $instructor->user?->name ?? 'غير محدد']);
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('employee_type') === 'instructor')
                            ->required(fn ($get) => $get('employee_type') === 'instructor')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    return;
                                }
                                self::recalculateInstructorAmount((int) $state, $get, $set, true);
                            })
                            ->columnSpan(1),

                        Select::make('admin_id')
                            ->label('السكرتارية/الإداري')
                            ->options(function () {
                                $query = Admin::query()->with('user');

                                $query->whereHas('user.roles', function ($q) {
                                    $q->where('name', 'secretary');
                                });

                                $admins = $query->get();

                                return $admins->mapWithKeys(fn ($admin) => [$admin->id => $admin->user?->name ?? 'غير محدد']);
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('employee_type') === 'admin')
                            ->required(fn ($get) => $get('employee_type') === 'admin')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    return;
                                }
                                $salary = Admin::find($state)?->salary ?? 0;
                                $set('amount', $salary);
                            })
                            ->columnSpan(1),

                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('bonus_percentage')
                            ->label('نسبة البونص %')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->live()
                            ->visible(fn ($get) => $get('employee_type') === 'instructor')
                            ->dehydrated(false)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $instructorId = $get('instructor_id');
                                if ($instructorId) {
                                    self::recalculateInstructorAmount((int) $instructorId, $get, $set);
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('bonus_amount_preview')
                            ->label('قيمة البونص')
                            ->numeric()
                            ->prefix('ج.م')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('employee_type') === 'instructor')
                            ->columnSpan(1),

                        DatePicker::make('payment_date')
                            ->label('تاريخ الصرف')
                            ->default(now())
                            ->columnSpan(1),

                        Select::make('vault_id')
                            ->label('الخزنة المصروف منها')
                            ->options(function () {
                                $user = auth()->user();
                                return Vault::query()
                                    ->where('is_active', true)
                                    ->when($user?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid))
                                    ->get()
                                    ->mapWithKeys(fn ($vault) => [$vault->id => $vault->getDisplayNameWithBalance()]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn($get) => $get('status') === 'paid')
                            ->rules([
                                fn ($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $status = $get('status');
                                    $amount = (float) ($get('amount') ?? 0);
                                    if ($status === 'paid' && $value && $amount > 0) {
                                        $vault = Vault::find($value);
                                        if ($vault && (float) $vault->getCurrentBalance() < $amount) {
                                            $fail('رصيد الخزنة المحددة غير كافٍ لصرف الراتب.');
                                        }
                                    }
                                },
                            ])
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'paid' => 'مدفوع',
                                'cancelled' => 'ملغي',
                            ])
                            ->default('pending')
                            ->required()
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),

                        Hidden::make('sessions_count')
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }

    protected static function recalculateInstructorAmount(int $instructorId, callable $get, callable $set, bool $notify = false): void
    {
        $instructor = Instructor::find($instructorId);
        if (!$instructor) {
            return;
        }

        $periodStart = $get('period_start') ?? now()->startOfMonth();
        $periodEnd = $get('period_end') ?? now()->endOfMonth();

        $sessionsCount = \App\Models\SessionSchedule::withoutGlobalScopes()
            ->where('instructor_id', $instructor->id)
            ->where('tenant_id', $instructor->tenant_id)
            ->where('status', 'completed')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->count();

        if ($sessionsCount === 0) {
            $sessionsCount = \App\Models\AttendanceSession::withoutGlobalScopes()
                ->whereHas('group', function ($q) use ($instructor) {
                    $q->where('instructor_id', $instructor->id);
                })
                ->where('tenant_id', $instructor->tenant_id)
                ->whereBetween('attendance_date', [$periodStart, $periodEnd])
                ->whereNull('salary_payment_id')
                ->count();
        }

        $hourlyRate = (float) ($instructor->hourly_rate ?? 0);
        $baseSalary = (float) ($instructor->base_salary ?? 0);
        $sessionsAmount = (float) $sessionsCount * $hourlyRate;
        $bonusPercentage = (float) ($get('bonus_percentage') ?? 0);
        $bonusAmount = $sessionsAmount * ($bonusPercentage / 100);
        $calculatedAmount = $baseSalary + $sessionsAmount + $bonusAmount;

        $set('amount', round($calculatedAmount, 2));
        $set('sessions_count', (int) $sessionsCount);
        $set('bonus_amount_preview', round($bonusAmount, 2));

        if ($notify) {
            \Filament\Notifications\Notification::make()
                ->title('تم حساب الأجر')
                ->body("السيشنات: {$sessionsCount} × {$hourlyRate} + أساسي {$baseSalary} + بونص {$bonusAmount} = {$calculatedAmount} ج.م")
                ->info()
                ->send();
        }
    }
}
