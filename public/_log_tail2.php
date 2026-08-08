<?php
header('Content-Type: text/plain');
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $fp = fopen($logFile, 'r');
    $size = filesize($logFile);
    fseek($fp, max(0, $size - 5000));
    echo fread($fp, 5000);
    fclose($fp);
} else {
    echo "Log file not found.";
}
