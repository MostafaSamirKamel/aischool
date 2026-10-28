<?php

// Ensure .env exists from .env.example
$envFile = __DIR__ . '/../.env';
$envExample = __DIR__ . '/../.env.example';
if (!file_exists($envFile) && file_exists($envExample)) {
    @copy($envExample, $envFile);
}

// Ensure register_argc_argv = On and variables_order = "EGPCS" in /opt/assets/php.ini (Anybuild edge runtime)
if (file_exists('/opt/assets/php.ini')) {
    $ini = (string) file_get_contents('/opt/assets/php.ini');
    if (!str_contains($ini, 'register_argc_argv = On')) {
        $append = "\n; Edge runtime overrides\nregister_argc_argv = On\nvariables_order = \"EGPCS\"\ndisplay_errors = On\n";
        file_put_contents('/opt/assets/php.ini', $ini . $append);
    }
}

// Prune large non-runtime backup files to keep edge container lightweight (<150MB)
$backupDir = __DIR__ . '/../database/backups';
if (is_dir($backupDir)) {
    foreach (glob($backupDir . '/*.sqlite*') as $f) {
        @unlink($f);
    }
}
