<?php

namespace App\Filament\Admin\Resources\Payments\Schemas;

use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Vault;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الطالب والتسجيل')
                    ->description('ابحث عن الطالب بالاسم، الكود، هاتف الطالب، أو هاتف ولي الأمر')
                    ->schema([
                        TextInput::make('barcode')
                            ->label('باركود / كود الطالب')
                            ->placeholder('امسح الباركود أو اكتب الكود ثم اضغط Enter')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                if (!$state) {
                                    return;
                                }

                                $tenantId = auth()->user()?->tenant_id;
                                $student = Student::query()
                                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->where('student_code', trim($state))
                                    ->first();

                                if (!$student) {
                                    return;
                                }

                                $set('student_id', $student->id);
                                self::hydrateEnrollmentData($student->id, $set, $get, $livewire);
                            })
                            ->columnSpan(1),

                        Select::make('student_id')
                            ->label('الطالب')
                            ->placeholder('ابحث بالاسم، كود الطالب، هاتف الطالب، أو هاتف ولي الأمر')
                            ->options(function () {
                                $tenantId = auth()->user()?->tenant_id;
                                return Student::query()
                                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->where('status', 'active')
                                    ->with(['user', 'parents'])
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($student) {
                                        $name = $student->user?->name ?? 'طالب';
                                        $code = $student->student_code ?? '---';
                                        $phone = $student->phone ?? $student->user?->phone ?? '';
                                        $label = $name . ' (' . $code . ')' . ($phone ? ' - ' . $phone : '');
                                        return [$student->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                $tenantId = auth()->user()?->tenant_id;
                                return Student::query()
                                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->where('status', 'active')
                                    ->where(function ($q) use ($search) {
                                        $q->where('student_code', 'like', "%{$search}%")
                                            ->orWhere('phone', 'like', "%{$search}%")
                                            ->orWhere('phone_2', 'like', "%{$search}%")
                                            ->orWhereHas('user', function ($uq) use ($search) {
                                                $uq->where('name', 'like', "%{$search}%")
                                                    ->orWhere('phone', 'like', "%{$search}%");
                                            })
                                            ->orWhereHas('parents', function ($pq) use ($search) {
                                                $pq->where('phone', 'like', "%{$search}%")
                                                    ->orWhere('name', 'like', "%{$search}%");
                                            });
                                    })
                                    ->with(['user', 'parents'])
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($student) {
                                        $name = $student->user?->name ?? 'طالب';
                                        $code = $student->student_code ?? '---';
                                        $phone = $student->phone ?? $student->user?->phone ?? '';
                                        $parentPhone = $student->parents->first()?->phone;
                                        $extra = $phone ?: ($parentPhone ? 'ولي الأمر: ' . $parentPhone : '');
                                        $label = $name . ' (' . $code . ')' . ($extra ? ' - ' . $extra : '');
                                        return [$student->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $student = Student::with(['user', 'parents'])->find($value);
                                if (!$student) return null;
                                $name = $student->user?->name ?? 'طالب';
                                $code = $student->student_code ?? '---';
                                $phone = $student->phone ?? $student->user?->phone ?? '';
                                return $name . ' (' . $code . ')' . ($phone ? ' - ' . $phone : '');
                            })
                            ->searchable()
                            ->required()
                            ->preload()
                            ->live()
                            ->dehydrated(false)
                            ->disabled(fn($context) => $context === 'edit')
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                self::hydrateEnrollmentData($state, $set, $get, $livewire);
                            })
                            ->columnSpan(2),

                        Select::make('enrollment_id')
                            ->label('التسجيل / المسار الدراسي المستهدف')
                            ->options(function (callable $get) {
                                $studentId = $get('student_id');
                                if (!$studentId) return [];

                                $tenantId = auth()->user()?->tenant_id;
                                return Enrollment::query()
                                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->where('student_id', $studentId)
                                    ->where('status', 'active')
                                    ->with(['track', 'enrollmentCourses.course'])
                                    ->get()
                                    ->mapWithKeys(function ($enr) {
                                        $track = $enr->track?->name ?? 'كورس منفرد';
                                        $courses = $enr->enrollmentCourses->map(fn($ec) => $ec->course?->name)->filter()->join(', ');
                                        $rem = number_format($enr->getRemainingBalance(), 2);
                                        $label = "{$enr->enrollment_code} - {$track}" . ($courses ? " ({$courses})" : "") . " [المتبقي: {$rem} ج.م]";
                                        return [$enr->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                self::hydrateEnrollmentData($get('student_id'), $set, $get, $livewire, $state);
                            })
                            ->columnSpan(2),

                        TextInput::make('track_name')
                            ->label('المسار الأكاديمي')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('سيظهر بعد اختيار الطالب')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('الموقف المالي الشامل للطالب')
                    ->description('العقود، الخصومات المعتمدة، المدفوعات السابقة، والرصيد المتبقي الإجمالي')
                    ->schema([
                        TextInput::make('courses_total')
                            ->label('إجمالي قيمة العقود/الكورسات')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('ج.م')
                            ->placeholder('0.00')
                            ->columnSpan(1),

                        TextInput::make('total_discount')
                            ->label('🏷️ الخصومات المعتمدة')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('ج.م')
                            ->placeholder('0.00')
                            ->extraAttributes(['class' => 'font-bold text-blue-600'])
                            ->columnSpan(1),

                        TextInput::make('paid_upfront')
                            ->label('💰 المسدد مقدماً (عند التسجيل)')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('ج.م')
                            ->placeholder('0.00')
                            ->columnSpan(1),

                        TextInput::make('other_payments')
                            ->label('📋 مدفوعات الأقساط السابقة')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('ج.م')
                            ->placeholder('0.00')
                            ->columnSpan(1),

                        TextInput::make('total_paid')
                            ->label('✅ إجمالي المسدد حتى الآن')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('ج.م')
                            ->placeholder('0.00')
                            ->extraAttributes(['class' => 'font-bold text-green-600'])
                            ->columnSpan(1),

                        TextInput::make('remaining_balance')
                            ->label('⚠️ المتبقي الإجمالي المطلوب')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('ج.م')
                            ->placeholder('0.00')
                            ->extraAttributes(['class' => 'font-extrabold text-lg text-red-600'])
                            ->columnSpan(1),
                    ])
                    ->columns(6)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('بيانات الدفعة الحالية (سند التحصيل)')
                    ->schema([
                        TextInput::make('payment_code')
                            ->label(__('filament/admin/payment_resource.payment_code'))
                            ->required()
                            ->unique('payments', 'payment_code', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->id) : $rule;
                            })
                            ->default(fn () => Payment::generatePaymentCode())
                            ->readOnly()
                            ->dehydrated()
                            ->columnSpan(1)
                            ->helperText('كود إيصال الدفعة ينشأ تلقائياً'),

                        TextInput::make('amount')
                            ->label('المبلغ المراد سداده اليوم *')
                            ->required()
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0.01)
                            ->live(debounce: 250)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updatePaymentPreview($set, $get);
                            })
                            ->helperText('سيتم توزيعه فوراً وفق الأولوية المالية والأقدمية على الأقساط المفتوحة')
                            ->columnSpan(1),

                        Select::make('vault_id')
                            ->label('الخزنة المستلمة *')
                            ->relationship(
                                'vault',
                                'name',
                                fn($query) => $query
                                    ->select('vaults.*')
                                    ->where('vaults.is_active', true)
                                    ->when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('vaults.tenant_id', $tid))
                                    ->distinct()
                                    ->orderBy('vaults.name')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getDisplayNameWithBalance())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updatePaymentPreview($set, $get);
                            })
                            ->helperText('الخزينة المعتمدة التي سيتم إيداع النقدية بها وتحديث رصيدها')
                            ->columnSpan(1),

                        DatePicker::make('payment_date')
                            ->label(__('filament/admin/payment_resource.payment_date'))
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        Select::make('status')
                            ->label(__('filament/admin/payment_resource.status'))
                            ->options([
                                'completed' => '✅ مكتمل (ترحيل فوري للخزينة والأقساط)',
                                'pending' => '⏳ قيد الانتظار',
                                'failed' => '❌ فاشل',
                                'refunded' => '↩️ مسترد',
                            ])
                            ->default('completed')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('transaction_reference')
                            ->label(__('filament/admin/payment_resource.transaction_reference'))
                            ->placeholder('رقم الشيك / مرجع التحويل / رقم الحوالة')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Select::make('received_by')
                            ->label(__('filament/admin/payment_resource.received_by'))
                            ->relationship('receivedBy', 'name', fn($query) => $query->where('status', 'active'))
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->required()
                            ->columnSpan(1),

                        RichEditor::make('notes')
                            ->label(__('filament/admin/payment_resource.notes'))
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                // Live dynamic allocation preview
                ViewField::make('payment_preview_info')
                    ->label('')
                    ->view('filament.admin.components.payment-preview-card')
                    ->dehydrated(false)
                    ->visible(fn(callable $get): bool => floatval($get('amount')) > 0)
                    ->columnSpanFull(),

                // Grouped Installments Section
                ViewField::make('installments_info')
                    ->label('')
                    ->view('filament.admin.components.installments-info')
                    ->dehydrated(false)
                    ->visible(fn(callable $get): bool => !empty($get('installments_info')))
                    ->columnSpanFull(),

                // Student Previous Payment Receipts History
                ViewField::make('payment_history_info')
                    ->label('')
                    ->view('filament.admin.components.student-payments-history')
                    ->dehydrated(false)
                    ->visible(fn(callable $get): bool => !empty($get('payment_history_info')))
                    ->columnSpanFull(),

                // Courses and Sessions Information
                Section::make('الكورسات والمحاضرات المسجلة')
                    ->description('تفاصيل الكورسات والمجموعات والمحاضرات المجدولة')
                    ->schema([
                        ViewField::make('courses_info')
                            ->label('')
                            ->view('filament.admin.components.courses-sessions-info')
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(callable $get): bool => !empty($get('courses_info')))
                    ->collapsible(),
            ]);
    }

    /**
     * Calculate oldest-first allocation preview matching PaymentObserver logic.
     */
    public static function calculateAllocationPreview(array $installments, float $paymentAmount, float $currentBalance): array
    {
        $amountLeft = max(0, $paymentAmount);
        $affected = [];

        // Sort installments oldest first by due_date, then installment_number, then id
        usort($installments, function ($a, $b) {
            $dateA = $a['due_date'] ?? '9999-12-31';
            $dateB = $b['due_date'] ?? '9999-12-31';
            if ($dateA !== $dateB) {
                return strcmp($dateA, $dateB);
            }
            return ($a['installment_number'] ?? 0) <=> ($b['installment_number'] ?? 0);
        });

        foreach ($installments as $inst) {
            $remaining = floatval($inst['remaining'] ?? ($inst['amount'] - $inst['paid_amount']));
            if ($remaining <= 0.001) {
                continue;
            }

            if ($amountLeft <= 0) {
                break;
            }

            $allocated = min($amountLeft, $remaining);
            $newPaid = floatval($inst['paid_amount'] ?? 0) + $allocated;
            $newRemaining = max(0, $remaining - $allocated);
            $newStatus = ($newRemaining <= 0.001) ? 'paid' : 'partial';

            $affected[] = [
                'installment_number' => $inst['installment_number'],
                'due_date' => $inst['due_date'],
                'amount' => $inst['amount'],
                'current_paid' => $inst['paid_amount'],
                'allocated_amount' => $allocated,
                'new_remaining' => $newRemaining,
                'new_status' => $newStatus,
                'enrollment_code' => $inst['enrollment_code'] ?? '',
            ];

            $amountLeft -= $allocated;
        }

        $expectedBalance = max(0, $currentBalance - $paymentAmount);

        return [
            'payment_amount' => $paymentAmount,
            'current_balance' => $currentBalance,
            'expected_balance' => $expectedBalance,
            'affected_installments' => $affected,
            'unallocated_surplus' => max(0, $amountLeft),
        ];
    }

    /**
     * Recalculate dynamic preview state based on form inputs.
     */
    public static function updatePaymentPreview(callable $set, callable $get): void
    {
        $amount = floatval($get('amount') ?? 0);
        $currentBalance = floatval($get('remaining_balance') ?? 0);
        $allInstallments = $get('installments_info') ?? [];
        $vaultId = $get('vault_id');

        $vaultName = 'لم يتم تحديد الخزنة بعد';
        if ($vaultId) {
            $vault = Vault::find($vaultId);
            if ($vault) {
                $vaultName = $vault->getDisplayNameWithBalance();
            }
        }

        $preview = self::calculateAllocationPreview($allInstallments, $amount, $currentBalance);
        $preview['vault_name'] = $vaultName;

        $set('payment_preview_info', $preview);
    }

    /**
     * Hydrate all financial and academic records for the selected student.
     */
    public static function hydrateEnrollmentData(?int $studentId, callable $set, callable $get, $livewire, ?int $targetEnrollmentId = null): void
    {
        if (method_exists($livewire, 'dispatch')) {
            $livewire->dispatch('student-selected', studentId: $studentId);
        }

        $set('enrollment_id', null);
        $set('track_name', null);
        $set('courses_total', 0);
        $set('total_discount', 0);
        $set('total_paid', 0);
        $set('remaining_balance', 0);
        $set('courses_info', []);
        $set('installments_info', []);
        $set('payment_history_info', []);
        $set('payment_preview_info', []);
        $set('payment_plan', null);

        if (!$studentId) {
            return;
        }

        $tenantId = auth()->user()?->tenant_id;

        // Fetch active enrollments
        $enrollments = Enrollment::where('student_id', $studentId)
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->with([
                'track',
                'enrollmentCourses.course',
                'enrollmentCourses.group.instructor.user',
                'installments' => fn($q) => $q->where('status', '!=', 'cancelled')->orderBy('due_date', 'asc')->orderBy('installment_number', 'asc'),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($enrollments->isEmpty()) {
            return;
        }

        // Determine active target enrollment
        $activeEnrollment = null;
        if ($targetEnrollmentId) {
            $activeEnrollment = $enrollments->firstWhere('id', $targetEnrollmentId);
        }
        if (!$activeEnrollment) {
            $activeEnrollment = $enrollments->first();
        }

        $set('enrollment_id', $activeEnrollment->id);
        $set('track_name', $activeEnrollment->track?->name ?? 'كورس منفرد');
        $set('payment_plan', $activeEnrollment->payment_plan);

        // Financial Aggregations across active enrollments
        $totalCoursesPrice = 0;
        $totalDiscount = 0;
        $totalPaidUpfront = 0;
        $allCoursesInfo = [];
        $allInstallmentsInfo = [];

        foreach ($enrollments as $enrollment) {
            $coursesTotalPrice = $enrollment->enrollmentCourses->sum('course_price');
            $totalCoursesPrice += $coursesTotalPrice;
            $totalDiscount += floatval($enrollment->discount ?? 0);
            $totalPaidUpfront += $enrollment->getCalculatedUpfrontAmount();

            // Course and session details
            foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
                $course = $enrollmentCourse->course;
                if (!$course) continue;

                $group = $enrollmentCourse->group;
                $sessions = collect();
                if ($group) {
                    $sessions = \App\Models\SessionSchedule::where('course_id', $course->id)
                        ->where('group_id', $group->id)
                        ->orderBy('date', 'asc')
                        ->get();
                }

                $totalSessions = $enrollmentCourse->total_sessions ?? $sessions->count();
                $completedSessions = $enrollmentCourse->current_session ?? $sessions->where('status', 'completed')->count();

                $sessionsData = $sessions->map(function ($session) {
                    $dayName = $session->date ? Carbon::parse($session->date)->locale('ar')->translatedFormat('l') : '-';
                    return [
                        'date' => $session->date ? Carbon::parse($session->date)->format('Y-m-d') : '-',
                        'day' => $dayName,
                        'start_time' => $session->start_time ?? '-',
                        'end_time' => $session->end_time ?? '-',
                        'status' => match ($session->status) {
                            'scheduled' => 'مجدولة',
                            'ongoing' => 'جارية',
                            'completed' => 'منتهية',
                            'cancelled' => 'ملغاة',
                            default => $session->status
                        },
                    ];
                })->toArray();

                $allCoursesInfo[] = [
                    'enrollment_code' => $enrollment->enrollment_code,
                    'course_name' => $course->name,
                    'course_code' => 'C-' . str_pad($course->id, 4, '0', STR_PAD_LEFT),
                    'course_price' => $enrollmentCourse->course_price,
                    'total_sessions' => $totalSessions,
                    'completed_sessions' => $completedSessions,
                    'remaining_sessions' => max(0, $totalSessions - $completedSessions),
                    'sessions' => $sessionsData,
                ];
            }

            // Installment obligations
            foreach ($enrollment->installments as $installment) {
                $rem = floatval($installment->amount) - floatval($installment->paid_amount);
                $allInstallmentsInfo[] = [
                    'id' => $installment->id,
                    'enrollment_code' => $enrollment->enrollment_code,
                    'installment_number' => $installment->installment_number,
                    'amount' => floatval($installment->amount),
                    'due_date' => $installment->due_date ? Carbon::parse($installment->due_date)->format('Y-m-d') : '-',
                    'paid_amount' => floatval($installment->paid_amount),
                    'paid_date' => $installment->paid_date ? Carbon::parse($installment->paid_date)->format('Y-m-d') : '-',
                    'status' => match ($installment->status) {
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'overdue' => 'متأخر',
                        'partial' => 'مدفوع جزئي',
                        default => $installment->status
                    },
                    'remaining' => max(0, $rem),
                ];
            }
        }

        // Total payments made through installment allocations
        $enrollmentIds = $enrollments->pluck('id')->toArray();
        $totalPaidFromInstallments = floatval(
            Installment::whereIn('enrollment_id', $enrollmentIds)
                ->where('status', '!=', 'cancelled')
                ->sum('paid_amount')
        );

        $totalPaid = $totalPaidUpfront + $totalPaidFromInstallments;
        $remainingBalance = max(0, $totalCoursesPrice - $totalDiscount - $totalPaid);

        $set('courses_total', $totalCoursesPrice);
        $set('total_discount', $totalDiscount);
        $set('paid_upfront', $totalPaidUpfront);
        $set('other_payments', $totalPaidFromInstallments);
        $set('total_paid', $totalPaid);
        $set('remaining_balance', $remainingBalance);

        // Pre-fill amount with the current remaining balance or installment due
        if ($remainingBalance > 0) {
            $set('amount', round($remainingBalance, 2));
        }

        // Fetch official historical payment receipts
        $completedPayments = Payment::whereIn('enrollment_id', $enrollmentIds)
            ->with(['vault', 'receivedBy', 'enrollment'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $paymentsHistoryData = $completedPayments->map(function ($p) {
            return [
                'id' => $p->id,
                'payment_code' => $p->payment_code,
                'transaction_reference' => $p->transaction_reference,
                'payment_date' => $p->payment_date ? Carbon::parse($p->payment_date)->format('Y-m-d') : '-',
                'amount' => floatval($p->amount),
                'payment_method' => $p->payment_method ?? 'cash',
                'vault_name' => $p->vault?->name ?? 'غير محدد',
                'received_by' => $p->receivedBy?->name ?? 'غير محدد',
                'status' => $p->status,
                'enrollment_code' => $p->enrollment?->enrollment_code ?? '-',
            ];
        })->toArray();

        $set('installments_info', $allInstallmentsInfo);
        $set('courses_info', $allCoursesInfo);
        $set('payment_history_info', $paymentsHistoryData);

        // Compute preview
        self::updatePaymentPreview($set, $get);
    }
}
