<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Helper to make cURL requests with cookies
function httpGet($url, $cookieFile) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

function httpLogin($loginUrl, $phone, $password, $cookieFile) {
    // 1. Get CSRF token
    $get = httpGet($loginUrl, $cookieFile);
    preg_match('/name="_token" value="([^"]+)"/', $get['body'], $matches);
    $csrf = $matches[1] ?? null;

    if (!$csrf) {
        // Filament might use wire:initial-data or livewire token
        preg_match('/"csrfToken":"([^"]+)"/', $get['body'], $matches2);
        $csrf = $matches2[1] ?? null;
    }

    // 2. Post login
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        '_token' => $csrf,
        'phone' => $phone,
        'password' => $password,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => $response];
}

// Or generate authenticated session directly via Laravel session store
function generateAuthCookieForUser($userId, $cookieFile) {
    $session = app('session')->driver();
    $session->start();
    $session->put('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $userId);
    $session->save();
    $sessionId = $session->getId();
    
    // Write Netscape format cookie jar
    $cookieContent = "# Netscape HTTP Cookie File\n" .
        "127.0.0.1\tFALSE\t/\tFALSE\t0\tai_school_session\t" . $sessionId . "\n";
    file_put_contents($cookieFile, $cookieContent);
    return $sessionId;
}

$testCookie = sys_get_temp_dir() . '/ai_school_test_cookie.txt';

echo "Testing GET http://127.0.0.1:8000/admin/login ...\n";
$res = httpGet('http://127.0.0.1:8000/admin/login', $testCookie);
echo "Result: HTTP " . $res['code'] . " (Length: " . strlen($res['body']) . " bytes)\n";

if ($res['code'] === 200) {
    echo "SUCCESS: Dev server responds properly to external HTTP requests!\n";
} else {
    echo "ERROR: Server returned " . $res['code'] . "\n";
}
