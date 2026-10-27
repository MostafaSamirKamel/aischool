<?php

namespace App\Filament\Instructor\Resources\AssignmentSubmissions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AssignmentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات التسليم والصور المرفوعة')
                    ->schema([
                        Placeholder::make('assignment_title')
                            ->label('عنوان الواجب')
                            ->content(fn ($record) => $record?->assignment?->title ?? '-'),
                        Placeholder::make('student_name')
                            ->label('اسم الطالب')
                            ->content(fn ($record) => $record?->student?->name ?? '-'),
                        Placeholder::make('submitted_at')
                            ->label('تاريخ التسليم')
                            ->content(fn ($record) => $record?->submitted_at?->format('Y-m-d H:i') ?? 'لم يتم التسليم'),
                        Placeholder::make('notes')
                            ->label('ملاحظات الطالب')
                            ->content(fn ($record) => $record?->notes ?: 'لا توجد ملاحظات من الطالب'),
                        Placeholder::make('homework_images')
                            ->label('صور الواجب المرفوعة')
                            ->content(function ($record) {
                                if (!$record || empty($record->images_list)) {
                                    return 'لا توجد صور مرفوعة';
                                }

                                $html = '<div class="flex flex-wrap gap-2 pt-1">';
                                foreach ($record->images_list as $index => $path) {
                                    $url = route('assignments.submissions.image', ['submission' => $record->id, 'index' => $index]);
                                    $html .= '<a href="' . $url . '" target="_blank" class="block border rounded-lg overflow-hidden shadow-sm hover:ring-2 hover:ring-primary-500 transition">';
                                    $html .= '<img src="' . $url . '" alt="صورة ' . ($index + 1) . '" class="w-20 h-20 object-cover">';
                                    $html .= '</a>';
                                }
                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    
                Section::make('التقييم وملاحظات المهندس')
                    ->schema([
                        TextInput::make('score')
                            ->label('الدرجة (من 10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->step(0.5)
                            ->helperText('الدرجة من 0 إلى 10 (يقبل كسور مثل 8.5)'),
                        Select::make('status')
                            ->label('حالة التسليم')
                            ->options([
                                'pending' => 'معلق',
                                'submitted' => 'تم التسليم',
                                'graded' => 'تم التصحيح',
                                'late' => 'متأخر',
                            ])
                            ->required(),
                        Toggle::make('published_at')
                            ->label('اعتماد ونشر التقييم للطالب')
                            ->helperText('عند التفعيل تظهر الدرجة وملاحظات المهندس للطالب فوراً. عند الإلغاء تظل الدرجة مسودة خاصة.')
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null)
                            ->formatStateUsing(fn ($record) => (bool) $record?->published_at),
                        Textarea::make('instructor_feedback')
                            ->label('ملاحظات المهندس (Instructor Feedback)')
                            ->placeholder('اكتب ملاحظاتك للطالب، مثلاً: الحل كويس جدًا، بس محتاج تراجع استخدام الشرط الثاني.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
