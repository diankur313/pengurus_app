<?php
header('Content-Type: text/plain');
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $fp = fopen($logFile, 'r');
    $size = filesize($logFile);
    fseek($fp, max(0, $size - 20000));
    $content = fread($fp, 20000);
    fclose($fp);
    // Find last occurrence of [202
    $pos = strrpos($content, '[202');
    if ($pos !== false) {
        echo substr($content, $pos);
    } else {
        echo $content;
    }
} else {
    echo "Log file not found.";
}
