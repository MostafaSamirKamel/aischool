<?php
$m = json_decode(file_get_contents(__DIR__ . '/ui_discovery_manifest.json'), true);
foreach ($m['routes']['admin'] as $r) {
    if (str_contains($r['uri'], 'create') || str_contains($r['uri'], 'edit')) continue;
    echo $r['uri'] . "\n";
}
