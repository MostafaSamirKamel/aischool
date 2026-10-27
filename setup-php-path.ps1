# Laravel Helper - Add PHP to PATH permanently
# تشغيل هذا الملف مرة واحدة فقط

Write-Host "Adding PHP to System PATH..." -ForegroundColor Yellow

$phpPath = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64"

# Get current PATH
$currentPath = [Environment]::GetEnvironmentVariable("Path", "User")

# Check if already exists
if ($currentPath -like "*$phpPath*") {
    Write-Host "PHP is already in PATH!" -ForegroundColor Green
} else {
    # Add to PATH
    $newPath = "$phpPath;$currentPath"
    [Environment]::SetEnvironmentVariable("Path", $newPath, "User")
    
    Write-Host "✅ PHP added to PATH successfully!" -ForegroundColor Green
    Write-Host "⚠️  Please restart PowerShell for changes to take effect" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "After restart, you can use:" -ForegroundColor Cyan
Write-Host "  php artisan migrate" -ForegroundColor White
Write-Host "  php artisan tinker" -ForegroundColor White
Write-Host "  php artisan queue:work" -ForegroundColor White
