<?php

namespace App\Filament\Supervisor\Resources\SupervisionReports;

use App\Filament\Supervisor\Resources\SupervisionReports\Pages\CreateSupervisionReport;
use App\Filament\Supervisor\Resources\SupervisionReports\Pages\EditSupervisionReport;
use App\Filament\Supervisor\Resources\SupervisionReports\Pages\ListSupervisionReports;
use App\Filament\Supervisor\Resources\SupervisionReports\Pages\ViewSupervisionReport;
use App\Models\SupervisionReport;
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
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupervisionReportResource extends Resource
{
    protected static ?string $model = SupervisionReport::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'التقارير الإشرافية والإدارية';

    protected static ?string $modelLabel = 'تقرير إشرافي';

    protected static ?string $pluralModelLabel = 'التقارير الإشرافية والإدارية';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'الإشراف التعليمي';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->with(['preparer', 'publisher', 'versions'])
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات وفترة التقرير')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('report_type')
                            ->label('نوع التقرير')
                            ->options([
                                SupervisionReport::TYPE_WEEKLY => 'تقرير إشرافي أسبوعي',
                                SupervisionReport::TYPE_MONTHLY => 'تقرير إداري تعليمي شهري',
                            ])
                            ->default(SupervisionReport::TYPE_WEEKLY)
                            ->required(),

                        TextInput::make('period_key')
                            ->label('مفتاح الفترة (YYYY-WW أو YYYY-MM)')
                            ->default(now()->format('o-\\WW'))
                            ->required(),

                        TextInput::make('version')
                            ->label('الإصدار')
                            ->default(1)
                            ->disabled(),
                    ]),

                    Grid::make(2)->schema([
                        DatePicker::make('period_start')
                            ->label('بداية الفترة')
                            ->default(now()->startOfWeek())
                            ->required(),

                        DatePicker::make('period_end')
                            ->label('نهاية الفترة')
                            ->default(now()->endOfWeek())
                            ->required(),
                    ]),
                ]),

            Section::make('التحليل والأقسام الكتابية للمشرف')
                ->description('تسجيل الرؤية الإشرافية، التحديات المرصودة، والتوصيات الموجهة للإدارة.')
                ->schema([
                    Textarea::make('summary')
                        ->label('الملخص الإشرافي العام')
                        ->rows(3)
                        ->required(),

                    Textarea::make('instructor_observations')
                        ->label('ملاحظات أداء والتزام المهندسين')
                        ->rows(3)
                        ->required(),

                    Textarea::make('group_observations')
                        ->label('ملاحظات المجموعات الأكاديمية وسير الدراسة')
                        ->rows(3)
                        ->required(),

                    Textarea::make('problems_encountered')
                        ->label('المشكلات والعقبات المرصودة خلال الفترة')
                        ->rows(3),

                    Textarea::make('actions_taken')
                        ->label('الإجراءات الإشرافية والتصحيحية المتخذة')
                        ->rows(3),

                    Textarea::make('recommendations')
                        ->label('التوصيات المقترحة للإدارة وللأسبوع/الشهر القادم')
                        ->rows(3)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_code')
                    ->label('كود التقرير')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('report_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        SupervisionReport::TYPE_WEEKLY => 'أسبوعي',
                        default => 'شهري',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        SupervisionReport::TYPE_WEEKLY => 'info',
                        default => 'primary',
                    }),

                TextColumn::make('period_key')
                    ->label('الفترة')
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('من')
                    ->date('Y-m-d'),

                TextColumn::make('period_end')
                    ->label('إلى')
                    ->date('Y-m-d'),

                TextColumn::make('version')
                    ->label('الإصدار')
                    ->formatStateUsing(fn($state) => "v{$state}")
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        SupervisionReport::STATUS_PUBLISHED => 'معتمد ومنشور للإدارة',
                        SupervisionReport::STATUS_REVISION_REQUESTED => 'مطلوب مراجعة وتعديل',
                        default => 'مسودة قيد الإعداد',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        SupervisionReport::STATUS_PUBLISHED => 'success',
                        SupervisionReport::STATUS_REVISION_REQUESTED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('preparer.name')
                    ->label('المُعِد')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('report_type')
                    ->label('نوع التقرير')
                    ->options([
                        SupervisionReport::TYPE_WEEKLY => 'أسبوعي',
                        SupervisionReport::TYPE_MONTHLY => 'شهري',
                    ]),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        SupervisionReport::STATUS_DRAFT => 'مسودة',
                        SupervisionReport::STATUS_PUBLISHED => 'منشور',
                        SupervisionReport::STATUS_REVISION_REQUESTED => 'مطلوب تعديل',
                    ]),
            ])
            ->actions([
                ViewAction::make()->label('معاينة'),
                EditAction::make()
                    ->label('تعديل المسودة')
                    ->visible(fn(SupervisionReport $record) => in_array($record->status, [SupervisionReport::STATUS_DRAFT, SupervisionReport::STATUS_REVISION_REQUESTED])),

                Action::make('publish_report')
                    ->label('اعتماد ونشر التقرير')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(SupervisionReport $record) => in_array($record->status, [SupervisionReport::STATUS_DRAFT, SupervisionReport::STATUS_REVISION_REQUESTED]))
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد اعتماد ونشر التقرير للإدارة')
                    ->modalDescription('سيتم اعتماد هذا التقرير رسمياً وإتاحته لإدارة المدرسة، وحفظ لقطة إصدار تاريخية غير قابلة للتعديل الصامت.')
                    ->action(function (SupervisionReport $record) {
                        $service = app(\App\Services\EducationalSupervisionService::class);
                        $service->publishSupervisionReport($record, auth()->user());

                        Notification::make()
                            ->title('تم اعتماد ونشر التقرير الإشرافي بنجاح')
                            ->body("أصبح التقرير {$record->report_code} متاحاً للمركز الرئيسي والإدارة.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupervisionReports::route('/'),
            'create' => CreateSupervisionReport::route('/create'),
            'edit' => EditSupervisionReport::route('/{record}/edit'),
            'view' => ViewSupervisionReport::route('/{record}'),
        ];
    }
}
