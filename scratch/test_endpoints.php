<?php
$urls = [
    'Admin Login' => 'http://127.0.0.1:8000/admin/login',
    'Instructor Login' => 'http://127.0.0.1:8000/instructor/login',
    'Supervisor Login' => 'http://127.0.0.1:8000/supervisor/login',
    'Student Login' => 'http://127.0.0.1:8000/student/login',
    'Parent Login' => 'http://127.0.0.1:8000/parent/login',
];
foreach ($urls as $name => $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $hdr = @get_headers($url, 1, $ctx);
    $status = $hdr ? (is_array($hdr[0]) ? $hdr[0][0] : $hdr[0]) : 'FAILED TO CONNECT';
    echo "{$name} [{$url}]: {$status}" . PHP_EOL;
}
