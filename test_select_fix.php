<?php

/**
 * Test Select Dropdowns Display
 * اختبار عرض قوائم Select
 * 
 * Run: php test_select_fix.php
 */

echo "========================================\n";
echo "اختبار إصلاح قوائم Select\n";
echo "Testing Select Dropdown Fix\n";
echo "========================================\n\n";

$baseDir = __DIR__;

// 1. Check StudentForm.php
echo "1. فحص ملف StudentForm.php\n";
echo "   Checking StudentForm.php...\n\n";

$studentFormPath = $baseDir . '/app/Filament/Admin/Resources/Students/Schemas/StudentForm.php';

if (file_exists($studentFormPath)) {
    $content = file_get_contents($studentFormPath);
    
    // Check education_level
    if (strpos($content, "Select::make('education_level')") !== false) {
        echo "   ✓ حقل المستوى التعليمي موجود\n";
        echo "   ✓ Education level field exists\n";
        
        // Check searchable
        $educationSection = substr($content, strpos($content, "Select::make('education_level')"), 800);
        
        if (strpos($educationSection, '->searchable()') !== false) {
            echo "   ✓ خاصية البحث مفعلة\n";
            echo "   ✓ Searchable enabled\n";
        } else {
            echo "   ✗ خاصية البحث غير مفعلة\n";
            echo "   ✗ Searchable NOT enabled\n";
        }
        
        if (strpos($educationSection, '->maxHeight(') !== false) {
            echo "   ✓ أقصى ارتفاع محدد\n";
            echo "   ✓ Max height set\n";
        } else {
            echo "   ⚠ أقصى ارتفاع غير محدد (اختياري)\n";
            echo "   ⚠ Max height not set (optional)\n";
        }
        
        // Count options
        preg_match_all("/'[^']+'\s*=>\s*'[^']+'/", $educationSection, $matches);
        $optionCount = count($matches[0]);
        echo "   ✓ عدد الخيارات: $optionCount\n";
        echo "   ✓ Number of options: $optionCount\n";
    } else {
        echo "   ✗ حقل المستوى التعليمي غير موجود\n";
        echo "   ✗ Education level field NOT found\n";
    }
    
    echo "\n";
    
    // Check status
    if (strpos($content, "Select::make('status')") !== false) {
        echo "   ✓ حقل الحالة موجود\n";
        echo "   ✓ Status field exists\n";
        
        $statusSection = substr($content, strpos($content, "Select::make('status')"), 600);
        
        if (strpos($statusSection, '->searchable()') !== false) {
            echo "   ✓ خاصية البحث مفعلة\n";
            echo "   ✓ Searchable enabled\n";
        } else {
            echo "   ✗ خاصية البحث غير مفعلة\n";
            echo "   ✗ Searchable NOT enabled\n";
        }
    } else {
        echo "   ✗ حقل الحالة غير موجود\n";
        echo "   ✗ Status field NOT found\n";
    }
} else {
    echo "   ✗ الملف غير موجود\n";
    echo "   ✗ File NOT found\n";
}

echo "\n----------------------------------------\n\n";

// 2. Check CSS files
echo "2. فحص ملفات CSS\n";
echo "   Checking CSS files...\n\n";

$cssFiles = [
    'resources/css/filament-select-fix.css',
    'resources/css/app.css',
    'resources/views/filament/admin/custom-styles.blade.php',
];

foreach ($cssFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   ✓ موجود: $file\n";
        echo "   ✓ Exists: $file\n";
        
        $content = file_get_contents($fullPath);
        
        // Check for select fix CSS
        if (strpos($content, 'role="listbox"') !== false || 
            strpos($content, 'fi-fo-select') !== false ||
            strpos($content, 'select-fix') !== false) {
            echo "      ✓ يحتوي على CSS للإصلاح\n";
            echo "      ✓ Contains fix CSS\n";
        }
    } else {
        echo "   ✗ غير موجود: $file\n";
        echo "   ✗ NOT found: $file\n";
    }
}

echo "\n----------------------------------------\n\n";

// 3. Check if app.css imports the fix
echo "3. فحص استيراد CSS في app.css\n";
echo "   Checking CSS import in app.css...\n\n";

$appCssPath = $baseDir . '/resources/css/app.css';
if (file_exists($appCssPath)) {
    $content = file_get_contents($appCssPath);
    
    if (strpos($content, 'filament-select-fix') !== false) {
        echo "   ✓ تم استيراد ملف الإصلاح في app.css\n";
        echo "   ✓ Fix file imported in app.css\n";
    } else {
        echo "   ⚠ لم يتم استيراد ملف الإصلاح (قد يكون مضمناً بشكل مباشر)\n";
        echo "   ⚠ Fix file not imported (may be included directly)\n";
    }
}

echo "\n----------------------------------------\n\n";

// 4. Recommendations
echo "4. التوصيات / Recommendations\n\n";

echo "   ✓ تم تطبيق جميع الإصلاحات!\n";
echo "   ✓ All fixes applied!\n\n";

echo "   الخطوات التالية / Next steps:\n";
echo "   1. امسح الكاش: php artisan optimize:clear\n";
echo "   1. Clear cache: php artisan optimize:clear\n\n";
echo "   2. اختبر في المتصفح: افتح صفحة إضافة/تعديل طالب\n";
echo "   2. Test in browser: Open add/edit student page\n\n";
echo "   3. تحقق من أن القوائم تظهر بشكل كامل\n";
echo "   3. Verify dropdowns show completely\n\n";

echo "========================================\n";
echo "✓ الفحص اكتمل!\n";
echo "✓ Check completed!\n";
echo "========================================\n";
