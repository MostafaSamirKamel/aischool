<?php

namespace App\Filament\Admin\Resources\SupervisionReports;

use App\Filament\Admin\Resources\SupervisionReports\Pages\ListAdminSupervisionReports;
use App\Filament\Admin\Resources\SupervisionReports\Pages\ViewAdminSupervisionReport;
use App\Models\SupervisionReport;
use App\Services\EducationalSupervisionService;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminSupervisionReportResource extends Resource
{
    protected static ?string $model = SupervisionReport::class;

    protected static ?string $slug = 'supervision-reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'تقارير الإشراف التعليمي للفروع';

    protected static ?string $modelLabel = 'تقرير إشرافي';

    protected static ?string $pluralModelLabel = 'تقارير الإشراف التعليمي للفروع';

    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير والمتابعة';
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin sees published supervision reports across branches
        return parent::getEloquentQuery()
            ->with(['tenant', 'preparer', 'publisher', 'versions']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_code')
                    ->label('كود التقرير')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('tenant.name')
                    ->label('الفرع')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

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

                TextColumn::make('version')
                    ->label('الإصدار')
                    ->formatStateUsing(fn($state) => "v{$state}")
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        SupervisionReport::STATUS_PUBLISHED => 'منشور ومعتمد',
                        SupervisionReport::STATUS_REVISION_REQUESTED => 'مطلوب تعديل',
                        default => 'مسودة',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        SupervisionReport::STATUS_PUBLISHED => 'success',
                        SupervisionReport::STATUS_REVISION_REQUESTED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('report_type')
                    ->label('النوع')
                    ->options([
                        SupervisionReport::TYPE_WEEKLY => 'أسبوعي',
                        SupervisionReport::TYPE_MONTHLY => 'شهري',
                    ]),

                SelectFilter::make('tenant_id')
                    ->label('الفرع')
                    ->relationship('tenant', 'name'),
            ])
            ->actions([
                ViewAction::make()->label('معاينة'),

                Action::make('add_comment')
                    ->label('إضافة ملاحظة إدارية')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('info')
                    ->form([
                        Textarea::make('comment')
                            ->label('الملاحظة الإدارية (إلزامي)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SupervisionReport $record, array $data) {
                        $service = app(EducationalSupervisionService::class);
                        $service->addManagementComment($record, auth()->user(), $data['comment']);

                        Notification::make()
                            ->title('تمت إضافة الملاحظة الإدارية بنجاح')
                            ->success()
                            ->send();
                    }),

                Action::make('request_clarification')
                    ->label('طلب استفسار من المشرف')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('warning')
                    ->form([
                        Textarea::make('question')
                            ->label('السؤال أو الاستفسار المطلوب من المشرف إيضاحه (إلزامي)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SupervisionReport $record, array $data) {
                        $service = app(EducationalSupervisionService::class);
                        $service->requestReportClarification($record, auth()->user(), $data['question']);

                        Notification::make()
                            ->title('تم إرسال طلب الاستفسار للمشرف')
                            ->success()
                            ->send();
                    }),

                Action::make('request_revision')
                    ->label('طلب إعادة صياغة / تعديل')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('طلب تعديل وإعادة صياغة التقرير')
                    ->modalDescription('سيتم تغيير حالة التقرير إلى "مطلوب تعديل" وإشعار المشرف لإعداد مسودة منقحة جديدة مع حفظ الإصدار الحالي.')
                    ->form([
                        Textarea::make('reason')
                            ->label('مبررات وأسباب طلب التعديل (إلزامي)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SupervisionReport $record, array $data) {
                        $service = app(EducationalSupervisionService::class);
                        $service->requestReportRevision($record, auth()->user(), $data['reason']);

                        Notification::make()
                            ->title('تم طلب تعديل التقرير بنجاح')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminSupervisionReports::route('/'),
            'view' => ViewAdminSupervisionReport::route('/{record}'),
        ];
    }
}
