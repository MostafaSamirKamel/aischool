<?php

namespace App\Filament\Instructor\Resources\Assignments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use App\Models\Group;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('group_id')
                    ->label(__('filament/instructor/assignment_resource.group_id'))
                    ->relationship('group', 'name', function ($query) {
                        $instructorId = auth()->user()->instructor?->id;
                        return $query->where('instructor_id', $instructorId)
                            ->where('tenant_id', auth()->user()->tenant_id)
                            ->where('status', 'active');
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->preload(),
                Select::make('attendance_session_id')
                    ->label('السيشن المرتبط بالواجب')
                    ->placeholder('-- واجب عام للمجموعة (بدون سيشن محدد) --')
                    ->options(function (callable $get) {
                        $groupId = $get('group_id');
                        if (!$groupId) return [];
                        $sessions = \App\Models\AttendanceSession::where('group_id', $groupId)->orderBy('attendance_date')->get();
                        if ($sessions->isNotEmpty()) {
                            return $sessions->mapWithKeys(function ($s, $idx) {
                                $d = $s->attendance_date ? $s->attendance_date->format('Y-m-d') : '';
                                $note = $s->session_notes ? ' - ' . \Illuminate\Support\Str::limit($s->session_notes, 30) : '';
                                return [$s->id => "السيشن رقم " . ($idx + 1) . " ({$d}{$note})"];
                            })->toArray();
                        }
                        $schedules = \App\Models\SessionSchedule::where('group_id', $groupId)->orderBy('date')->get();
                        return $schedules->mapWithKeys(function ($s, $idx) {
                            $d = $s->date ? $s->date->format('Y-m-d') : '';
                            $t = $s->title ? ' - ' . \Illuminate\Support\Str::limit($s->title, 30) : '';
                            return [$s->id => "السيشن رقم " . ($idx + 1) . " ({$d}{$t})"];
                        })->toArray();
                    })
                    ->searchable()
                    ->nullable(),
                TextInput::make('title')
                    ->label(__('filament/instructor/assignment_resource.title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('filament/instructor/assignment_resource.description'))
                    ->columnSpanFull()
                    ->rows(4),
                FileUpload::make('attachment_path')
                    ->label(__('filament/instructor/assignment_resource.attachment_path'))
                    ->disk('public')
                    ->directory('assignments')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240)
                    ->helperText('يمكن رفع ملف PDF أو Word'),
                TextInput::make('external_link')
                    ->label('رابط خارجي')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://example.com أو رابط YouTube، Google Drive، إلخ')
                    ->helperText('يمكن إضافة رابط بدلاً من أو بالإضافة إلى الملف المرفق'),
                DatePicker::make('due_date')
                    ->label(__('filament/instructor/assignment_resource.due_date'))
                    ->required()
                    ->minDate(now()),
                TextInput::make('max_score')
                    ->label(__('filament/instructor/assignment_resource.max_score'))
                    ->required()
                    ->numeric()
                    ->default(100)
                    ->minValue(1)
                    ->maxValue(1000),
                Select::make('status')
                    ->label(__('filament/instructor/assignment_resource.status'))
                    ->options(['active' => __('filament/instructor/assignment_resource.status.active'), 'closed' => __('filament/instructor/assignment_resource.status.closed')])
                    ->default(__('filament/instructor/assignment_resource.status_default'))
                    ->required(),
            ]);
    }
}
