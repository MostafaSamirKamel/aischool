<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Group;
use App\Models\Student;

class AttendanceSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الجلسة')
                    ->schema([
                        DatePicker::make('attendance_date')
                            ->label(__('filament/instructor/attendance_session_resource.attendance_date'))
                            ->required()
                            ->default(now()),
                        Select::make('group_id')
                            ->label(__('filament/instructor/attendance_session_resource.group_id'))
                            ->relationship('group', 'name', function ($query) {
                                $instructorId = auth()->user()->instructor?->id;
                                return $query->where('instructor_id', $instructorId)
                                    ->where('tenant_id', auth()->user()->tenant_id)
                                    ->where('status', 'active');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $group = Group::find($state);
                                    $set('course_id', $group?->course_id);
                                    $set('department_id', $group?->course?->department_id);

                                    // جلب جميع الطلاب في المجموعة تلقائياً
                                    $group = Group::find($state);
                                    $students = $group ? $group->activeStudents()
                                        ->with('user')
                                        ->orderBy('students.student_code')
                                        ->get() : collect();

                                    $attendanceRecords = [];
                                    foreach ($students as $student) {
                                        $attendanceRecords[] = [
                                            'student_id' => $student->id,
                                            'status' => 'present',
                                            'check_in_time' => now()->format('H:i'),
                                            'commitment_score' => 8,
                                            'focus_score' => 8,
                                            'participation_score' => 8,
                                            'notes' => '',
                                        ];
                                    }
                                    $set('attendance_records', $attendanceRecords);
                                }
                            })
                            ->helperText('عند اختيار المجموعة سيتم تحميل جميع الطلاب تلقائياً'),
                        TextInput::make('classroom')
                            ->label(__('filament/instructor/attendance_session_resource.classroom')),
                        Textarea::make('session_notes')
                            ->label(__('filament/instructor/attendance_session_resource.session_notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    
                Section::make('تسجيل الحضور')
                    ->description('اختر حالة الحضور وأدخل درجات التقييم لكل طالب')
                    ->schema([
                        Repeater::make('attendance_records')
                            ->label('قائمة الطلاب')
                            ->relationship('records')
                            ->schema([
                                Select::make('student_id')
                                    ->label('الطالب')
                                    ->options(function (callable $get) {
                                        $groupId = $get('../../group_id');
                                        if (!$groupId) {
                                            return [];
                                        }
                                        $group = Group::find($groupId);
                                        $students = $group ? $group->activeStudents()
                                            ->with('user')
                                            ->orderBy('students.student_code')
                                            ->get() : collect();

                                        return $students->mapWithKeys(function ($student) {
                                            return [$student->id => $student->student_code . ' - ' . $student->user->name];
                                        });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),

                                Select::make('status')
                                    ->label('حالة الحضور')
                                    ->options([
                                        'present' => '✅ حاضر',
                                        'absent' => '❌ غائب',
                                        'late' => '⏰ متأخر',
                                        'excused' => '📝 غياب بعذر'
                                    ])
                                    ->required()
                                    ->default('present')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'present') {
                                            $set('check_in_time', now()->format('H:i'));
                                            $set('commitment_score', 8);
                                            $set('focus_score', 8);
                                            $set('participation_score', 8);
                                        } elseif ($state === 'late') {
                                            $set('check_in_time', now()->format('H:i'));
                                            $set('commitment_score', 5);
                                            $set('focus_score', 7);
                                            $set('participation_score', 7);
                                        } elseif ($state === 'absent') {
                                            $set('check_in_time', null);
                                            $set('commitment_score', 0);
                                            $set('focus_score', 0);
                                            $set('participation_score', 0);
                                        } else { // excused
                                            $set('check_in_time', null);
                                            $set('commitment_score', null);
                                            $set('focus_score', null);
                                            $set('participation_score', null);
                                        }
                                    })
                                    ->columnSpan(1),
                                    
                                TimePicker::make('check_in_time')
                                    ->label('وقت الحضور')
                                    ->seconds(false)
                                    ->visible(fn(callable $get) => in_array($get('status'), ['present', 'late']))
                                    ->columnSpan(1),

                                TextInput::make('commitment_score')
                                    ->label('🎯 الالتزام')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->default(8)
                                    ->suffix('/ 10')
                                    ->visible(fn(callable $get) => in_array($get('status'), ['present', 'late']))
                                    ->helperText('الانضباط والجدية')
                                    ->columnSpan(1),

                                TextInput::make('focus_score')
                                    ->label('👁️ التركيز')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->default(8)
                                    ->suffix('/ 10')
                                    ->visible(fn(callable $get) => in_array($get('status'), ['present', 'late']))
                                    ->helperText('الانتباه والتفاعل')
                                    ->columnSpan(1),

                                TextInput::make('participation_score')
                                    ->label('🙋 المشاركة')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->default(8)
                                    ->suffix('/ 10')
                                    ->visible(fn(callable $get) => in_array($get('status'), ['present', 'late']))
                                    ->helperText('المساهمة في النقاش')
                                    ->columnSpan(1),

                                Textarea::make('notes')
                                    ->label('ملاحظات')
                                    ->rows(2)
                                    ->placeholder('أي ملاحظات على أداء الطالب...')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible()
                            ->collapsed(false)
                            ->itemLabel(fn (array $state): ?string => 
                                $state['student_id']
                                ? (Student::find($state['student_id'])?->student_code ?? '') . ' - ' . (Student::find($state['student_id'])?->user?->name ?? 'طالب')
                                : '👤 طالب جديد'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
