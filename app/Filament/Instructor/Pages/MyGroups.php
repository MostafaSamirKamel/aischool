<?php

namespace App\Filament\Instructor\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use App\Models\Group;
use App\Models\Instructor;

class MyGroups extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected string $view = 'filament.instructor.pages.my-groups';
    
    protected static ?string $navigationLabel = 'مجموعاتي';
    
    protected static ?string $title = 'مجموعاتي';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament/instructor/navigation.groups_management');
    }

    public function table(Table $table): Table
    {
        $instructor = Instructor::where('user_id', auth()->id())->first();
        
        return $table
            ->query(
                Group::query()
                    ->where('instructor_id', $instructor?->id)
                    ->where('status', 'active')
                    ->with(['course', 'classroom', 'enrollmentCourses.enrollment.student.user'])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المجموعة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('course.name')
                    ->label('الكورس')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('classroom.name')
                    ->label('القاعة')
                    ->default('غير محددة')
                    ->icon('heroicon-o-building-office-2'),

                TextColumn::make('enrollmentCourses_count')
                    ->label('عدد الطلاب')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Count unique students through enrollmentCourses
                        return $record->enrollmentCourses()
                            ->distinct('enrollment_id')
                            ->count('enrollment_id');
                    }),

                TextColumn::make('capacity')
                    ->label('السعة')
                    ->formatStateUsing(fn($record) => "{$record->capacity} / {$record->max_students}")
                    ->badge()
                    ->color(fn($record) => $record->isFull() ? 'danger' : 'info'),

                TextColumn::make('sessions_progress')
                    ->label('السيشنات')
                    ->getStateUsing(function ($record) {
                        $completed = $record->completed_sessions_count;
                        $total     = $record->total_sessions_count;
                        return "{$completed}/{$total}";
                    })
                    ->formatStateUsing(function ($state, $record) {
                        $completed = $record->completed_sessions_count;
                        $total     = $record->total_sessions_count;
                        if ($total === 0) return 'لم تُحدد';
                        return "{$completed}/{$total}";
                    })
                    ->badge()
                    ->color(function ($record) {
                        $completed = $record->completed_sessions_count;
                        $total     = $record->total_sessions_count;
                        if ($total === 0)                     return 'gray';
                        if ($completed >= $total)             return 'success';
                        if ($completed >= ($total * 0.7))     return 'warning';
                        return 'info';
                    })
                    ->tooltip(function ($record) {
                        $completed = $record->completed_sessions_count;
                        $total     = $record->total_sessions_count;
                        if ($total === 0) return 'لا توجد سيشنات مجدولة';
                        $remaining = $total - $completed;
                        return "مكتملة: {$completed} | إجمالي: {$total} | متبقية: {$remaining}";
                    }),

                TextColumn::make('remaining_sessions')
                    ->label('المتبقي')
                    ->getStateUsing(function ($record) {
                        $completed = $record->completed_sessions_count;
                        $total = $record->total_sessions_count;

                        return max($total - $completed, 0);
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state === 0) {
                            return 'success';
                        }

                        if ($state <= 3) {
                            return 'warning';
                        }

                        return 'gray';
                    })
                    ->sortable(),

                TextColumn::make('schedule')
                    ->label('المواعيد')
                    ->formatStateUsing(function ($state) {
                        if (!$state || !is_array($state)) {
                            return 'غير محدد';
                        }
                        return collect($state)->map(function ($item) {
                            $day = $item['day'] ?? '';
                            $time = $item['time'] ?? '';
                            return "{$day}: {$time}";
                        })->join(' | ');
                    })
                    ->wrap(),

                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->actions([
                Action::make('viewAcademicCenter')
                    ->label('المركز الأكاديمي للمجموعة')
                    ->tooltip('الواجبات، الامتحانات، التسليمات، ونتائج الطلاب')
                    ->icon('heroicon-o-academic-cap')
                    ->color('primary')
                    ->url(fn($record) => route('filament.instructor.pages.homework-review-center', ['group' => $record->id])),

                Action::make('viewStudents')
                    ->label('الطلاب')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading(fn($record) => 'طلاب مجموعة: ' . $record->name)
                    ->modalContent(function ($record) {
                        // Get students through enrollmentCourses
                        $enrollmentCourses = $record->enrollmentCourses()
                            ->with(['enrollment.student.user'])
                            ->whereHas('enrollment.student') // Only get enrollments with valid students
                            ->get();

                        return view('filament.instructor.modals.group-students', [
                            'group' => $record,
                            'enrollmentCourses' => $enrollmentCourses
                        ]);
                    })
                    ->modalWidth('5xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),
            ])
            ->defaultSort('start_date', 'desc')
            ->emptyStateHeading('لا توجد مجموعات')
            ->emptyStateDescription('لم يتم تعيين أي مجموعات لك بعد')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
