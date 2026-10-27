<?php

namespace App\Filament\Supervisor\Resources\SupervisoryObservations;

use App\Filament\Supervisor\Resources\SupervisoryObservations\Pages\CreateSupervisoryObservation;
use App\Filament\Supervisor\Resources\SupervisoryObservations\Pages\ListSupervisoryObservations;
use App\Filament\Supervisor\Resources\SupervisoryObservations\Pages\ViewSupervisoryObservation;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\SupervisoryObservation;
use App\Services\EducationalSupervisionService;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupervisoryObservationResource extends Resource
{
    protected static ?string $model = SupervisoryObservation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'الملاحظات والتوجيهات الإشرافية';

    protected static ?string $modelLabel = 'ملاحظة إشرافية';

    protected static ?string $pluralModelLabel = 'الملاحظات الإشرافية';

    protected static ?int $navigationSort = 1;

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
            ->with(['instructor.user', 'group.course', 'supervisor', 'histories.actor'])
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->whereIn('instructor_id', $instructorIds);
    }

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $service = app(EducationalSupervisionService::class);
        $instructorIds = $service->getSupervisedInstructorIds($user);
        $groupIds = $service->getSupervisedGroupIds($user);

        return $schema->components([
            Section::make('بيانات الملاحظة والتكليف الإشرافي')
                ->description('تسجيل توجيه إشرافي مباشر للمهندس مع تحديد الإجراء التصحيحي المطلوب بدقة.')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('instructor_id')
                            ->label('المهندس المسئول')
                            ->options(
                                Instructor::whereIn('id', $instructorIds)
                                    ->with('user')
                                    ->get()
                                    ->pluck('user.name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('group_id')
                            ->label('المجموعة (اختياري)')
                            ->options(
                                Group::whereIn('id', $groupIds)
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('priority')
                            ->label('درجة الأهمية / الأولوية')
                            ->options([
                                'low' => 'منخفضة',
                                'normal' => 'عادية',
                                'urgent' => 'عاجلة وفورية',
                            ])
                            ->default('normal')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('category')
                            ->label('تصنيف الملاحظة')
                            ->options([
                                'attendance_not_recorded' => 'تأخر تسجيل الحضور والغياب',
                                'homework_grading_delay' => 'تأخر تصحيح الواجبات',
                                'examination_review' => 'مراجعة نتائج وتقييم الامتحانات',
                                'teaching_quality' => 'جودة الشرح والتفاعل الصفي',
                                'incomplete_educational_content' => 'ملاحظات استكمال المنهج والمحتوى',
                                'session_preparation' => 'جاهزية وتحضير السيشن',
                                'student_academic_concern' => 'متابعة مستوى أكاديمي للطلاب',
                                'other' => 'ملاحظة إشرافية عامة',
                            ])
                            ->required(),

                        DatePicker::make('expected_resolution_date')
                            ->label('تاريخ المراجعة المتوقع / المهلة')
                            ->default(now()->addDays(2))
                            ->required(),
                    ]),

                    TextInput::make('title')
                        ->label('موضوع / عنوان الملاحظة')
                        ->placeholder('مثال: رصد الحضور المتأخر لسيشن الأحد، أو تأخر تصحيح واجب الوحدة الثالثة')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('تفاصيل المشكلة المرصودة')
                        ->rows(3)
                        ->required(),

                    Textarea::make('required_action')
                        ->label('الإجراء التصحيحي المطلوب من المهندس')
                        ->placeholder('حدد بدقة ما يجب على المهندس إنجازه (مثال: الدخول لشاشة الحضور وتسجيل حضور المجموعة X وإشعارنا بالإنجاز)')
                        ->rows(3)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('observation_code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('instructor.user.name')
                    ->label('المهندس')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group.name')
                    ->label('المجموعة')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('category_label')
                    ->label('التصنيف')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('title')
                    ->label('الموضوع')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'urgent' => 'عاجلة',
                        'low' => 'منخفضة',
                        default => 'عادية',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'low' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
                        SupervisoryObservation::STATUS_COMPLETED => 'مكتملة',
                        default => 'جديدة',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'warning',
                        SupervisoryObservation::STATUS_COMPLETED => 'success',
                        default => 'info',
                    }),

                TextColumn::make('expected_resolution_date')
                    ->label('مهلة المعالجة')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        SupervisoryObservation::STATUS_NEW => 'جديدة',
                        SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
                        SupervisoryObservation::STATUS_COMPLETED => 'مكتملة',
                    ]),

                SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'urgent' => 'عاجلة',
                        'normal' => 'عادية',
                        'low' => 'منخفضة',
                    ]),

                SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        'attendance_not_recorded' => 'تأخر تسجيل الحضور',
                        'homework_grading_delay' => 'تأخر تصحيح الواجبات',
                        'examination_review' => 'مراجعة الامتحانات',
                        'teaching_quality' => 'جودة الشرح والتدريس',
                        'incomplete_educational_content' => 'ملاحظات المحتوى التعليمي',
                        'session_preparation' => 'جاهزية السيشن',
                        'student_academic_concern' => 'ملاحظة أكاديمية',
                        'other' => 'أخرى',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('معاينة'),

                Action::make('approve_completion')
                    ->label('اعتماد الإغلاق')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(SupervisoryObservation $record) => $record->status === SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
                    ->form([
                        Textarea::make('verification_notes')
                            ->label('ملاحظات التحقق الإشرافي (اختياري)')
                            ->placeholder('أدخل ملاحظات التأكد من اكتمال العمل المطلوب...')
                            ->rows(2),
                    ])
                    ->action(function (SupervisoryObservation $record, array $data) {
                        $record->approveCompletion(auth()->user(), $data['verification_notes'] ?? null);
                        Notification::make()
                            ->title('تم اعتماد إغلاق الملاحظة بنجاح')
                            ->body("تم توثيق إنجاز الملاحظة {$record->observation_code} وحفظ سجل الاعتماد.")
                            ->success()
                            ->send();
                    }),

                Action::make('return_for_correction')
                    ->label('إعادة للتصحيح')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn(SupervisoryObservation $record) => $record->status === SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
                    ->form([
                        Textarea::make('mandatory_reason')
                            ->label('سبب الإعادة والتوجيه الإضافي (إلزامي)')
                            ->placeholder('وضّح للمهندس ما زال ناقصاً أو يتطلب تعديلاً...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SupervisoryObservation $record, array $data) {
                        $record->returnForCorrection(auth()->user(), $data['mandatory_reason']);
                        Notification::make()
                            ->title('تمت إعادة الملاحظة للمهندس للتصحيح')
                            ->body("عادت حالة الملاحظة {$record->observation_code} إلى 'جديدة' مع حفظ سبب الإعادة في سجل التدقيق.")
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupervisoryObservations::route('/'),
            'create' => CreateSupervisoryObservation::route('/create'),
            'view' => ViewSupervisoryObservation::route('/{record}'),
        ];
    }
}
