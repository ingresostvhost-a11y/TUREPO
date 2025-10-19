<?php
function log_event($level, $message, $context = []) {
    $ts = date('c');
    $line = json_encode([
        'ts' => $ts,
        'level' => $level,
        'message' => $message,
        'context' => $context
    ], JSON_UNESCAPED_SLASHES);

    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
    error_log($line . PHP_EOL, 3, $logDir . '/mailer.log');
}
