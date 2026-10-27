<?php

namespace App\Filament\Instructor\Resources\LearningResources\Schemas;

use App\Models\Course;
use App\Models\Group;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات المادة')
                    ->schema([
                        TextInput::make('title')
                            ->label(__('filament/instructor/learning_resource_resource.title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__('filament/instructor/learning_resource_resource.description'))
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label(__('filament/instructor/learning_resource_resource.type'))
                                    ->required()
                                    ->options([
                                        'pdf' => 'ملف PDF',
                                        'image' => 'صورة',
                                        'audio' => 'صوت',
                                        'video' => 'فيديو',
                                        'document' => 'مستند',
                                        'presentation' => 'عرض تقديمي',
                                        'link' => 'رابط خارجي',
                                        'other' => 'أخرى'
                                    ])
                                    ->default('pdf')
                                    ->live(),
                            ]),
                    ]),

                Section::make('ربط المادة')
                    ->description('اختر الكورس والمجموعة ورقم المحاضرة')
                    ->schema([
                        Select::make('visibility')
                            ->default('group')
                            ->hidden()
                            ->dehydrated(),

                        Select::make('course_id')
                            ->label('الكورس')
                            ->required()
                            ->searchable()
                            ->relationship('course', 'name', function ($query) {
                                return $query->whereHas('groups', function ($q) {
                                    $instructorId = auth()->user()->instructor?->id;
                                    $q->where('instructor_id', $instructorId);
                                });
                            })
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('group_id', null);
                                $set('session_schedule_id', null);
                                $set('session_number', null);
                            }),

                        Select::make('group_id')
                            ->label('المجموعة')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $courseId = $get('course_id');
                                if (!$courseId) {
                                    return [];
                                }

                                return Group::where('instructor_id', auth()->user()->instructor?->id)
                                    ->where('course_id', $courseId)
                                    ->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('session_schedule_id', null);
                                $set('session_number', null);
                            })
                            ->disabled(fn(callable $get): bool => !$get('course_id'))
                            ->helperText('اختر الكورس أولاً'),

                        Select::make('session_schedule_id')
                            ->label('الجلسة المجدولة (اختياري)')
                            ->searchable()
                            ->options(function (callable $get) {
                                $groupId = $get('group_id');
                                if (!$groupId) {
                                    return [];
                                }

                                return \App\Models\SessionSchedule::where('group_id', $groupId)
                                    ->orderBy('date', 'asc')
                                    ->get()
                                    ->mapWithKeys(function ($session) {
                                        $label = ($session->date ? $session->date->format('Y-m-d') : '-') .
                                            ' - ' . ($session->start_time ?? '-') .
                                            ' إلى ' . ($session->end_time ?? '-');
                                        return [$session->id => $label];
                                    });
                            })
                            ->live()
                            ->disabled(fn(callable $get): bool => !$get('group_id'))
                            ->helperText('اختر المجموعة أولاً'),

                        TextInput::make('session_number')
                            ->label('رقم المحاضرة')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999)
                            ->helperText('أدخل رقم المحاضرة (مثال: 1، 2، 3...)')
                            ->placeholder('مثال: 1'),
                    ])
                    ->columns(2),

                Section::make('رفع الملف أو الرابط')
                    ->description('يمكنك رفع ملفات (صور، صوت، فيديو، مستندات) أو إضافة رابط خارجي')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('الملف أو الملفات')
                            ->disk('public')
                            ->directory('learning-resources')
                            ->maxSize(102400) // 100MB
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->acceptedFileTypes([
                                // Images
                                'image/jpeg',
                                'image/jpg',
                                'image/png',
                                'image/gif',
                                'image/svg+xml',
                                'image/webp',
                                // Audio
                                'audio/mpeg',
                                'audio/mp3',
                                'audio/wav',
                                'audio/ogg',
                                'audio/m4a',
                                // Video
                                'video/mp4',
                                'video/mpeg',
                                'video/quicktime',
                                'video/x-msvideo',
                                'video/webm',
                                // Documents
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'text/plain',
                            ])
                            ->previewable()
                            ->downloadable()
                            ->openable()
                            ->helperText('يمكنك رفع أكثر من ملف | الحد الأقصى: 100 ميجابايت للملف | يدعم: صور، PDF، Word، Excel، فيديو، صوت')
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $files = is_array($state) ? $state : [$state];
                                    $first = $files[0] ?? null;
                                    if ($first instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                        $set('file_name', $first->getClientOriginalName());
                                        $set('file_size', round($first->getSize() / 1024, 2));
                                        $set('file_extension', $first->getClientOriginalExtension());
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('external_link')
                            ->label('رابط خارجي (اختياري)')
                            ->url()
                            ->maxLength(500)
                            ->helperText('مثال: رابط يوتيوب، جوجل درايف، OneDrive، إلخ')
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->columnSpanFull(),
                    ]),

                Section::make('الإعدادات')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_downloadable')
                                    ->label(__('filament/instructor/learning_resource_resource.is_downloadable'))
                                    ->default(true)
                                    ->helperText('السماح للطلاب بتحميل الملف'),

                                Toggle::make('is_active')
                                    ->label(__('filament/instructor/learning_resource_resource.is_active'))
                                    ->default(true)
                                    ->helperText('إظهار المادة للطلاب'),
                            ]),
                    ]),
            ]);
    }
}
