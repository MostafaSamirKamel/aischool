<?php

// Ensure .env exists from .env.example
$envFile = __DIR__ . '/../.env';
$envExample = __DIR__ . '/../.env.example';
if (!file_exists($envFile) && file_exists($envExample)) {
    @copy($envExample, $envFile);
}

// Ensure register_argc_argv = On in /opt/assets/php.ini (Anybuild edge runtime)
if (file_exists('/opt/assets/php.ini')) {
    $ini = (string) file_get_contents('/opt/assets/php.ini');
    if (str_contains($ini, 'register_argc_argv = Off')) {
        $ini = str_replace('register_argc_argv = Off', 'register_argc_argv = On', $ini);
        file_put_contents('/opt/assets/php.ini', $ini);
    }
}
