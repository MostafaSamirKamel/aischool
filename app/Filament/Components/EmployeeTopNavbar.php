<?php

namespace App\Filament\Components;

use Filament\Facades\Filament;
use Filament\Livewire\Topbar as BaseTopbar;
use Illuminate\Contracts\View\View;

class EmployeeTopNavbar extends BaseTopbar
{
    /**
     * Get structured navigation data organized into top-level Mega Categories.
     */
    public function getNavigationData(): array
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $panelId = $panel?->getId() ?? 'admin';
        $navigation = Filament::getNavigation();
        $currentUrl = request()->url();

        // 1. Define Category Schemas per Panel
        $categoryConfigs = [];

        if ($panelId === 'admin') {
            $categoryConfigs = [
                'students' => [
                    'label' => 'الطلاب والتسجيل',
                    'icon' => 'heroicon-o-academic-cap',
                    'groups' => [
                        'الطلاب والتسجيل' => 'تسجيل وحضور الطلاب',
                        'متابعة الطلاب' => 'المتابعة اليومية',
                        'إدارة الطلاب' => 'المهام الأكاديمية',
                    ],
                ],
                'academic' => [
                    'label' => 'الشؤون الأكاديمية',
                    'icon' => 'heroicon-o-book-open',
                    'groups' => [
                        'المحتوى التعليمي' => 'المناهج والمجموعات',
                        'الامتحانات والتقييم' => 'الاختبارات والنتائج',
                        'التقارير والمتابعة' => 'الإشراف والاعتراضات',
                    ],
                ],
                'finance' => [
                    'label' => 'الإدارة المالية',
                    'icon' => 'heroicon-o-banknotes',
                    'groups' => [
                        'الإدارة المالية' => 'الخزائن والمصروفات',
                        'إدارة الأجور' => 'مرتبات المدرسين',
                        'تقارير الأقساط' => 'متابعة التحصيل',
                    ],
                ],
                'reports' => [
                    'label' => 'التقارير والإحصائيات',
                    'icon' => 'heroicon-o-chart-bar-square',
                    'groups' => [
                        'التقارير' => 'التقارير العامة والشاملة',
                        'التقارير المالية' => 'تقارير الإيرادات والخزائن',
                    ],
                ],
                'administration' => [
                    'label' => 'إدارة النظام',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'groups' => [
                        'الموارد البشرية' => 'فريق العمل والموظفين',
                        'الإدارة والمستخدمين' => 'الأدوار والمؤسسات',
                        'البيانات الأساسية' => 'التعريفات والإعدادات',
                        'إعدادات النظام' => 'المهام والجدولة',
                    ],
                ],
            ];
        } elseif ($panelId === 'instructor') {
            $categoryConfigs = [
                'teaching' => [
                    'label' => 'التدريس والدرجات',
                    'icon' => 'heroicon-o-academic-cap',
                    'groups' => [
                        'التدريس' => 'المركز الأكاديمي والنتائج',
                        'إدارة المحتوى' => 'الواجبات والاختبارات',
                    ],
                ],
                'groups' => [
                    'label' => 'المجموعات والطلاب',
                    'icon' => 'heroicon-o-user-group',
                    'groups' => [
                        'إدارة المجموعات' => 'المجموعات وجلسات الحضور',
                        'التواصل' => 'رسائل الطلاب والمجموعات',
                    ],
                ],
                'supervision_reports' => [
                    'label' => 'المتابعة والتقارير',
                    'icon' => 'heroicon-o-document-chart-bar',
                    'groups' => [
                        'المتابعة الإشرافية' => 'التوجيهات والتقييمات الرسمية',
                        'الإدارة' => 'الأجور والتقارير الأكاديمية',
                    ],
                ],
            ];
        } elseif ($panelId === 'supervisor') {
            $categoryConfigs = [
                'supervision' => [
                    'label' => 'الإشراف والمجموعات',
                    'icon' => 'heroicon-o-user-group',
                    'groups' => [
                        'الإشراف' => 'مجموعات وفروع المركز',
                    ],
                ],
                'quality' => [
                    'label' => 'الجودة التعليمية والتوجيه',
                    'icon' => 'heroicon-o-sparkles',
                    'groups' => [
                        'الإشراف التعليمي' => 'التقييمات والتقارير الذكية',
                    ],
                ],
            ];
        }

        // 2. Separate Direct / Dashboard items and categorize groups
        $dashboardItems = [];
        $categories = [];
        $unmappedGroups = [];

        // Initialize categories structure
        foreach ($categoryConfigs as $catKey => $config) {
            $categories[$catKey] = [
                'key' => $catKey,
                'label' => $config['label'],
                'icon' => $config['icon'],
                'is_active' => false,
                'total_items' => 0,
                'columns' => [],
            ];
        }

        foreach ($navigation as $group) {
            $groupLabel = trim($group->getLabel() ?? '');
            $items = $group->getItems();

            if (empty($items)) {
                continue;
            }

            // Direct / Root items
            if ($groupLabel === '' || in_array($groupLabel, ['Direct', 'الرئيسية', 'بدون مجموعة (Direct / Root)'])) {
                foreach ($items as $item) {
                    $dashboardItems[] = [
                        'label' => $item->getLabel(),
                        'url' => $item->getUrl(),
                        'icon' => $item->getIcon(),
                        'badge' => $item->getBadge(),
                        'badge_color' => $item->getBadgeColor($item->getBadge()),
                        'is_active' => $this->isItemActive($item, $currentUrl),
                        'target' => $item->shouldOpenUrlInNewTab() ? '_blank' : null,
                    ];
                }
                continue;
            }

            // Map into predefined categories
            $mapped = false;
            foreach ($categoryConfigs as $catKey => $config) {
                if (array_key_exists($groupLabel, $config['groups'])) {
                    $mapped = true;
                    $groupItemsData = [];
                    $groupIsActive = false;

                    foreach ($items as $item) {
                        $isActive = $this->isItemActive($item, $currentUrl);
                        if ($isActive) {
                            $groupIsActive = true;
                            $categories[$catKey]['is_active'] = true;
                        }

                        $groupItemsData[] = [
                            'label' => $item->getLabel(),
                            'url' => $item->getUrl(),
                            'icon' => $item->getIcon(),
                            'badge' => $item->getBadge(),
                            'badge_color' => $item->getBadgeColor($item->getBadge()),
                            'is_active' => $isActive,
                            'target' => $item->shouldOpenUrlInNewTab() ? '_blank' : null,
                        ];
                    }

                    $categories[$catKey]['columns'][] = [
                        'group_label' => $groupLabel,
                        'sub_heading' => $config['groups'][$groupLabel] ?? $groupLabel,
                        'is_active' => $groupIsActive,
                        'items' => $groupItemsData,
                    ];
                    $categories[$catKey]['total_items'] += count($groupItemsData);
                    break;
                }
            }

            if (!$mapped) {
                $unmappedItems = [];
                $unmappedActive = false;
                foreach ($items as $item) {
                    $isActive = $this->isItemActive($item, $currentUrl);
                    if ($isActive) {
                        $unmappedActive = true;
                    }
                    $unmappedItems[] = [
                        'label' => $item->getLabel(),
                        'url' => $item->getUrl(),
                        'icon' => $item->getIcon(),
                        'badge' => $item->getBadge(),
                        'badge_color' => $item->getBadgeColor($item->getBadge()),
                        'is_active' => $isActive,
                        'target' => $item->shouldOpenUrlInNewTab() ? '_blank' : null,
                    ];
                }

                $unmappedGroups[] = [
                    'group_label' => $groupLabel,
                    'sub_heading' => $groupLabel,
                    'is_active' => $unmappedActive,
                    'items' => $unmappedItems,
                ];
            }
        }

        // Add Unmapped Groups as an additional category if present
        if (!empty($unmappedGroups)) {
            $unmappedTotal = 0;
            $unmappedActive = false;
            foreach ($unmappedGroups as $uCol) {
                $unmappedTotal += count($uCol['items']);
                if ($uCol['is_active']) {
                    $unmappedActive = true;
                }
            }

            $categories['other'] = [
                'key' => 'other',
                'label' => 'أقسام إضافية',
                'icon' => 'heroicon-o-squares-plus',
                'is_active' => $unmappedActive,
                'total_items' => $unmappedTotal,
                'columns' => $unmappedGroups,
            ];
        }

        // Filter out empty categories
        $activeCategories = array_filter($categories, fn($cat) => !empty($cat['columns']));

        return [
            'dashboard_items' => $dashboardItems,
            'categories' => $activeCategories,
            'panel_id' => $panelId,
        ];
    }

    /**
     * Check if a navigation item is active based on current request URL.
     */
    protected function isItemActive($item, string $currentUrl): bool
    {
        if (method_exists($item, 'isActive') && $item->isActive()) {
            return true;
        }

        $url = $item->getUrl();
        if (!$url) {
            return false;
        }

        if ($url === $currentUrl) {
            return true;
        }

        // If URL matches base path
        $urlPath = parse_url($url, PHP_URL_PATH);
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);

        if ($urlPath && $currentPath) {
            $trimmedUrl = rtrim($urlPath, '/');
            $trimmedCurrent = rtrim($currentPath, '/');

            if ($trimmedUrl === $trimmedCurrent) {
                return true;
            }

            // Check if current page is a child route of the item
            if (strlen($trimmedUrl) > 6 && str_starts_with($trimmedCurrent, $trimmedUrl . '/')) {
                return true;
            }
        }

        return false;
    }

    public function render(): View
    {
        return view('filament.components.employee-top-navbar', [
            'navData' => $this->getNavigationData(),
            'tenant' => Filament::getTenant(),
            'panel' => Filament::getCurrentOrDefaultPanel(),
            'user' => auth()->user(),
        ]);
    }
}
