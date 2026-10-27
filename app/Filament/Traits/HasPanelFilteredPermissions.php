<?php

namespace App\Filament\Traits;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

trait HasPanelFilteredPermissions
{
    protected static function getPanelType(): ?string
    {
        // الحصول على panel_type من Livewire component أو من الفورم
        $panelType = null;
        
        // محاولة الحصول عليها من Livewire component
        if (function_exists('livewire') && $component = livewire()) {
            // محاولة من property مباشر (أولوية أعلى)
            if (property_exists($component, 'currentPanelType')) {
                $panelType = $component->currentPanelType;
            }
            // محاولة من data array (في الفورم)
            elseif (isset($component->data['panel_type'])) {
                $panelType = $component->data['panel_type'];
            }
            // محاولة من record
            elseif (isset($component->record) && isset($component->record->panel_type)) {
                $panelType = $component->record->panel_type;
            }
        }
        
        // fallback للـ request
        if (empty($panelType)) {
            $panelType = request()->input('data.panel_type') ?? request()->input('panel_type') ?? request()->old('panel_type');
        }
        
        return $panelType;
    }

    /**
     * لا نعمل override لـ getShieldFormComponents لأنه سيمنع Shield من العمل بشكل صحيح
     * بدلاً من ذلك، نفلتر فقط في getResourceEntitiesSchema و getPageOptions و getWidgetOptions
     */

    public static function getResourceEntitiesSchema(Get $get = null): ?array
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->is_super_admin || $user->hasRole('super_admin'));
        $userPermissions = $user && ! $isSuperAdmin ? $user->getAllPermissions()->pluck('name')->toArray() : null;

        $filtered = collect(FilamentShield::getResources())
            ->filter(function (array $entity, string $key) use ($get, $isSuperAdmin, $userPermissions) {
                // الحصول على panel_type من الفورم مباشرة إذا كان متاح
                $panelType = $get ? $get('panel_type') : static::getPanelType();
                
                // إذا كان هناك panel_type محدد، فلتر حسب namespace
                if (!empty($panelType) && $panelType !== 'admin') {
                    $panelNamespace = "App\\Filament\\" . ucfirst($panelType) . "\\";
                    if (!str_starts_with($entity['resourceFqcn'], $panelNamespace)) {
                        return false;
                    }
                }

                if (! $isSuperAdmin) {
                    $options = static::getResourcePermissionOptions($entity);
                    $available = collect($options)->filter(fn ($label, $perm) => in_array($perm, $userPermissions, true));
                    if ($available->isEmpty()) {
                        return false;
                    }
                }

                return true;
            });

        // Group entities by their Navigation Group
        $grouped = $filtered->groupBy(function (array $entity): string {
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
                return [$entity['model'] => array_keys(static::getResourcePermissionOptions($entity))];
            })->toArray();

            $entityNames = array_keys($entityOptions);

            $resourceSections = $entities->map(function (array $entity): \Filament\Schemas\Components\Section {
                $sectionLabel = strval(
                    FilamentShield::getLocalizedResourceLabel($entity['resourceFqcn'])
                );

                return Section::make($sectionLabel)
                    ->description(new HtmlString(
                        '<span style="word-break: break-word; font-size: 0.8rem; color: #64748b;">' . Utils::showModelPath($entity['modelFqcn']) . '</span>'
                    ))
                    ->compact()
                    ->schema([
                        static::getCheckBoxListComponentForResource($entity),
                    ])
                    ->extraAttributes(['class' => 'fi-shield-resource-section'])
                    ->collapsible()
                    ->collapsed();
            })->toArray();

            $groupSections[] = Section::make($groupName)
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
                        }),
                ])
                ->extraAttributes(['class' => 'fi-shield-group-section'])
                ->collapsible()
                ->columnSpanFull();
        }

        return $groupSections;
    }

    public static function getResourceTabBadgeCount(): ?int
    {
        $panelType = static::getPanelType();
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->is_super_admin || $user->hasRole('super_admin'));
        $userPermissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];
        
        return collect(FilamentShield::getResources())
            ->filter(function (array $entity) use ($panelType) {
                if (empty($panelType) || $panelType === 'admin') {
                    return true;
                }
                $panelNamespace = "App\\Filament\\" . ucfirst($panelType) . "\\";
                return str_starts_with($entity['resourceFqcn'], $panelNamespace);
            })
            ->sum(function (array $entity) use ($isSuperAdmin, $userPermissions): int {
                $options = static::getResourcePermissionOptions($entity);
                if ($isSuperAdmin) {
                    return count($options);
                }
                return collect($options)->filter(fn ($label, $perm) => in_array($perm, $userPermissions, true))->count();
            });
    }

    public static function getResourcePermissionOptions(array $entity): array
    {
        return once(fn (): array => FilamentShield::getResourcePermissionsWithLabels($entity['resourceFqcn']));
    }

    public static function getCheckBoxListComponentForResource(array $entity): Component
    {
        $permissionsArray = static::getResourcePermissionOptions($entity);

        return static::getCheckboxListFormComponent(
            name: $entity['model'],
            options: $permissionsArray,
            searchable: false,
            columns: 2,
            columnSpan: 2
        )
        ->dehydrated(true);
    }

    public static function getTabFormComponentForResources(): Component
    {
        return false
            ? static::getTabFormComponentForSimpleResourcePermissionsView()
            : Tab::make('resources')
                ->label(__('filament-shield::filament-shield.resources'))
                ->visible(fn (): bool => Utils::isResourceTabEnabled())
                ->badge(static::getResourceTabBadgeCount())
                ->schema([
                    \Filament\Schemas\Components\View::make('filament.components.shield-collapse-controls'),
                    Grid::make()
                        ->schema(fn(Get $get) => static::getResourceEntitiesSchema($get))
                        ->columns(1),
                ]);
    }

    public static function getPageOptions(): array
    {
        $panelType = static::getPanelType();
        
        return collect(FilamentShield::getPages())
            ->filter(function (array $page) use ($panelType) {
                if (empty($panelType) || $panelType === 'admin') {
                    return true;
                }
                $panelNamespace = "App\\Filament\\" . ucfirst($panelType) . "\\";
                return str_starts_with($page['pageFqcn'], $panelNamespace);
            })
            ->flatMap(fn ($page) => $page['permissions'])
            ->toArray();
    }

    public static function getWidgetOptions(): array
    {
        $panelType = static::getPanelType();
        
        return collect(FilamentShield::getWidgets())
            ->filter(function (array $widget) use ($panelType) {
                if (empty($panelType) || $panelType === 'admin') {
                    return true;
                }
                $panelNamespace = "App\\Filament\\" . ucfirst($panelType) . "\\";
                return str_starts_with($widget['widgetFqcn'], $panelNamespace);
            })
            ->flatMap(fn ($widget) => $widget['permissions'])
            ->toArray();
    }

    public static function getTabFormComponentForPage(): \Filament\Schemas\Components\Component
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('super_admin') || ($user->is_super_admin ?? false));
        $userPermissions = $user && ! $isSuperAdmin ? $user->getAllPermissions()->pluck('name')->toArray() : null;

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
            ->visible(fn (): bool => \BezhanSalleh\FilamentShield\Support\Utils::isPageTabEnabled() && $totalPagePermissionsCount > 0)
            ->badge($totalPagePermissionsCount)
            ->schema([
                \Filament\Schemas\Components\Grid::make()
                    ->schema($groupSections)
                    ->columns(1),
            ]);
    }

    public static function getTabFormComponentForSimpleResourcePermissionsView(): Component
    {
        $panelType = static::getPanelType();
        $options = collect(FilamentShield::getAllResourcePermissionsWithLabels())
            ->filter(function ($label, $permission) use ($panelType) {
                if (empty($panelType) || $panelType === 'admin') {
                    return true;
                }
                // فلترة حسب panel
                return str_contains($permission, ucfirst($panelType));
            })
            ->toArray();
            
        $count = once(fn (): int => count($options));

        return Tab::make('resources')
            ->label(__('filament-shield::filament-shield.resources'))
            ->visible(fn (): bool => Utils::isResourceTabEnabled() && $count > 0)
            ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent(
                    name: 'resources_tab',
                    options: $options,
                ),
            ]);
    }

    public static function getCheckboxListFormComponent(
        string $name,
        array $options,
        bool $searchable = true,
        array|string|int|null $columns = null,
        array|string|int|null $columnSpan = null
    ): Component {
        $user = auth()->user();
        if ($user && ! $user->is_super_admin && ! $user->hasRole('super_admin')) {
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            $options = collect($options)
                ->filter(fn ($label, $permissionName) => in_array($permissionName, $userPermissions, true))
                ->toArray();
        }

        return CheckboxList::make($name)
            ->options($options)
            ->searchable($searchable)
            ->columns($columns ?? 2)
            ->columnSpan($columnSpan)
            ->gridDirection('row')
            ->bulkToggleable();
    }
}
