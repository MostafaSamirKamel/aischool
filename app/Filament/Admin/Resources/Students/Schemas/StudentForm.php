<?php

namespace App\Filament\Admin\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Services\NationalIdParser;
use App\Models\User;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الحساب')
                    ->description('سيتم إنشاء حساب مستخدم تلقائياً للطالب')
                    ->schema([
                        TextInput::make('user_name')
                            ->label(__('filament/admin/student_resource.user_name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل اسم الطالب الكامل')
                            ->columnSpan(1),

                        TextInput::make('user_username')
                            ->label('اسم المستخدم')
                            ->unique('users', 'username', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->user_id, 'id') : $rule;
                            })
                            ->alphaNum()
                            ->helperText('يمكن استخدامه لتسجيل الدخول (اختياري)')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('user_email')
                            ->label(__('filament/admin/student_resource.user_email'))
                            ->email()
                            ->required()
                            ->unique('users', 'email', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->user_id, 'id') : $rule;
                            })
                            ->maxLength(255)
                            ->placeholder('example@email.com')
                            ->columnSpan(1),

                        TextInput::make('user_password')
                            ->label(__('filament/admin/student_resource.user_password'))
                            ->password()
                            ->required(fn($context) => $context === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->minLength(8)
                            ->placeholder('على الأقل 8 أحرف')
                            ->helperText('اتركه فارغاً إذا كنت لا تريد تغيير كلمة المرور')
                            ->columnSpan(1),

                        TextInput::make('student_code')
                            ->label(__('filament/admin/student_resource.student_code'))
                            ->required()
                            ->unique('students', 'student_code', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->id) : $rule;
                            })
                            ->default(fn() => \App\Models\Student::generateStudentCode())
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('البيانات الشخصية')
                    ->schema([
                        TextInput::make('national_id')
                            ->label(__('filament/admin/student_resource.national_id'))
                            ->maxLength(14)
                            ->unique('students', 'national_id', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->id) : $rule;
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!empty($state) && strlen($state) === 14) {
                                    $parsed = NationalIdParser::parse($state);
                                    if ($parsed) {
                                        $set('birth_date', $parsed['birth_date']);
                                        $set('gender', $parsed['gender']);
                                        $set('city', $parsed['governorate_name']);

                                        // حساب العمر
                                        if ($parsed['birth_date']) {
                                            $birthDate = \Carbon\Carbon::parse($parsed['birth_date']);
                                            $now = now();
                                            $diff = $birthDate->diff($now);
                                            $age = sprintf('%d سنة و %d شهر و %d يوم', $diff->y, $diff->m, $diff->d);
                                            $set('age_display', $age);
                                        }
                                    }
                                }
                            })
                            ->helperText('سيتم استخراج تاريخ الميلاد والجنس والمحافظة تلقائياً عند الخروج من الحقل')
                            ->columnSpanFull(),

                        DatePicker::make('birth_date')
                            ->label(__('filament/admin/student_resource.birth_date'))
                            ->maxDate(now())
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state, $record) {
                                if ($state) {
                                    $birthDate = \Carbon\Carbon::parse($state);
                                    $now = now();
                                    $diff = $birthDate->diff($now);
                                    $age = sprintf('%d سنة و %d شهر و %d يوم', $diff->y, $diff->m, $diff->d);
                                    $set('age_display', $age);
                                }
                            }),

                        TextInput::make('age_display')
                            ->label('العمر')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function ($record) {
                                return $record?->age;
                            })
                            ->placeholder('سيتم حسابه تلقائياً'),

                        Select::make('gender')
                            ->label(__('filament/admin/student_resource.gender'))
                            ->options(['male' => __('filament/admin/student_resource.gender.male'), 'female' => __('filament/admin/student_resource.gender.female')])
                            ->required(),

                        TextInput::make('phone')
                            ->label(__('filament/admin/student_resource.phone'))
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('phone_2')
                            ->label(__('filament/admin/student_resource.phone_2'))
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(3),

                Section::make('العنوان')
                    ->schema([
                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name_ar')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(12) // محافظة البحيرة
                            ->createOptionForm([
                                TextInput::make('name_ar')
                                    ->label('اسم المحافظة بالعربية')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_en')
                                    ->label('اسم المحافظة بالإنجليزية')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $governorate = \App\Models\Governorate::create($data);
                                return $governorate->id;
                            })
                            ->afterStateUpdated(function (Set $set) {
                                $set('district_id', null);
                            }),

                        Select::make('district_id')
                            ->label('المركز / القسم')
                            ->relationship(
                                'district',
                                'name_ar',
                                fn($query, Get $get) => $query->where('governorate_id', $get('governorate_id') ?? 12)
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn(Get $get) => filled($get('governorate_id')))
                            ->createOptionForm([
                                TextInput::make('name_ar')
                                    ->label('اسم المركز/القسم بالعربية')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_en')
                                    ->label('اسم المركز/القسم بالإنجليزية')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $data['governorate_id'] = $get('governorate_id');
                                $district = \App\Models\District::create($data);
                                return $district->id;
                            }),

                        RichEditor::make('detailed_address')
                            ->label('العنوان التفصيلي')
                            ->columnSpanFull()
                           

                    ])
                    ->columns(2),

                Section::make('المعلومات التعليمية')
                    ->schema([
                        TextInput::make('school_name')
                            ->label(__('filament/admin/student_resource.school_name'))
                            ->maxLength(255),

                        Select::make('education_level')
                            ->label(__('filament/admin/student_resource.education_level'))
                            // ->native(false)
                            // ->searchable()
                            ->options(fn() => [
                                'ابتدائية' => 'ابتدائية',
                                'إعدادية' => 'إعدادية',
                                'ثانوية عامة' => 'ثانوية عامة',
                                'ثانوية أزهرية' => 'ثانوية أزهرية',
                                'دبلوم فني' => 'دبلوم فني',
                                'دبلوم تجاري' => 'دبلوم تجاري',
                                'دبلوم صناعي' => 'دبلوم صناعي',
                                'جامعي' => 'جامعي',
                                'دراسات عليا' => 'دراسات عليا',
                            ])
                            ->placeholder('اختر المستوى التعليمي'),

                        Select::make('status')
                            ->label(__('filament/admin/student_resource.status'))
                            ->options(['active' => __('filament/admin/student_resource.status.active'), 'inactive' => __('filament/admin/student_resource.status.inactive'), 'suspended' => __('filament/admin/student_resource.status.suspended'), 'graduated' => __('filament/admin/student_resource.status.graduated')])
                            ->default(__('filament/admin/student_resource.status_default'))
                            // ->native(false)
                            // ->searchable()
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('ملاحظات طبية')
                    ->schema([
                        RichEditor::make('medical_notes')
                            ->label(__('filament/admin/student_resource.medical_notes'))
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                Section::make('بيانات ولي الأمر')
                    ->description('يمكنك اختيار ولي أمر موجود أو إضافة ولي أمر جديد')
                    ->schema([
                        Repeater::make('parents')
                            ->schema([
                                Toggle::make('is_new_parent')
                                    ->label('إضافة ولي أمر جديد')
                                    ->reactive()
                                    ->default(false)
                                    ->columnSpanFull(),

                                Select::make('parent_user_id')
                                    ->label('اختر ولي أمر موجود')
                                    ->options(function () {
                                        return \App\Models\User::whereHas('roles', function ($q) {
                                            $q->where('name', 'parent');
                                        })
                                            ->where('tenant_id', auth()->user()->tenant_id)
                                            ->get()
                                            ->mapWithKeys(fn($user) => [
                                                $user->id => $user->name . ' - ' . $user->email
                                            ]);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn(Get $get) => !$get('is_new_parent'))
                                    ->required(fn(Get $get) => !$get('is_new_parent'))
                                    ->columnSpan(2),

                                // حقول ولي أمر جديد
                                TextInput::make('new_parent_name')
                                    ->label('اسم ولي الأمر')
                                    ->visible(fn(Get $get) => $get('is_new_parent'))
                                    ->required(fn(Get $get) => $get('is_new_parent'))
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('new_parent_username')
                                    ->label('اسم المستخدم')
                                    ->visible(fn(Get $get) => $get('is_new_parent'))
                                    ->unique('users', 'username')
                                    ->alphaNum()
                                    ->helperText('يمكن استخدامه لتسجيل الدخول (اختياري)')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('new_parent_email')
                                    ->label('البريد الإلكتروني')
                                    ->email()
                                    ->visible(fn(Get $get) => $get('is_new_parent'))
                                    ->required(fn(Get $get) => $get('is_new_parent'))
                                    ->unique('users', 'email')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('new_parent_password')
                                    ->label('كلمة المرور')
                                    ->password()
                                    ->visible(fn(Get $get) => $get('is_new_parent'))
                                    ->required(fn(Get $get) => $get('is_new_parent'))
                                    ->minLength(8)
                                    ->columnSpan(1),

                                TextInput::make('new_parent_phone')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->visible(fn(Get $get) => $get('is_new_parent'))
                                    ->maxLength(20)
                                    ->columnSpan(1),

                                Select::make('relationship_type')
                                    ->label('صلة القرابة')
                                    ->options([
                                        'father' => 'أب',
                                        'mother' => 'أم',
                                        'guardian' => 'ولي أمر',
                                        'brother' => 'أخ',
                                        'sister' => 'أخت',
                                        'uncle' => 'عم/خال',
                                        'aunt' => 'عمة/خالة',
                                        'other' => 'أخرى',
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                Toggle::make('is_primary_guardian')
                                    ->label('ولي الأمر الرئيسي')
                                    ->default(false)
                                    ->columnSpan(1),

                                Toggle::make('has_pickup_permission')
                                    ->label('لديه صلاحية الاستلام')
                                    ->default(true)
                                    ->columnSpan(1),

                                Toggle::make('can_view_grades')
                                    ->label('يمكنه رؤية الدرجات')
                                    ->default(true)
                                    ->columnSpan(1),

                                Toggle::make('can_receive_notifications')
                                    ->label('يستقبل الإشعارات')
                                    ->default(true)
                                    ->columnSpan(1),

                                RichEditor::make('notes')
                                    ->label('ملاحظات')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('➕ إضافة ولي أمر آخر')
                            ->collapsible()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                $state['new_parent_name'] ??
                                (isset($state['parent_user_id']) && $state['parent_user_id'] ? User::find($state['parent_user_id'])?->name : null) ??
                                'ولي أمر جديد'
                            ),
                    ]),
            ]);
    }
}
