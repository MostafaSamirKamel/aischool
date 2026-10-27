<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Roles;

use App\Filament\Admin\Resources\Roles\Pages\CreateRole;
use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Admin\Resources\Roles\Pages\ListRoles;
use App\Filament\Admin\Resources\Roles\Pages\ViewRole;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use BezhanSalleh\PluginEssentials\Concerns\Resource as Essentials;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Override;

class RoleResource extends Resource
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;
    use HasShieldFormComponents;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->unique(
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule): Unique => Utils::isTenancyEnabled() ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id) : $rule
                                    )
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->maxLength(255),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    ->default(Filament::getTenant()?->id)
                                    ->options(fn (): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),

                                static::getSelectAllFormComponent(),
                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                static::getShieldFormComponents(),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->weight(FontWeight::Medium)
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('filament-shield::filament-shield.column.guard_name')),
                TextColumn::make('team.name')
                    ->default('Global')
                    ->badge()
                    ->color(fn (mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                    ->label(__('filament-shield::filament-shield.column.team'))
                    ->searchable()
                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    #[Override]
    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return Utils::getResourceSlug();
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster();
    }

    public static function getEssentialsPlugin(): ?FilamentShieldPlugin
    {
        return FilamentShieldPlugin::get();
    }

    public static function getFilteredResourceEntities(): array
    {
        return collect(\BezhanSalleh\FilamentShield\Facades\FilamentShield::getResources())->toArray();
    }

    public static function getCheckboxListFormComponent(string $name, array $options, bool $searchable = true, array | int | string | null $columns = null, array | int | string | null $columnSpan = null): \Filament\Schemas\Components\Component
    {
        $user = auth()->user();
        if ($user && ! $user->hasRole('super_admin') && ! ($user->is_super_admin ?? false)) {
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            $options = collect($options)
                ->filter(fn ($label, $permissionName) => in_array($permissionName, $userPermissions, true))
                ->toArray();
        }

        return CheckboxList::make($name)
            ->hiddenLabel()
            ->options(fn (): array => $options)
            ->searchable($searchable)
            ->live()
            ->afterStateHydrated(function (\Filament\Schemas\Components\Component $component, string $operation, ?Model $record, \Filament\Schemas\Components\Utilities\Set $set) use ($options): void {
                static::setPermissionStateForRecordPermissions(
                    component: $component,
                    operation: $operation,
                    permissions: $options,
                    record: $record
                );

                static::toggleSelectAllViaEntities($component->getLivewire(), $set);
            })
            ->afterStateUpdated(function (\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set): void {
                static::toggleSelectAllViaEntities($livewire, $set);
            })
            ->selectAllAction(fn (
                \Filament\Actions\Action $action,
                \Filament\Schemas\Components\Component $component,
                \Livewire\Component $livewire,
                \Filament\Schemas\Components\Utilities\Set $set
            ) => static::bulkToggleableAction(
                action: $action,
                component: $component,
                livewire: $livewire,
                set: $set
            ))
            ->deselectAllAction(fn (
                \Filament\Actions\Action $action,
                \Filament\Schemas\Components\Component $component,
                \Livewire\Component $livewire,
                \Filament\Schemas\Components\Utilities\Set $set
            ) => static::bulkToggleableAction(
                action: $action,
                component: $component,
                livewire: $livewire,
                set: $set,
                resetState: true
            ))
            ->dehydrated(fn ($state): bool => ! blank($state))
            ->bulkToggleable()
            ->gridDirection('row')
            ->columns($columns ?? static::shield()->getCheckboxListColumns())
            ->columnSpan($columnSpan ?? static::shield()->getCheckboxListColumnSpan());
    }

    public static function getPageOptions(): array
    {
        $options = collect(\BezhanSalleh\FilamentShield\Facades\FilamentShield::getPages())
            ->flatMap(fn ($page) => $page['permissions'])
            ->toArray();

        $user = auth()->user();
        if ($user && ! $user->hasRole('super_admin') && ! ($user->is_super_admin ?? false)) {
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            return collect($options)->filter(fn ($label, $perm) => in_array($perm, $userPermissions, true))->toArray();
        }
        return $options;
    }

    public static function getWidgetOptions(): array
    {
        $options = collect(\BezhanSalleh\FilamentShield\Facades\FilamentShield::getWidgets())
            ->flatMap(fn ($widget) => $widget['permissions'])
            ->toArray();

        $user = auth()->user();
        if ($user && ! $user->hasRole('super_admin') && ! ($user->is_super_admin ?? false)) {
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            return collect($options)->filter(fn ($label, $perm) => in_array($perm, $userPermissions, true))->toArray();
        }
        return $options;
    }

    public static function toggleSelectAllViaEntities(\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set): void
    {
        /** @phpstan-ignore-next-line */
        $entitiesStates = collect($livewire->form->getFlatComponents())
            ->reduce(function (mixed $counts, mixed $component) {
                if ($component instanceof CheckboxList) {
                    $counts[$component->getName()] = count(array_keys($component->getOptions())) === count(collect($component->getState())->values()->unique()->toArray());
                }

                return $counts;
            }, collect())
            ->values();

        if ($entitiesStates->containsStrict(false)) {
            $set('select_all', false);
        } else {
            $set('select_all', true);
        }
    }

    public static function toggleEntitiesViaSelectAll(\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set, bool $state): void
    {
        /** @phpstan-ignore-next-line */
        $entitiesComponents = collect($livewire->form->getFlatComponents())
            ->filter(fn (mixed $component): bool => $component instanceof CheckboxList);

        if ($state) {
            $entitiesComponents
                ->each(
                    function (CheckboxList $component) use ($set): void {
                        $set($component->getName(), array_keys($component->getOptions()));
                    }
                );
        } else {
            $entitiesComponents
                ->each(fn (CheckboxList $component): CheckboxList => $component->state([]));
        }
    }

    public static function getTabFormComponentForResources(): \Filament\Schemas\Components\Component
    {
        return static::shield()->hasSimpleResourcePermissionView()
            ? static::getTabFormComponentForSimpleResourcePermissionsView()
            : \Filament\Schemas\Components\Tabs\Tab::make('resources')
                ->label(__('filament-shield::filament-shield.resources'))
                ->visible(fn (): bool => Utils::isResourceTabEnabled())
                ->badge(static::getResourceTabBadgeCount())
                ->schema([
                    \Filament\Schemas\Components\View::make('filament.components.shield-collapse-controls'),
                    \Filament\Schemas\Components\Grid::make()
                        ->schema(static::getResourceEntitiesSchema() ?? [])
                        ->columns(1),
                ]);
    }

    public static function getResourceEntitiesSchema(): ?array
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('super_admin') || ($user->is_super_admin ?? false));
        $userPermissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];

        $filteredEntities = collect(static::getFilteredResourceEntities())
            ->filter(function (array $entity) use ($isSuperAdmin, $userPermissions): bool {
                if (! $isSuperAdmin && ! empty($userPermissions)) {
                    $options = static::getResourcePermissionOptions($entity);
                    $available = collect($options)->filter(fn ($label, $perm) => in_array($perm, $userPermissions, true));
                    if ($available->isEmpty()) {
                        return false;
                    }
                }
                return true;
            });

        // Group entities by their Navigation Group
        $grouped = $filteredEntities->groupBy(function (array $entity): string {
            $resourceFqcn = $entity['resourceFqcn'] ?? '';
            if (class_exists($resourceFqcn) && method_exists($resourceFqcn, 'getNavigationGroup')) {
                try {
                    $group = $resourceFqcn::getNavigationGroup();
                    if ($group) {
                        return (string) $group;
                    }
                } catch (\Throwable $e) {}
            }
            return '📁 نماذج وصلاحيات عامة / أخرى';
        });

        $groupSections = [];
        $groupIndex = 0;

        foreach ($grouped as $groupName => $entities) {
            $groupIndex++;
            $groupKey = 'group_' . $groupIndex;

            $entityOptions = $entities->mapWithKeys(function (array $entity) {
                return [$entity['resourceFqcn'] => array_keys(static::getResourcePermissionOptions($entity))];
            })->toArray();

            $entityNames = array_keys($entityOptions);

            $resourceSections = $entities->map(function (array $entity): \Filament\Schemas\Components\Section {
                $sectionLabel = strval(
                    static::shield()->hasLocalizedPermissionLabels()
                        ? \BezhanSalleh\FilamentShield\Facades\FilamentShield::getLocalizedResourceLabel($entity['resourceFqcn'])
                        : $entity['model']
                );

                return \Filament\Schemas\Components\Section::make($sectionLabel)
                    ->description(fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                        '<span style="word-break: break-word; font-size: 0.8rem; color: #64748b;">' . 
                        \BezhanSalleh\FilamentShield\Support\Utils::showModelPath($entity['modelFqcn']) . 
                        '</span>'
                    ))
                    ->compact()
                    ->schema([
                        static::getCheckBoxListComponentForResource($entity),
                    ])
                    ->extraAttributes(['class' => 'fi-shield-resource-section'])
                    ->collapsible()
                    ->collapsed();
            })->toArray();

            $groupSections[] = \Filament\Schemas\Components\Section::make($groupName)
                ->description('صلاحيات قسم: ' . $groupName . ' (' . count($resourceSections) . ' نماذج)')
                ->icon('heroicon-o-folder')
                ->schema($resourceSections)
                ->headerActions([
                    \Filament\Actions\Action::make('select_all_' . $groupKey)
                        ->label('تحديد كل صلاحيات القسم')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->size('xs')
                        ->action(function (\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set) use ($entityOptions): void {
                            foreach ($entityOptions as $componentName => $options) {
                                $set($componentName, $options);
                            }
                            static::toggleSelectAllViaEntities($livewire, $set);
                        }),
                    \Filament\Actions\Action::make('deselect_all_' . $groupKey)
                        ->label('إلغاء تحديد القسم')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->size('xs')
                        ->action(function (\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set) use ($entityNames): void {
                            foreach ($entityNames as $componentName) {
                                $set($componentName, []);
                            }
                            static::toggleSelectAllViaEntities($livewire, $set);
                        }),
                ])
                ->extraAttributes(['class' => 'fi-shield-group-section'])
                ->collapsible()
                ->columnSpanFull();
        }

        return $groupSections;
    }

    public static function getTabFormComponentForPage(): \Filament\Schemas\Components\Component
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('super_admin') || ($user->is_super_admin ?? false));
        $userPermissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];

        $pages = collect(\BezhanSalleh\FilamentShield\Facades\FilamentShield::getPages());

        // Group pages by Navigation Group
        $grouped = $pages->groupBy(function (array $page): string {
            $pageClass = $page['class'] ?? '';
            if (class_exists($pageClass) && method_exists($pageClass, 'getNavigationGroup')) {
                try {
                    $group = $pageClass::getNavigationGroup();
                    if ($group) {
                        return (string) $group;
                    }
                } catch (\Throwable $e) {}
            }
            return '📁 صفحات وتقارير عامة / أخرى';
        });

        $groupSections = [];
        $groupIndex = 0;
        $totalPagePermissionsCount = 0;

        foreach ($grouped as $groupName => $pagesInGroup) {
            $groupIndex++;
            $groupKey = 'page_group_' . $groupIndex;

            $groupOptions = [];
            foreach ($pagesInGroup as $page) {
                foreach ($page['permissions'] as $permName => $permLabel) {
                    if ($isSuperAdmin || in_array($permName, $userPermissions, true)) {
                        $groupOptions[$permName] = $permLabel;
                    }
                }
            }

            if (empty($groupOptions)) {
                continue;
            }

            $totalPagePermissionsCount += count($groupOptions);
            $componentName = 'pages_' . $groupKey;
            $allPermKeys = array_keys($groupOptions);

            $groupSections[] = \Filament\Schemas\Components\Section::make($groupName)
                ->description('صلاحيات صفحات: ' . $groupName . ' (' . count($groupOptions) . ' صفحة)')
                ->icon('heroicon-o-folder')
                ->schema([
                    static::getCheckboxListFormComponent(
                        name: $componentName,
                        options: $groupOptions,
                        searchable: false,
                        columns: 3,
                        columnSpan: 3
                    ),
                ])
                ->headerActions([
                    \Filament\Actions\Action::make('select_all_' . $groupKey)
                        ->label('تحديد كل صفحات القسم')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->size('xs')
                        ->action(function (\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set) use ($componentName, $allPermKeys): void {
                            $set($componentName, $allPermKeys);
                            static::toggleSelectAllViaEntities($livewire, $set);
                        }),
                    \Filament\Actions\Action::make('deselect_all_' . $groupKey)
                        ->label('إلغاء تحديد القسم')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->size('xs')
                        ->action(function (\Livewire\Component $livewire, \Filament\Schemas\Components\Utilities\Set $set) use ($componentName): void {
                            $set($componentName, []);
                            static::toggleSelectAllViaEntities($livewire, $set);
                        }),
                ])
                ->extraAttributes(['class' => 'fi-shield-group-section'])
                ->collapsible()
                ->columnSpanFull();
        }

        return \Filament\Schemas\Components\Tabs\Tab::make('pages')
            ->label(__('filament-shield::filament-shield.pages'))
            ->visible(fn (): bool => Utils::isPageTabEnabled() && $totalPagePermissionsCount > 0)
            ->badge($totalPagePermissionsCount)
            ->schema([
                \Filament\Schemas\Components\Grid::make()
                    ->schema($groupSections)
                    ->columns(1),
            ]);
    }

    public static function getResourceTabBadgeCount(): ?int
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('super_admin') || ($user->is_super_admin ?? false));
        if ($isSuperAdmin) {
            return collect(static::getFilteredResourceEntities())
                ->sum(fn (array $resource): int => count($resource['permissions']));
        }

        $userPermissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];

        return collect(static::getFilteredResourceEntities())
            ->sum(function (array $entity) use ($userPermissions): int {
                $options = static::getResourcePermissionOptions($entity);
                return collect($options)->filter(fn ($label, $perm) => in_array($perm, $userPermissions, true))->count();
            });
    }
}
