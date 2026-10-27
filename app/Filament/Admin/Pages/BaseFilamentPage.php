<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page as BasePage;

/**
 * Base Page for Filament 4 compatibility
 * 
 * استخدم هذا الـ class كـ parent لأي صفحة جديدة لضمان التوافق مع Filament 4
 * 
 * @example
 * class MyPage extends BaseFilamentPage
 * {
 *     protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
 *     protected static \UnitEnum|string|null $navigationGroup = 'المجموعة';
 *     protected string $view = 'filament.pages.my-page'; // non-static!
 * }
 */
abstract class BaseFilamentPage extends BasePage
{
    /**
     * ⚠️ تنبيه: استخدم دائماً هذا الـ type للـ navigationIcon
     * 
     * @var \BackedEnum|string|null
     */
    protected static \BackedEnum|string|null $navigationIcon = null;

    /**
     * ⚠️ تنبيه: استخدم دائماً هذا الـ type للـ navigationGroup
     * 
     * @var \UnitEnum|string|null
     */
    protected static \UnitEnum|string|null $navigationGroup = null;

    /**
     * ⚠️ تنبيه: الـ $view يجب أن يكون non-static property
     * 
     * @var string
     */
    // protected string $view; // Define in child class
}
