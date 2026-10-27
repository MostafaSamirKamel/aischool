<?php

namespace App\Filament\Instructor\Resources\StudentMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Schema;
use App\Models\Student;
use App\Models\Enrollment;

class StudentMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('student_id')
                            ->label('الطالب')
                            ->options(function () {
                                // Get students from instructor's groups
                                $instructorId = auth()->user()?->instructor?->id;
                                if (!$instructorId) {
                                    return [];
                                }
                                
                                return Enrollment::whereHas('group', function ($query) use ($instructorId) {
                                    $query->where('instructor_id', $instructorId)
                                        ->where('tenant_id', auth()->user()->tenant_id);
                                })
                                ->with('student.user')
                                ->get()
                                ->pluck('student.user.name', 'student_id')
                                ->unique();
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر الطالب المراد إرسال الرسالة إليه'),
                        
                        Select::make('priority')
                            ->label('الأولوية')
                            ->options([
                                'low' => 'منخفضة',
                                'normal' => 'عادية',
                                'high' => 'عالية',
                                'urgent' => 'عاجلة',
                            ])
                            ->default('normal')
                            ->required(),
                    ]),
                
                Grid::make(2)
                    ->schema([
                        Select::make('type')
                            ->label('نوع الرسالة')
                            ->options([
                                'info' => 'معلومة',
                                'warning' => 'تحذير',
                                'success' => 'نجاح',
                                'urgent' => 'عاجل',
                            ])
                            ->default('info')
                            ->required(),
                        
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'draft' => 'مسودة',
                                'sent' => 'مرسلة',
                            ])
                            ->default('sent')
                            ->required(),
                    ]),
                
                TextInput::make('subject')
                    ->label('عنوان الرسالة')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Textarea::make('message')
                    ->label('محتوى الرسالة')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
                
                FileUpload::make('attachments')
                    ->label('المرفقات')
                    ->multiple()
                    ->directory('student-messages')
                    ->maxSize(10240)
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->helperText('يمكن إرفاق ملفات (صور، PDF، Word) بحد أقصى 10 ميجا لكل ملف')
                    ->columnSpanFull(),
            ]);
    }
}
