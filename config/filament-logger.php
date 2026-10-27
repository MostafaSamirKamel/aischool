<?php

return [
    'datetime_format' => 'd/m/Y H:i:s',
    'date_format' => 'd/m/Y',

    'activity_resource' => \Jacobtims\FilamentLogger\Resources\ActivityResource::class,
    'scoped_to_tenant' => true,
    'navigation_sort' => null,

    'resources' => [
        'enabled' => true,
        'log_name' => 'Resource',
        'logger' => \Jacobtims\FilamentLogger\Loggers\ResourceLogger::class,
        'color' => 'success',

        'exclude' => [
            // App\Filament\Resources\UserResource::class,
            \App\Filament\Admin\Pages\Reports\StudentReport::class,
            \App\Filament\Admin\Pages\Reports\FinancialReport::class,
            \App\Filament\Admin\Pages\Reports\EnrollmentReport::class,
            \App\Filament\Admin\Pages\Reports\PerformanceReport::class,
            \App\Filament\Admin\Resources\ExpenseCategories\ExpenseCategoryResource::class,
            \App\Filament\Admin\Resources\Expenses\ExpenseResource::class,
            \App\Filament\Admin\Resources\RevenueCategories\RevenueCategoryResource::class,
            \App\Filament\Admin\Resources\Revenues\RevenueResource::class,
            \App\Filament\Student\Resources\MyCourseResource::class,
            \App\Filament\Student\Resources\MyExamResource::class,
            \App\Filament\Student\Resources\MyAssignmentResource::class,
        ],
        'cluster' => null,
        'navigation_group' => 'قائمة النظام',
    ],

    'access' => [
        'enabled' => true,
        'logger' => \Jacobtims\FilamentLogger\Loggers\AccessLogger::class,
        'color' => 'danger',
        'log_name' => 'Access',
    ],

    'notifications' => [
        'enabled' => true,
        'logger' => \Jacobtims\FilamentLogger\Loggers\NotificationLogger::class,
        'color' => null,
        'log_name' => 'Notification',
    ],

    'models' => [
        'enabled' => true,
        'log_name' => 'Model',
        'color' => 'warning',
        'logger' => \Jacobtims\FilamentLogger\Loggers\ModelLogger::class,
        'register' => [
            // App\Models\User::class,
        ],
    ],

    'custom' => [
        // [
        //     'log_name' => 'Custom',
        //     'color' => 'primary',
        // ]
    ],
];
