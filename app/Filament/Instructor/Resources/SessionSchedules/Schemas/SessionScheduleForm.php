<?php

namespace App\Filament\Instructor\Resources\SessionSchedules\Schemas;

use App\Models\Group;
use App\Models\Classroom;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Placeholder;

class SessionScheduleForm
{
    public static function form($form): Schema
    {
        return $form->schema([
            Section::make('معلومات السيشن')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('group_id')
                                ->label('المجموعة')
                                ->required()
                                ->relationship('group', 'name', function ($query) {
                                    $instructor = auth()->user()->instructor;
                                    if (!$instructor) {
                                        return $query->whereRaw('1 = 0');
                                    }

                                    return $query->where('instructor_id', $instructor->id)
                                        ->where('tenant_id', auth()->user()->tenant_id)
                                        ->where('status', 'active');
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $group = Group::find($state);
                                        if ($group) {
                                            $set('course_id', $group->course_id);
                                            $set('instructor_id', $group->instructor_id);
                                        }
                                    }
                                }),

                            TextInput::make('title')
                                ->label('عنوان السيشن')
                                ->maxLength(255)
                                ->placeholder('مثال: شرح الباب الأول'),

                            Hidden::make('course_id'),
                            Hidden::make('instructor_id')
                                ->default(fn() => auth()->user()->instructor?->id),
                        ]),

                    Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('اكتب وصفاً للسيشن أو الموضوعات التي سيتم تناولها')
                        ->columnSpanFull(),
                ]),

            Section::make('الجدولة')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            DatePicker::make('date')
                                ->label('التاريخ')
                                ->required()
                                ->displayFormat('Y-m-d')
                                ->minDate(now())
                                ->live(),

                            TimePicker::make('start_time')
                                ->label('وقت البدء')
                                ->required()
                                ->seconds(false)
                                ->live(),

                            TimePicker::make('end_time')
                                ->label('وقت الانتهاء')
                                ->required()
                                ->seconds(false)
                                ->live()
                                ->rules([
                                    fn($get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $startTime = $get('start_time');
                                        if ($startTime && $value && $value <= $startTime) {
                                            $fail('وقت الانتهاء يجب أن يكون بعد وقت البدء');
                                        }
                                    },
                                ]),
                        ]),

                    Select::make('classroom_id')
                        ->label('القاعة')
                        ->relationship('classroom', 'name', function ($query) {
                            return $query->where('tenant_id', auth()->user()->tenant_id)
                                ->where('status', 'active');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->helperText('اختياري - اختر القاعة إذا كان السيشن داخل المركز'),

                    Radio::make('type')
                        ->label('نوع السيشن')
                        ->required()
                        ->options([
                            'regular' => 'عادي',
                            'makeup' => 'تعويضي',
                            'extra' => 'إضافي',
                            'exam' => 'امتحان',
                        ])
                        ->default('regular')
                        ->inline()
                        ->columnSpanFull(),
                ]),

            Section::make('تحذيرات التضارب')
                ->schema([
                    Placeholder::make('conflicts_warning')
                        ->label('')
                        ->content(function ($record, $get) {
                            if (!$record) {
                                // For create mode, we'll check conflicts after saving
                                return 'سيتم فحص التضاربات تلقائياً عند الحفظ.';
                            }

                            if (!$record->has_conflicts) {
                                return '✅ لا توجد تضاربات في الجدول';
                            }

                            $conflicts = $record->conflicts_data;
                            $html = '<div class="space-y-2">';

                            if (isset($conflicts['instructor'])) {
                                $html .= '<div class="text-red-600 font-semibold">⚠️ تضارب مع جدول المدرس:</div>';
                                $html .= '<ul class="list-disc list-inside">';
                                foreach ($conflicts['instructor'] as $conflict) {
                                    $html .= '<li>لديك سيشن آخر مع ' . $conflict['group'] . ' في نفس الوقت (' . $conflict['time'] . ')</li>';
                                }
                                $html .= '</ul>';
                            }

                            if (isset($conflicts['classroom'])) {
                                $html .= '<div class="text-orange-600 font-semibold">⚠️ تضارب في القاعة:</div>';
                                $html .= '<ul class="list-disc list-inside">';
                                foreach ($conflicts['classroom'] as $conflict) {
                                    $html .= '<li>القاعة محجوزة لـ ' . $conflict['group'] . ' مع ' . $conflict['instructor'] . ' (' . $conflict['time'] . ')</li>';
                                }
                                $html .= '</ul>';
                            }

                            if (isset($conflicts['students'])) {
                                $html .= '<div class="text-yellow-600 font-semibold">⚠️ تضارب مع طلاب:</div>';
                                $html .= '<ul class="list-disc list-inside">';
                                foreach ($conflicts['students'] as $conflict) {
                                    $html .= '<li>' . $conflict['student_name'] . ' لديه سيشن آخر مع ' . $conflict['conflicting_group'] . ' (' . $conflict['time'] . ')</li>';
                                }
                                $html .= '</ul>';
                            }

                            $html .= '</div>';
                            return new \Illuminate\Support\HtmlString($html);
                        }),
                ])
                ->visible(fn($record) => $record !== null),
        ]);
    }
}
