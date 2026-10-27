<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Filament\Facades\Filament;
use App\Models\User;

class EmployeeNavigationMapper
{
    public static function map(string $panelId, array $navigationGroups): array
    {
        $categories = [];
        
        if ($panelId === 'admin') {
            $categories = [
                'students' => [
                    'label' => 'الطلاب والتسجيل',
                    'icon' => 'heroicon-o-academic-cap',
                    'groups' => [
                        'الطلاب والتسجيل' => [],
                        'متابعة الطلاب' => [],
                        'إدارة الطلاب' => [],
                    ],
                ],
                'academic' => [
                    'label' => 'الشؤون الأكاديمية',
                    'icon' => 'heroicon-o-book-open',
                    'groups' => [
                        'المحتوى التعليمي' => [],
                        'الامتحانات والتقييم' => [],
                        'التقارير والمتابعة' => [],
                    ],
                ],
                'finance' => [
                    'label' => 'الإدارة المالية',
                    'icon' => 'heroicon-o-banknotes',
                    'groups' => [
                        'الإدارة المالية' => [],
                        'إدارة الأجور' => [],
                        'تقارير الأقساط' => [],
                    ],
                ],
                'reports' => [
                    'label' => 'التقارير والإحصائيات',
                    'icon' => 'heroicon-o-chart-bar-square',
                    'groups' => [
                        'التقارير' => [],
                        'التقارير المالية' => [],
                    ],
                ],
                'administration' => [
                    'label' => 'إدارة النظام',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'groups' => [
                        'الموارد البشرية' => [],
                        'الإدارة والمستخدمين' => [],
                        'البيانات الأساسية' => [],
                        'إعدادات النظام' => [],
                    ],
                ],
            ];
        } elseif ($panelId === 'instructor') {
            $categories = [
                'teaching' => [
                    'label' => 'التدريس والدرجات',
                    'icon' => 'heroicon-o-academic-cap',
                    'groups' => [
                        'التدريس' => [],
                        'إدارة المحتوى' => [],
                    ],
                ],
                'groups' => [
                    'label' => 'المجموعات والطلاب',
                    'icon' => 'heroicon-o-user-group',
                    'groups' => [
                        'إدارة المجموعات' => [],
                        'التواصل' => [],
                    ],
                ],
                'supervision_reports' => [
                    'label' => 'المتابعة والتقارير',
                    'icon' => 'heroicon-o-document-chart-bar',
                    'groups' => [
                        'المتابعة الإشرافية' => [],
                        'الإدارة' => [],
                    ],
                ],
            ];
        } elseif ($panelId === 'supervisor') {
            $categories = [
                'supervision' => [
                    'label' => 'الإشراف والمجموعات',
                    'icon' => 'heroicon-o-user-group',
                    'groups' => [
                        'الإشراف' => [],
                    ],
                ],
                'quality' => [
                    'label' => 'الجودة التعليمية والتوجيه',
                    'icon' => 'heroicon-o-sparkles',
                    'groups' => [
                        'الإشراف التعليمي' => [],
                    ],
                ],
            ];
        }

        // Dashboard / Direct Items
        $dashboardItems = [];
        $unmappedGroups = [];

        foreach ($navigationGroups as $group) {
            $groupLabel = $group->getLabel();
            
            if (!$groupLabel || $groupLabel === 'Direct' || $groupLabel === 'بدون مجموعة (Direct / Root)') {
                foreach ($group->getItems() as $item) {
                    $dashboardItems[] = $item;
                }
                continue;
            }

            $mapped = false;
            foreach ($categories as &$cat) {
                if (array_key_exists($groupLabel, $cat['groups'])) {
                    $cat['groups'][$groupLabel][] = $group;
                    $mapped = true;
                    break;
                }
            }
            unset($cat);

            if (!$mapped) {
                $unmappedGroups[$groupLabel] = $group;
            }
        }

        // Filter out empty categories
        foreach ($categories as $k => $cat) {
            $hasItems = false;
            foreach ($cat['groups'] as $gList) {
                if (!empty($gList)) {
                    $hasItems = true;
                    break;
                }
            }
            if (!$hasItems) {
                unset($categories[$k]);
            }
        }

        return [
            'dashboard_items' => $dashboardItems,
            'categories' => $categories,
            'unmapped_groups' => $unmappedGroups,
        ];
    }
}

// Test mapping across panels
$cases = [
    ['panel' => 'admin', 'email' => 'admin@admin.com', 'role' => 'Super Admin'],
    ['panel' => 'admin', 'email' => 'manager.main@admin.com', 'role' => 'Branch Manager'],
    ['panel' => 'admin', 'email' => 'secretary.main@admin.com', 'role' => 'Secretary'],
    ['panel' => 'instructor', 'email' => 'instructor.main@admin.com', 'role' => 'Instructor'],
    ['panel' => 'supervisor', 'email' => 'supervisor.main@admin.com', 'role' => 'Supervisor'],
];

foreach ($cases as $c) {
    auth()->logout();
    $user = User::where('email', $c['email'])->first();
    auth()->login($user);
    $panel = Filament::getPanel($c['panel']);
    Filament::setCurrentPanel($panel);
    $panel->boot();

    $mapped = EmployeeNavigationMapper::map($c['panel'], Filament::getNavigation());

    echo "========================================================\n";
    echo "PANEL: {$c['panel']} | USER: {$c['role']} ({$user->name})\n";
    echo "========================================================\n";
    echo "Direct / Dashboard items: " . count($mapped['dashboard_items']) . "\n";
    foreach ($mapped['dashboard_items'] as $it) {
        echo "   * [{$it->getLabel()}] -> {$it->getUrl()}\n";
    }
    
    echo "Categories (" . count($mapped['categories']) . "):\n";
    foreach ($mapped['categories'] as $catKey => $cat) {
        $totalItems = 0;
        foreach ($cat['groups'] as $gName => $groups) {
            foreach ($groups as $g) {
                $totalItems += count($g->getItems());
            }
        }
        echo "   + Category: {$cat['label']} ({$totalItems} items)\n";
        foreach ($cat['groups'] as $gName => $groups) {
            foreach ($groups as $g) {
                echo "       - Subgroup: {$gName} (" . count($g->getItems()) . " items)\n";
                foreach ($g->getItems() as $item) {
                    echo "           > {$item->getLabel()}\n";
                }
            }
        }
    }

    echo "Unmapped Groups: " . count($mapped['unmapped_groups']) . "\n";
    foreach ($mapped['unmapped_groups'] as $uLabel => $uGroup) {
        echo "   ! {$uLabel} (" . count($uGroup->getItems()) . " items)\n";
    }
    echo "\n";
}
