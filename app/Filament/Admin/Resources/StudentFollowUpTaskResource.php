<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StudentFollowUpTaskResource\Pages;
use App\Models\StudentFollowUpTask;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class StudentFollowUpTaskResource extends Resource
{
    protected static ?string $model = StudentFollowUpTask::class;

    protected static ?string $navigationLabel = 'متابعة الطلاب';

    protected static ?string $modelLabel = 'مهمة متابعة';

    protected static ?string $pluralModelLabel = 'مهام متابعة الطلاب';

    protected static ?int $navigationSort = 10;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الطلاب';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['student.user', 'group', 'assignee', 'creator'])
            ->latest('id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل مهمة المتابعة')
                    ->schema([
                        Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student.user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('group_id')
                            ->label('المجموعة')
                            ->relationship('group', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('title')
                            ->label('عنوان المهمة / الإشارة')
                            ->required()
                            ->maxLength(255),

                        Select::make('trigger_type')
                            ->label('سبب الإشارة')
                            ->options([
                                'repeated_absence' => 'غياب متكرر',
                                'missed_assignments' => 'واجبات لم تسلم',
                                'missed_exams' => 'امتحانات لم تحل',
                                'academic_followup' => 'متابعة تعليمية',
                                'declining_grades' => 'تدني الدرجات',
                                'parent_follow_up' => 'متابعة ولي الأمر',
                                'manual' => 'متابعة إدارية يدوية',
                            ])
                            ->required(),

                        Select::make('priority')
                            ->label('الأولوية')
                            ->options([
                                'low' => 'منخفضة',
                                'normal' => 'عادية',
                                'high' => 'عالية',
                                'urgent' => 'طارئة',
                            ])
                            ->default('normal')
                            ->required(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'new' => 'جديدة',
                                'contacted' => 'تم التواصل',
                            ])
                            ->default('new')
                            ->required(),

                        Select::make('assigned_to')
                            ->label('المسؤول عن المتابعة')
                            ->options(fn() => User::where('tenant_id', auth()->user()?->tenant_id)
                                ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'administrator', 'supervisor', 'secretary']))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Textarea::make('reason')
                            ->label('تفاصيل المشكلة / الملاحظات')
                            ->columnSpanFull(),

                        Textarea::make('outcome_notes')
                            ->label('نتائج المتابعة والإجراء المتخذ')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->placeholder('عامة')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('العنوان / السبب')
                    ->searchable()
                    ->limit(35),

                BadgeColumn::make('priority')
                    ->label('الأولوية')
                    ->colors([
                        'danger' => 'urgent',
                        'warning' => 'high',
                        'primary' => 'normal',
                        'gray' => 'low',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'urgent' => 'طارئة',
                        'high' => 'عالية',
                        'normal' => 'عادية',
                        'low' => 'منخفضة',
                        default => $state,
                    }),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => fn($state) => in_array($state, ['new', 'pending', 'in_progress']),
                        'success' => fn($state) => in_array($state, ['contacted', 'completed']),
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'contacted', 'completed' => 'تم التواصل',
                        default => 'جديدة',
                    }),

                TextColumn::make('assignee.name')
                    ->label('المسؤول')
                    ->placeholder('غير محدد'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'in_progress' => 'جاري المتابعة',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                    ]),

                SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'urgent' => 'طارئة',
                        'high' => 'عالية',
                        'normal' => 'عادية',
                        'low' => 'منخفضة',
                    ]),

                SelectFilter::make('trigger_type')
                    ->label('نوع الإشارة')
                    ->options([
                        'repeated_absence' => 'غياب متكرر',
                        'missed_assignments' => 'واجبات لم تسلم',
                        'missed_exams' => 'امتحانات لم تحل',
                        'academic_followup' => 'متابعة تعليمية',
                        'declining_grades' => 'تدني الدرجات',
                        'parent_follow_up' => 'متابعة ولي الأمر',
                        'manual' => 'يدوية',
                    ]),

            ])
            ->actions([
                EditAction::make(),
                Action::make('complete')
                    ->label('إغلاق المهمة')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(StudentFollowUpTask $record) => $record->status !== 'completed')
                    ->form([
                        Textarea::make('outcome_notes')
                            ->label('نتيجة المتابعة')
                            ->required(),
                    ])
                    ->action(function (StudentFollowUpTask $record, array $data) {
                        $record->markCompleted($data['outcome_notes']);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentFollowUpTasks::route('/'),
            'edit' => Pages\EditStudentFollowUpTask::route('/{record}/edit'),
        ];
    }
}
