<?php

namespace App\Filament\Admin\Resources\AttendanceSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Group;
use App\Models\Student;
use App\Models\Course;

class AttendanceSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament/admin/attendance_session_resource.session_info'))
                    ->description(__('filament/admin/attendance_session_resource.session_info_description'))
                    ->schema([
                        DatePicker::make('attendance_date')
                            ->label(__('filament/admin/attendance_session_resource.attendance_date'))
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Select::make('course_id')
                            ->label(__('filament/admin/attendance_session_resource.course_id'))
                            ->options(fn() => Course::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('group_id', null);
                                $set('department_id', null);
                                $set('classroom', null);
                                $set('records', []);
                            })
                            ->columnSpan(1),

                        Select::make('group_id')
                            ->label(__('filament/admin/attendance_session_resource.group_id'))
                            ->options(function (Get $get) {
                                $courseId = $get('course_id');
                                if (!$courseId) {
                                    return [];
                                }
                                return Group::where('course_id', $courseId)
                                    ->where('status', 'active')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn(Get $get) => !$get('course_id'))
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                if (!$state) {
                                    $set('records', []);
                                    $set('department_id', null);
                                    $set('classroom', null);
                                    return;
                                }

                                // Get group details with relationships
                                $group = Group::with(['classroom.department'])->find($state);
                                if ($group) {
                                    // Get department from classroom
                                    $set('department_id', $group->classroom?->department_id);
                                    // Get classroom name
                                    $set('classroom', $group->classroom?->name ?? $group->classroom?->code);
                                }

                                // Get all students in the selected group with active enrollments
                                $group = Group::find($state);
                                $students = $group ? $group->activeStudents()
                                    ->with('user')
                                    ->get() : collect();

                                // Prepare the attendance data for each student
                                $attendanceData = $students->map(function ($student) {
                                    return [
                                        'student_id' => $student->id,
                                        'student_name' => $student->user->name ?? 'غير معروف',
                                        'is_present' => true,
                                        'status' => 'present',
                                        'check_in_time' => now()->format('H:i'),
                                        'check_out_time' => null,
                                        'notes' => null,
                                    ];
                                })->toArray();

                                $set('records', $attendanceData);
                            })
                            ->columnSpan(1),

                        Select::make('department_id')
                            ->label(__('filament/admin/attendance_session_resource.department_id'))
                            ->relationship('department', 'name')
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(1),

                        TextInput::make('classroom')
                            ->label(__('filament/admin/attendance_session_resource.classroom'))
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(1),

                        Textarea::make('session_notes')
                            ->label(__('filament/admin/attendance_session_resource.session_notes'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make(__('filament/admin/attendance_session_resource.students_attendance'))
                    ->description(__('filament/admin/attendance_session_resource.students_attendance_description'))
                    ->schema([
                        Repeater::make('records')
                            ->label(__('filament/admin/attendance_session_resource.records'))
                            ->relationship('records')
                            ->schema([
                                Hidden::make('student_id')
                    ->label(__('filament/admin/attendance_session_resource.student_id')),

                                TextInput::make('student_name')
                                    ->label(__('filament/admin/attendance_session_resource.student_name'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($state, $set, $record) {
                                        if (!$state && $record && $record->student) {
                                            $set('student_name', $record->student->user->name ?? 'غير معروف');
                                        }
                                    })
                                    ->columnSpan(2),

                                Toggle::make('is_present')
                                    ->label(__('filament/admin/attendance_session_resource.is_present'))
                                    ->default(true)
                                    ->inline(false)
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('status', 'present');
                                            $set('check_in_time', now()->format('H:i'));
                                        } else {
                                            $set('status', 'absent');
                                            $set('check_in_time', null);
                                            $set('check_out_time', null);
                                        }
                                    })
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->label(__('filament/admin/attendance_session_resource.status'))
                                    ->options(['present' => __('filament/admin/attendance_session_resource.status.present'), 'absent' => __('filament/admin/attendance_session_resource.status.absent'), 'late' => __('filament/admin/attendance_session_resource.status.late'), 'excused' => __('filament/admin/attendance_session_resource.status.excused')])
                                    ->default('present')
                                    ->required()
                                    ->disabled(fn(Get $get) => !$get('is_present'))
                                    ->columnSpan(2),

                                TimePicker::make('check_in_time')
                                    ->label(__('filament/admin/attendance_session_resource.check_in_time'))
                                    ->seconds(false)
                                    ->disabled(fn(Get $get) => !$get('is_present'))
                                    ->columnSpan(1),

                                TimePicker::make('check_out_time')
                                    ->label(__('filament/admin/attendance_session_resource.check_out_time'))
                                    ->seconds(false)
                                    ->disabled(fn(Get $get) => !$get('is_present'))
                                    ->columnSpan(1),

                                Textarea::make('notes')
                                    ->label(__('filament/admin/attendance_session_resource.notes'))
                                    ->rows(1)
                                    ->columnSpan(2),
                            ])
                            ->columns(9)
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(Get $get) => !empty($get('records'))),
            ]);
    }
}
