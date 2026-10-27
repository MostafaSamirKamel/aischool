<?php

namespace App\Filament\Admin\Resources\Enrollments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\HtmlString;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات التسجيل')
                    ->schema([
                        Select::make('student_id')
                            ->label(__('filament/admin/enrollment_resource.student_id'))
                            ->relationship('student', 'student_code', fn($query) => $query->where('status', 'active')->when(auth()->user()->tenant_id, function ($q) {
                                return $q->where('tenant_id', auth()->user()->tenant_id); }))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name)
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $user = auth()->user();
                                $query = \App\Models\Student::where('status', 'active');

                                // فلترة الطلاب حسب tenant_id المستخدم الحالي
                                if ($user->tenant_id) {
                                    $query->where('tenant_id', $user->tenant_id);
                                }

                                return $query->where(function ($q) use ($search) {
                                    $q->where('student_code', 'like', "%{$search}%")
                                        ->orWhereHas('user', function ($sq) use ($search) {
                                            $sq->where('name', 'like', "%{$search}%");
                                            });
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->required()
                            ->preload()
                            ->columnSpan(1),

                        Select::make('courses')
                            ->label('الكورسات')
                            ->multiple()
                            ->options(function (callable $get) {
                                $user = auth()->user();
                                $selectedCourses = $get('courses') ?? [];

                                $query = \App\Models\Course::where('status', 'active');

                                // فلترة الكورسات حسب tenant_id المستخدم الحالي
                                if ($user->tenant_id) {
                                    $query->where('tenant_id', $user->tenant_id);
                                }

                                $activeCourses = $query->get();

                                // إضافة الكورسات المختارة سابقاً حتى لو كانت غير نشطة (للتعديل)
                                if (!empty($selectedCourses)) {
                                    $selectedCourseIds = array_filter($selectedCourses);
                                    if (!empty($selectedCourseIds)) {
                                        $selectedCourseModels = \App\Models\Course::whereIn('id', $selectedCourseIds)
                                            ->when($user->tenant_id, function ($q) use ($user) {
                                                return $q->where('tenant_id', $user->tenant_id);
                                            })
                                            ->get();
                                        $activeCourses = $activeCourses->merge($selectedCourseModels)->unique('id');
                                    }
                                }

                                return $activeCourses->mapWithKeys(function ($course) {
                                    return [$course->id => "{$course->name} - {$course->price} ج.م"];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->rule('array')
                            ->rule('min:1')
                            ->exists('courses', 'id')
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (is_array($state) && !empty($state)) {
                                    $user = auth()->user();
                                    $query = \App\Models\Course::whereIn('id', $state);

                                    // فلترة الكورسات حسب tenant_id المستخدم الحالي
                                    if ($user->tenant_id) {
                                        $query->where('tenant_id', $user->tenant_id);
                                    }

                                    $totalPrice = $query->sum('price');
                                    $set('total_amount', $totalPrice);

                                    // Update remaining balance
                                    $discount = floatval($get('discount') ?? 0);
                                    $paid = floatval($get('paid_amount') ?? 0);
                                    $set('remaining', $totalPrice - $discount - $paid);
                                }
                            })
                            ->helperText('اختر الكورسات - يمكنك تحديد المجموعة لكل كورس بعد الحفظ')
                            ->columnSpan(1),

                        Select::make('group_id')
                            ->label('المجموعة الدراسية')
                            ->options(function (Get $get, ?\App\Models\Enrollment $record) {
                                $tenantId = $record?->tenant_id ?? auth()->user()?->tenant_id;
                                $selectedCourses = $get('courses') ?? [];
                                if (empty($selectedCourses) && $record) {
                                    $selectedCourses = $record->enrollmentCourses()->pluck('course_id')->toArray();
                                }

                                $query = \App\Models\Group::query()
                                    ->where('status', 'active')
                                    ->with(['course', 'instructor.user']);

                                if ($tenantId) {
                                    $query->where('tenant_id', $tenantId);
                                }

                                if (!empty($selectedCourses)) {
                                    $query->whereIn('course_id', $selectedCourses);
                                }

                                $groups = $query->get();

                                if ($record && $record->group_id && !$groups->contains('id', $record->group_id)) {
                                    $currentGroup = \App\Models\Group::with(['course', 'instructor.user'])->find($record->group_id);
                                    if ($currentGroup) {
                                        $groups->prepend($currentGroup);
                                    }
                                }

                                return $groups->mapWithKeys(function ($g) {
                                    $instructor = $g->instructor?->user?->name ? " - أ/ " . $g->instructor->user->name : '';
                                    $schedule = '';
                                    if (is_array($g->schedule) && !empty($g->schedule)) {
                                        $days = collect($g->schedule)->pluck('day')->take(2)->join(', ');
                                        $schedule = " ({$days})";
                                    }
                                    $capacity = " [{$g->capacity}/{$g->max_students} طالب]";
                                    $label = "{$g->name} - {$g->course?->name}{$instructor}{$capacity}{$schedule}";
                                    return [$g->id => $label];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(?\App\Models\Enrollment $record) => $record && !auth()->user()?->can('transfer_student_group'))
                            ->helperText(function (?\App\Models\Enrollment $record) {
                                if ($record && !auth()->user()?->can('transfer_student_group')) {
                                    return 'ليس لديك صلاحية نقل الطالب إلى مجموعة أخرى (transfer_student_group)';
                                }
                                return 'اختر المجموعة الدراسية المؤهلة بنفس الفرع والمقرر';
                            })
                            ->afterStateUpdated(function ($state, callable $set, Get $get, ?\App\Models\Enrollment $record) {
                                if (!$record || !$state) {
                                    $set('has_progress_difference', false);
                                    $set('progress_difference_message', null);
                                    return;
                                }

                                $currentGroupId = $record->group_id 
                                    ?? $record->enrollmentCourses->first()?->group_id
                                    ?? $record->groups->first()?->id;

                                if ((int) $state === (int) $currentGroupId) {
                                    $set('has_progress_difference', false);
                                    $set('progress_difference_message', null);
                                    return;
                                }

                                $targetGroup = \App\Models\Group::find($state);
                                if ($targetGroup) {
                                    $service = app(\App\Services\Enrollment\StudentGroupTransferService::class);
                                    $diff = $service->getAcademicProgressDifference($record, $targetGroup);
                                    $set('has_progress_difference', $diff['has_difference']);
                                    $set('progress_difference_message', $diff['warning_message']);
                                }
                            })
                            ->columnSpan(1),

                        Placeholder::make('progress_difference_warning')
                            ->label('')
                            ->content(function (Get $get) {
                                $msg = $get('progress_difference_message');
                                if (!$msg) return '';
                                return new HtmlString('<div class="p-3 text-sm text-amber-800 rounded-lg bg-amber-50 dark:bg-gray-800 dark:text-amber-300 border border-amber-300" role="alert"><span class="font-bold">⚠️ تنبيه: </span>' . e($msg) . '</div>');
                            })
                            ->visible(fn(Get $get) => (bool) $get('has_progress_difference'))
                            ->columnSpanFull(),

                        Checkbox::make('confirm_transfer_progress')
                            ->label('أؤكد الاطلاع على فرق التقدم الأكاديمي والمتابعة في تأكيد نقل الطالب للمجموعة الجديدة')
                            ->visible(fn(Get $get) => (bool) $get('has_progress_difference'))
                            ->columnSpanFull(),

                        TextInput::make('transfer_reason')
                            ->label('سبب النقل (اختياري)')
                            ->placeholder('أدخل سبب نقل الطالب إلى هذه المجموعة إن وجد')
                            ->visible(function (Get $get, ?\App\Models\Enrollment $record) {
                                if (!$record) return false;
                                $currentGroupId = $record->group_id 
                                    ?? $record->enrollmentCourses->first()?->group_id
                                    ?? $record->groups->first()?->id;
                                return $get('group_id') && (int) $get('group_id') !== (int) $currentGroupId;
                            })
                            ->columnSpanFull(),

                        Select::make('track_id')
                            ->label('المسار (اختياري - للتصنيف فقط)')
                            ->relationship('track', 'name', fn($query) => $query->where('status', 'active')->when(auth()->user()->tenant_id, function ($q) {
                                return $q->where('tenant_id', auth()->user()->tenant_id); }))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('المسار للتصنيف فقط')
                            ->columnSpan(1),

                        TextInput::make('enrollment_code')
                            ->label(__('filament/admin/enrollment_resource.enrollment_code'))
                            ->required()
                            ->unique('enrollments', 'enrollment_code', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->id) : $rule;
                            })
                            ->default(fn () => \App\Models\Enrollment::generateEnrollmentCode())
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                            
                        DatePicker::make('enrollment_date')
                            ->label(__('filament/admin/enrollment_resource.enrollment_date'))
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        Select::make('status')
                            ->label(__('filament/admin/enrollment_resource.status'))
                            ->options(['pending' => __('filament/admin/enrollment_resource.status.pending'), 'active' => __('filament/admin/enrollment_resource.status.active'), 'completed' => __('filament/admin/enrollment_resource.status.completed'), 'cancelled' => __('filament/admin/enrollment_resource.status.cancelled'), 'suspended' => __('filament/admin/enrollment_resource.status.suspended')])
                            ->default(__('filament/admin/enrollment_resource.status_default'))
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('تفاصيل الدفع وخطة الدفع')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('إجمالي قيمة الكورسات المختارة')
                            ->required()
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('يتم حسابه تلقائيًا من الكورسات المختارة')
                            ->columnSpan(1),
                            
                        TextInput::make('discount')
                            ->label(__('filament/admin/enrollment_resource.discount'))
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $total = floatval($get('total_amount') ?? 0);
                                $discount = floatval($state ?? 0);
                                $paid = floatval($get('paid_amount') ?? 0);
                                $set('remaining', $total - $discount - $paid);

                                // إعادة حساب الأقساط إذا كانت موجودة
                                $installmentCount = intval($get('installments_count') ?? 0);
                                if ($installmentCount >= 2 && $get('payment_plan') === 'installments') {
                                    $installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
                                        $total,
                                        $discount,
                                        $paid,
                                        $installmentCount
                                    );
                                    $set('installments', $installments);
                                }
                            })
                            ->columnSpan(1),

                        Select::make('vault_id')
                            ->label('الخزنة المستلمة')
                            ->relationship('vault', 'name', fn($query) => $query->where('is_active', true)->when(auth()->user()->tenant_id, function ($q) {
                                return $q->where('tenant_id', auth()->user()->tenant_id); }))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getDisplayNameWithBalance())
                            ->searchable()
                            ->preload()
                            ->required(fn(callable $get) => floatval($get('paid_amount') ?? 0) > 0)
                            ->helperText('إلزامي عند سداد دفعة مقدمة (> 0 ج.م). غير مطلوب للتسجيل بدون دفع.')
                            ->columnSpan(1),

                        TextInput::make('paid_amount')
                            ->label('المدفوع')
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $total = floatval($get('total_amount') ?? 0);
                                $discount = floatval($get('discount') ?? 0);
                                $paid = floatval($state ?? 0);
                                $set('remaining', $total - $discount - $paid);

                                // إعادة حساب الأقساط إذا كانت موجودة
                                $installmentCount = intval($get('installments_count') ?? 0);
                                if ($installmentCount >= 2 && $get('payment_plan') === 'installments') {
                                    $installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
                                        $total,
                                        $discount,
                                        $paid,
                                        $installmentCount
                                    );
                                    $set('installments', $installments);
                                }
                            })
                            ->columnSpan(1),
                            
                        TextInput::make('remaining')
                            ->label('المتبقي')
                            ->numeric()
                            ->prefix('ج.م')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function (Get $get) {
                                $total = floatval($get('total_amount') ?? 0);
                                $discount = floatval($get('discount') ?? 0);
                                $paid = floatval($get('paid_amount') ?? 0);
                                return $total - $discount - $paid;
                            })
                            ->afterStateHydrated(function ($component, $state, Get $get) {
                                $total = floatval($get('total_amount') ?? 0);
                                $discount = floatval($get('discount') ?? 0);
                                $paid = floatval($get('paid_amount') ?? 0);
                                $component->state($total - $discount - $paid);
                            })
                            ->columnSpan(1),

                        RichEditor::make('discount_reason')
                            ->label(__('filament/admin/enrollment_resource.discount_reason'))
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => floatval($get('discount') ?? 0) > 0),

                        Select::make('payment_plan')
                            ->label(__('filament/admin/enrollment_resource.payment_plan'))
                            ->options(['full' => __('filament/admin/enrollment_resource.payment_plan.full'), 'installments' => __('filament/admin/enrollment_resource.payment_plan.installments')])
                            ->default(__('filament/admin/enrollment_resource.payment_plan_default'))
                            ->required()
                            ->reactive()
                            ->columnSpan(1),
                            
                        TextInput::make('installments_count')
                            ->label(__('filament/admin/enrollment_resource.installments_count'))
                            ->numeric()
                            ->minValue(2)
                            ->maxValue(12)
                            ->visible(fn (Get $get) => $get('payment_plan') === 'installments')
                            ->required(fn(Get $get) => $get('payment_plan') === 'installments')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state || $state < 2) {
                                    return;
                                }

                                // استخدام الخدمة المتخصصة لحساب الأقساط
                                $total = floatval($get('total_amount') ?? 0);
                                $discount = floatval($get('discount') ?? 0);
                                $paid = floatval($get('paid_amount') ?? 0);

                                // حساب الأقساط باستخدام الخدمة
                                $installments = \App\Services\Enrollment\InstallmentCalculationService::calculateForForm(
                                    $total,
                                    $discount,
                                    $paid,
                                    $state
                                );

                                $set('installments', $installments);
                            })
                            ->columnSpan(1),

                        Repeater::make('installments')
                            ->label('تفاصيل الأقساط')
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addActionLabel('إضافة قسط')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Auto-number installments sequentially
                                if (is_array($state)) {
                                    $counter = 1;
                                    foreach ($state as $key => $item) {
                                        $state[$key]['installment_number'] = $counter;
                                        $counter++;
                                    }
                                    $set('installments', $state);
                                }
                            })
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('installment_number')
                                            ->label('رقم القسط')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),

                                        TextInput::make('amount')
                                            ->label('المبلغ')
                                            ->numeric()
                                            ->required()
                                            ->prefix('ج.م')
                                            ->minValue(0)
                                            ->columnSpan(1),

                                        DatePicker::make('due_date')
                                            ->label('تاريخ الاستحقاق')
                                            ->required()
                                            ->minDate(now())
                                            ->columnSpan(1),

                                        Select::make('status')
                                            ->label('الحالة')
                                            ->options([
                                                'pending' => 'قيد الانتظار',
                                                'paid' => 'مدفوع',
                                                'overdue' => 'متأخر',
                                                'partial' => 'مدفوع جزئياً',
                                                'cancelled' => 'ملغي',
                                            ])
                                            ->default('pending')
                                            ->required()
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('paid_amount')
                                            ->label('المبلغ المدفوع')
                                            ->numeric()
                                            ->prefix('ج.م')
                                            ->minValue(0)
                                            ->default(0)
                                            ->columnSpan(1),

                                        DatePicker::make('paid_date')
                                            ->label('تاريخ الدفع')
                                            ->columnSpan(1),
                                    ]),

                                TextInput::make('notes')
                                    ->label('ملاحظات')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['installment_number'])
                                ? "القسط #{$state['installment_number']} - " . ($state['amount'] ?? '0') . " ج.م"
                                : null
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $get): array {
                                // Auto-generate installment number if not set
                                if (!isset($data['installment_number']) || empty($data['installment_number'])) {
                                    $existingCount = count($get('installments') ?? []);
                                    $data['installment_number'] = $existingCount;
                                }
                                return $data;
                            })
                            ->visible(fn(Get $get) => $get('payment_plan') === 'installments')
                            ->columnSpanFull()
                            ->minItems(fn(Get $get) => $get('payment_plan') === 'installments' ? ($get('installments_count') ?? 0) : 0)
                            ->maxItems(fn(Get $get) => $get('installments_count') ?? 12),

                        RichEditor::make('notes')
                            ->label(__('filament/admin/enrollment_resource.notes'))
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->description('الإجمالي = مجموع أسعار الكورسات المختارة')
                    ->columnSpanFull(),
            ]);
    }
}
