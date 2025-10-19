<?php
// src/lib/env.php
if (!function_exists('env_load')) {
    function env_load(?string $path = null): void {
        // Por defecto: /httpdocs/.env (document root en Plesk)
        $docroot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
        $path = $path ?: rtrim($docroot, '/') . '/.env';
        if (!is_readable($path)) return;

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;

            [$k, $v] = array_pad(explode('=', $line, 2), 2, null);
            if ($k !== null && $v !== null) {
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                $_ENV[$k] = $v;
                putenv("$k=$v");
            }
        }
    }
}

if (!function_exists('env_get')) {
    function env_get(string $key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

// Carga inmediata
env_load();
