<?php

namespace App\Filament\Instructor\Resources\AttendanceSessions\Pages;

use App\Filament\Instructor\Resources\AttendanceSessions\AttendanceSessionResource;
use App\Models\AttendanceSession;
use App\Models\Group;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReports extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    
    protected static string $resource = AttendanceSessionResource::class;

    protected static ?string $navigationLabel = 'تقارير الحضور';
    
    protected static ?string $title = 'تقارير الحضور والغياب';

    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'group_id' => request()->query('group_id'),
            'date_from' => request()->query('date_from', now()->subMonth()->format('Y-m-d')),
            'date_to' => request()->query('date_to', now()->format('Y-m-d')),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('فلترة التقارير')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('group_id')
                                    ->label('المجموعة')
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        $user = auth()->user();

                                        if (!$user || !$user->instructor) {
                                            return [];
                                        }

                                        return Group::query()
                                            ->where('tenant_id', $user->tenant_id)
                                            ->whereHas('instructor', function ($query) use ($user) {
                                                $query->where('user_id', $user->id)
                                                    ->where('tenant_id', $user->tenant_id);
                                            })
                                            ->with('course')
                                            ->get()
                                            ->mapWithKeys(function ($group) {
                                                return [
                                                    $group->id => $group->name . ' - ' . $group->course->name
                                                ];
                                            });
                                    }),
                                DatePicker::make('date_from')
                                    ->label('من تاريخ')
                                    ->default(now()->subMonth()),
                                DatePicker::make('date_to')
                                    ->label('إلى تاريخ')
                                    ->default(now()),
                            ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        if (!$user || !$user->instructor) {
            return $table->query(AttendanceSession::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(fn (): Builder => AttendanceSession::query()
                    ->with(['group.course', 'records.student.user'])
                    ->where('tenant_id', $user->tenant_id)
                    ->whereHas('group.instructor', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where('tenant_id', $user->tenant_id);
                    })
                    ->when($this->data['group_id'] ?? null, function ($query, $groupId) {
                        $query->where('group_id', $groupId);
                    })
                    ->when($this->data['date_from'] ?? null, function ($query, $date) {
                        $query->where('attendance_date', '>=', $date);
                    })
                    ->when($this->data['date_to'] ?? null, function ($query, $date) {
                        $query->where('attendance_date', '<=', $date);
                    })
                    ->orderBy('attendance_date', 'desc')
            )
            ->columns([
                TextColumn::make('attendance_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group.course.name')
                    ->label('الكورس')
                    ->searchable(),
                TextColumn::make('classroom')
                    ->label('القاعة')
                    ->default('-'),
                TextColumn::make('records_count')
                    ->label('إجمالي الطلاب')
                    ->counts('records')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('present_count')
                    ->label('حاضر')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'present')->count();
                    }),
                TextColumn::make('late_count')
                    ->label('متأخر')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'late')->count();
                    }),
                TextColumn::make('absent_count')
                    ->label('غائب')
                    ->badge()
                    ->color('danger')
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'absent')->count();
                    }),
                TextColumn::make('excused_count')
                    ->label('بعذر')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        return $record->records()->where('status', 'excused')->count();
                    }),
                TextColumn::make('attendance_rate')
                    ->label('نسبة الحضور')
                    ->badge()
                    ->color(fn ($record) => $this->getAttendanceRateColor($record))
                    ->getStateUsing(function ($record) {
                        $total = $record->records()->count();
                        if ($total == 0) return '0%';
                        $present = $record->records()->whereIn('status', ['present', 'late'])->count();
                        return round(($present / $total) * 100) . '%';
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة المجموعة')
                    ->relationship('group', 'status')
                    ->options([
                        'active' => 'نشطة',
                        'completed' => 'مكتملة',
                        'scheduled' => 'مجدولة',
                    ]),
            ])
            ->recordActions([
                Action::make('viewDetails')
                    ->label('التفاصيل')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => 'تفاصيل حضور ' . $record->attendance_date->format('Y-m-d'))
                    ->modalContent(fn ($record) => view('filament.instructor.modals.attendance-details', [
                        'session' => $record->load('records.student.user')
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),
                Action::make('edit')
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->url(fn ($record) => AttendanceSessionResource::getUrl('take', [
                        'group_id' => $record->group_id,
                        'date' => $record->attendance_date->format('Y-m-d')
                    ])),
            ])
            ->defaultSort('attendance_date', 'desc')
            ->poll('30s');
    }

    protected function getAttendanceRateColor($record): string
    {
        $total = $record->records()->count();
        if ($total == 0) return 'gray';
        
        $present = $record->records()->whereIn('status', ['present', 'late'])->count();
        $rate = ($present / $total) * 100;
        
        if ($rate >= 80) return 'success';
        if ($rate >= 60) return 'warning';
        return 'danger';
    }
    
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.instructor.pages.attendance-reports');
    }
}
