<?php

$src = __DIR__ . '/vendor/bezhansalleh/filament-shield/src/Resources/RoleResource.php';
$dest = __DIR__ . '/app/Filament/Resources/Shield/RoleResource.php';

if (!is_dir(dirname($dest))) {
    mkdir(dirname($dest), 0755, true);
}
copy($src, $dest);

$srcDir = __DIR__ . '/vendor/bezhansalleh/filament-shield/src/Resources/RoleResource/Pages';
$destDir = __DIR__ . '/app/Filament/Resources/Shield/RoleResource/Pages';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$files = scandir($srcDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        copy($srcDir . '/' . $file, $destDir . '/' . $file);
    }
}
echo "Copied successfully!";
