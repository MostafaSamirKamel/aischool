<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use Filament\Tables;
use App\Models\Tenant;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class UsersReport extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;
    protected string $view = 'filament.admin.pages.users-report';

    public static function getNavigationIcon(): ?string
    {
        return __('filament/admin/users_report.navigation_icon');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/users_report.title');
    }

    public function getTitle(): string
    {
        return __('filament/admin/users_report.navigation_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/admin/users_report.navigation_group');
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public function table(Table $table): Table
    {
        $isSuperAdmin = auth()->user()?->is_super_admin === true;
        $tenantId = $isSuperAdmin ? null : auth()->user()->tenant_id;

        return $table
            ->query(
                User::query()
                    ->with(['tenant', 'roles'])
                    ->when(!$isSuperAdmin && $tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            )
            ->columns([
                TextColumn::make('tenant.name')
                    ->label(__('filament/admin/users_report.clinic'))
                    ->visible(fn() => auth()->user()?->is_super_admin === true)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label(__('filament/admin/users_report.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('filament/admin/users_report.email'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label(__('filament/admin/users_report.roles'))
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'مدير النظام',
                        'panel_user' => 'مستخدم',
                        default => $state,
                    }),

                IconColumn::make('is_super_admin')
                    ->label(__('filament/admin/users_report.super_admin'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),

                IconColumn::make('email_verified_at')
                    ->label(__('filament/admin/users_report.email_verified'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/users_report.registration_date'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/users_report.last_update'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label(__('filament/admin/users_report.tenant_filter'))
                    ->options(Tenant::pluck('name', 'id'))
                    ->visible(fn() => auth()->user()?->is_super_admin === true),

                SelectFilter::make('is_super_admin')
                    ->label(__('filament/admin/users_report.user_type_filter'))
                    ->options([
                        1 => 'مدير نظام',
                        0 => 'مستخدم عادي',
                    ]),

                Filter::make('email_verified')
                    ->label(__('filament/admin/users_report.verified_only'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Filter::make('email_not_verified')
                    ->label(__('filament/admin/users_report.not_verified_only'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('filament/admin/users_report.from_date_filter')),
                        DatePicker::make('created_until')
                            ->label(__('filament/admin/users_report.to_date_filter')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->headerActions([
                Action::make('print')
                    ->label(__('filament/admin/users_report.print'))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(fn() => $this->printReport()),

                Action::make('export')
                    ->label(__('filament/admin/users_report.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn() => $this->exportToExcel()),
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label(__('filament/admin/users_report.export_selected'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Collection $records) => $this->exportSelected($records)),
            ]);
    }

    protected function printReport()
    {
        $users = $this->getFilteredTableQuery()->get();
        
        $pdf = Pdf::loadView('reports.users-pdf', [
            'users' => $users,
            'title' => 'تقرير المستخدمين',
            'date' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'users-report-' . now()->format('Y-m-d') . '.pdf');
    }

    protected function exportToExcel()
    {
        return Excel::download(
            new \App\Exports\UsersExport($this->getFilteredTableQuery()->get()),
            'users-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    protected function exportSelected(Collection $records)
    {
        return Excel::download(
            new \App\Exports\UsersExport($records),
            'users-selected-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
