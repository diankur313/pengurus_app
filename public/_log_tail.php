<?php
header('Content-Type: text/plain');
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $fp = fopen($logFile, 'r');
    fseek($fp, -5000, SEEK_END);
    echo fread($fp, 5000);
    fclose($fp);
} else {
    echo "Log file not found.";
}
