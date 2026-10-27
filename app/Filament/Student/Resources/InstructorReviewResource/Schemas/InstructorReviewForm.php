<?php

namespace App\Filament\Student\Resources\InstructorReviewResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class InstructorReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Hidden::make('student_id')
                    ->default(fn () => auth()->user()->student?->id),

                Select::make('instructor_id')
                    ->label('المدرس')
                    ->relationship('instructor', 'employee_code', function ($query) {
                        // Get instructors from student's enrollments
                        $studentId = auth()->user()?->student?->id;
                        if (!$studentId) {
                            return $query->whereRaw('1 = 0');
                        }

                        $instructorIds = \App\Models\EnrollmentCourse::query()
                            ->whereHas('enrollment', function ($q) use ($studentId) {
                                $q->where('student_id', $studentId);
                            })
                            ->whereHas('group', function ($g) {
                                $g->whereNotNull('instructor_id');
                            })
                            ->with('group:id,instructor_id')
                            ->get()
                            ->pluck('group.instructor_id')
                            ->unique()
                            ->filter();

                        return $query->whereIn('instructors.id', $instructorIds);
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name ?? 'غير معروف')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('rating')
                    ->label('التقييم')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐ ممتاز',
                        4 => '⭐⭐⭐⭐ جيد جداً',
                        3 => '⭐⭐⭐ جيد',
                        2 => '⭐⭐ مقبول',
                        1 => '⭐ ضعيف',
                    ])
                    ->required()
                    ->native(false),

                Textarea::make('review_text')
                    ->label('نص المراجعة')
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}
