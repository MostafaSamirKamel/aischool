<?php

namespace App\Filament\Admin\Resources\StudentParents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use App\Services\NationalIdParser;

class StudentParentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الحساب')
                    ->description('سيتم إنشاء حساب مستخدم تلقائياً لولي الأمر')
                    ->schema([
                        TextInput::make('user_name')
                            ->label(__('filament/admin/student_parent_resource.user_name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل اسم ولي الأمر الكامل')
                            ->columnSpan(1),

                        TextInput::make('user_username')
                            ->label(__('filament/admin/student_parent_resource.user_username'))
                            ->unique('users', 'username', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record && $record->user_id ? $rule->ignore($record->user_id, 'id') : $rule;
                            })
                            ->alphaNum()
                            ->helperText('يمكن استخدامه لتسجيل الدخول (اختياري)')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('user_email')
                            ->label(__('filament/admin/student_parent_resource.user_email'))
                            ->email()
                            ->required()
                            ->unique('users', 'email', modifyRuleUsing: function ($rule, $get, $record) {
                                if ($record && $record->user_id) {
                                    return $rule->ignore($record->user_id);
                                }
                                return $rule;
                            })
                            ->maxLength(255)
                            ->placeholder('example@email.com')
                            ->columnSpan(1),

                        TextInput::make('user_password')
                            ->label(__('filament/admin/student_parent_resource.user_password'))
                            ->password()
                            ->required(fn($context) => $context === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->minLength(8)
                            ->placeholder('على الأقل 8 أحرف')
                            ->helperText('اتركه فارغاً إذا كنت لا تريد تغيير كلمة المرور')
                            ->columnSpan(1),

                        TextInput::make('user_phone')
                            ->label(__('filament/admin/student_parent_resource.user_phone'))
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('البيانات الشخصية')
                    ->schema([
                        TextInput::make('national_id')
                            ->label(__('filament/admin/student_parent_resource.national_id'))
                            ->maxLength(14)
                            ->unique('student_parents', 'national_id', modifyRuleUsing: function ($rule, $get, $record) {
                                return $record ? $rule->ignore($record->id) : $rule;
                            })
                            ->lazy()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!empty($state) && strlen($state) === 14) {
                                    $parsed = NationalIdParser::parse($state);
                                    if ($parsed) {
                                        $set('birth_date', $parsed['birth_date']);
                                        $set('gender', $parsed['gender']);
                                        $set('governorate', $parsed['governorate_name']);
                                    }
                                }
                            })
                            ->helperText('سيتم استخراج تاريخ الميلاد والجنس والمحافظة تلقائياً عند الخروج من الحقل')
                            ->columnSpanFull(),

                        DatePicker::make('birth_date')
                            ->label(__('filament/admin/student_parent_resource.birth_date'))
                            ->maxDate(now())
                            ->columnSpan(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $birthDate = \Carbon\Carbon::parse($state);
                                    $now = now();
                                    $diff = $birthDate->diff($now);
                                    $age = sprintf('%d سنة و %d شهر و %d يوم', $diff->y, $diff->m, $diff->d);
                                    $set('age_display', $age);
                                }
                            }),

                        TextInput::make('age_display')
                            ->label(__('filament/admin/student_parent_resource.age_display'))
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function ($record) {
                                return $record?->age;
                            })
                            ->placeholder('سيتم حسابه تلقائياً')
                            ->columnSpan(1),

                        Select::make('gender')
                            ->label(__('filament/admin/student_parent_resource.gender'))
                            ->options(['male' => __('filament/admin/student_parent_resource.gender.male'), 'female' => __('filament/admin/student_parent_resource.gender.female')])
                            ->columnSpan(1),

                        TextInput::make('governorate')
                            ->label(__('filament/admin/student_parent_resource.governorate'))
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Section::make('معلومات أساسية')
                    ->schema([
                        Select::make('student_id')
                            ->label(__('filament/admin/student_parent_resource.student_id'))
                            ->relationship('student', 'student_code', fn($query) => $query->where('status', 'active'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->student_code} - {$record->user?->name}")
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $tenantId = auth()->user()->tenant_id;
                                return \App\Models\Student::where('status', 'active')
                                    ->when(!auth()->user()->is_super_admin && $tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                                    ->where(function ($q) use ($search) {
                                        $q->where('student_code', 'like', "%{$search}%")
                                            ->orWhere('phone', 'like', "%{$search}%")
                                            ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                                    })
                                    ->with('user')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($s) => [$s->id => "{$s->student_code} - " . ($s->user?->name ?? 'طالب')]);
                            })
                            ->required()
                            ->preload(),

                        Select::make('relation')
                            ->label(__('filament/admin/student_parent_resource.relation'))
                            ->options(['father' => __('filament/admin/student_parent_resource.relation.father'), 'mother' => __('filament/admin/student_parent_resource.relation.mother'), 'guardian' => __('filament/admin/student_parent_resource.relation.guardian'), 'other' => __('filament/admin/student_parent_resource.relation.other')])
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('الصلاحيات')
                    ->schema([
                        Toggle::make('is_primary')
                            ->label(__('filament/admin/student_parent_resource.is_primary'))
                            ->helperText('ولي الأمر الرئيسي للطالب')
                            ->default(false),

                        Toggle::make('can_pickup')
                            ->label(__('filament/admin/student_parent_resource.can_pickup'))
                            ->helperText('يمكنه استلام الطالب')
                            ->default(true),

                        Toggle::make('receive_notifications')
                            ->label(__('filament/admin/student_parent_resource.receive_notifications'))
                            ->helperText('يتلقى الإشعارات والتنبيهات')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }
}
