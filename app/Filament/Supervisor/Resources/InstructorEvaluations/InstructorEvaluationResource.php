<?php

namespace App\Filament\Supervisor\Resources\InstructorEvaluations;

use App\Filament\Supervisor\Resources\InstructorEvaluations\Pages\CreateInstructorEvaluation;
use App\Filament\Supervisor\Resources\InstructorEvaluations\Pages\ListInstructorEvaluations;
use App\Filament\Supervisor\Resources\InstructorEvaluations\Pages\ViewInstructorEvaluation;
use App\Models\AttendanceSession;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use App\Services\EducationalSupervisionService;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstructorEvaluationResource extends Resource
{
    protected static ?string $model = InstructorEvaluation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'تقييمات جودة التدريس';

    protected static ?string $modelLabel = 'تقييم تدريس';

    protected static ?string $pluralModelLabel = 'تقييمات جودة التدريس';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'الإشراف التعليمي';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $service = app(EducationalSupervisionService::class);
        $instructorIds = $service->getSupervisedInstructorIds($user);

        return parent::getEloquentQuery()
            ->with(['instructor.user', 'supervisor', 'publisher'])
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->whereIn('instructor_id', $instructorIds);
    }

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $service = app(EducationalSupervisionService::class);
        $instructorIds = $service->getSupervisedInstructorIds($user);
        $groupIds = $service->getSupervisedGroupIds($user);

        $ratingOptions = [
            null => 'لم يتم تقييم هذا المعيار',
            1 => '1 - ضعيف / يحتاج تدخلاً عاجلاً',
            2 => '2 - دون المستوى المقبول',
            3 => '3 - مقبول / يفي بالحد الأدنى',
            4 => '4 - جيد جداً / متمكن',
            5 => '5 - ممتاز / متميز واحترافي',
        ];

        return $schema->components([
            Section::make('معلومات التقييم ونوعه')
                ->description('تحديد نوع التقييم وفترته والمهندس الخاضع للتقييم.')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('instructor_id')
                            ->label('المهندس')
                            ->options(
                                Instructor::whereIn('id', $instructorIds)
                                    ->with('user')
                                    ->get()
                                    ->pluck('user.name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('evaluation_type')
                            ->label('نوع التقييم')
                            ->options([
                                InstructorEvaluation::TYPE_MONTHLY => 'تقييم شهري رسمي',
                                InstructorEvaluation::TYPE_SESSION => 'تقييم سيشن (اختياري)',
                            ])
                            ->default(InstructorEvaluation::TYPE_MONTHLY)
                            ->reactive()
                            ->required(),

                        TextInput::make('evaluation_period')
                            ->label('فترة التقييم (السنة والشهـر)')
                            ->default(now()->format('Y-m'))
                            ->placeholder('YYYY-MM')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('attendance_session_id')
                            ->label('السيشن المرتبط (للتقييم الاختياري)')
                            ->options(
                                AttendanceSession::whereIn('group_id', $groupIds)
                                    ->with('group')
                                    ->latest('attendance_date')
                                    ->take(30)
                                    ->get()
                                    ->mapWithKeys(fn($s) => [$s->id => "{$s->group?->name} - {$s->attendance_date?->format('Y-m-d')}"])
                            )
                            ->searchable()
                            ->nullable(),

                        Select::make('review_basis')
                            ->label('أساس وبينة التقييم')
                            ->options([
                                'direct_observation' => 'ملاحظة مباشرة بالحضور الصفي',
                                'recording_review' => 'مراجعة تسجيل الحصة المسجلة',
                                'educational_work_review' => 'مراجعة الواجبات والأعمال التعليمية',
                                'monthly_evidence_review' => 'مراجعة الشواهد والتقارير الشهرية الشاملة',
                            ])
                            ->default('direct_observation')
                            ->required(),
                    ]),
                ]),

            Section::make('المعايير الرقمية الستة المعتمدة (مقياس 1 - 5)')
                ->description('اختر تقدير كل معيار، أو اختر (لم يتم تقييم هذا المعيار) لاستثنائه تماماً من المتوسط الحسابي دون احتساب صفر.')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('score_explanation_quality')
                            ->label('1. جودة الشرح وتبسيط المفاهيم')
                            ->options($ratingOptions)
                            ->nullable(),

                        Select::make('score_student_interaction')
                            ->label('2. التفاعل وإشراك الطلاب')
                            ->options($ratingOptions)
                            ->nullable(),

                        Select::make('score_practical_application')
                            ->label('3. التطبيق العملي والتمارين')
                            ->options($ratingOptions)
                            ->nullable(),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('score_curriculum_adherence')
                            ->label('4. الالتزام بالمنهج والخطة')
                            ->options($ratingOptions)
                            ->nullable(),

                        Select::make('score_time_management')
                            ->label('5. إدارة وقت الحصة والأنشطة')
                            ->options($ratingOptions)
                            ->nullable(),

                        Select::make('score_homework_followup')
                            ->label('6. متابعة وتصحيح الواجبات')
                            ->options($ratingOptions)
                            ->nullable(),
                    ]),
                ]),

            Section::make('الأقسام الكتابية الثلاثة الإلزامية')
                ->description('تحليل وصفي دقيق وموضوعي لأداء المهندس لتقديم توجيه نوعي حقيقي.')
                ->schema([
                    Textarea::make('strengths')
                        ->label('نقاط القوة (ما تميز به المهندس)')
                        ->rows(3)
                        ->required(),

                    Textarea::make('areas_for_improvement')
                        ->label('نقاط الضعف / جوانب التحسين المطلوبة')
                        ->rows(3)
                        ->required(),

                    Textarea::make('recommendations')
                        ->label('التوصيات العملية المقترحة للتطوير')
                        ->rows(3)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('evaluation_code')
                    ->label('كود التقييم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('instructor.user.name')
                    ->label('المهندس')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('evaluation_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        InstructorEvaluation::TYPE_SESSION => 'تقييم سيشن (اختياري)',
                        default => 'تقييم شهري رسمي',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        InstructorEvaluation::TYPE_SESSION => 'info',
                        default => 'primary',
                    }),

                TextColumn::make('evaluation_period')
                    ->label('الفترة')
                    ->sortable(),

                TextColumn::make('calculated_average')
                    ->label('المتوسط')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? "{$state} / 5" : 'لم يُحسب')
                    ->color(fn($state) => match (true) {
                        $state >= 4.0 => 'success',
                        $state >= 3.0 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('criteria_observed_count')
                    ->label('المعايير المرصودة')
                    ->formatStateUsing(fn($state) => "{$state} من 6")
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        InstructorEvaluation::STATUS_PUBLISHED => 'منشور رسمي',
                        default => 'مسودة غير منشورة',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        InstructorEvaluation::STATUS_PUBLISHED => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        InstructorEvaluation::STATUS_DRAFT => 'مسودة',
                        InstructorEvaluation::STATUS_PUBLISHED => 'منشور',
                    ]),

                SelectFilter::make('evaluation_type')
                    ->label('نوع التقييم')
                    ->options([
                        InstructorEvaluation::TYPE_MONTHLY => 'شهري رسمي',
                        InstructorEvaluation::TYPE_SESSION => 'سيشن اختياري',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('معاينة'),

                Action::make('publish_evaluation')
                    ->label('نشر التقييم الرسمي')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->visible(fn(InstructorEvaluation $record) => $record->status === InstructorEvaluation::STATUS_DRAFT)
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد نشر التقييم التعليمي الرسمي')
                    ->modalDescription('عند النشر، سيصبح هذا التقييم متاحاً ومقروءاً للمهندس في حسابه الشخصي، وسيتم إشعاره رسمياً.')
                    ->action(function (InstructorEvaluation $record) {
                        $record->publish(auth()->user());
                        Notification::make()
                            ->title('تم نشر التقييم الرسمي بنجاح')
                            ->body("تم نشر التقييم {$record->evaluation_code} للمهندس {$record->instructor?->user?->name}.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorEvaluations::route('/'),
            'create' => CreateInstructorEvaluation::route('/create'),
            'view' => ViewInstructorEvaluation::route('/{record}'),
        ];
    }
}
