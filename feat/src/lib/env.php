<?php
// Carga variables desde .env y permite leerlas con env_get()

if (!function_exists('env_load')) {
  function env_load(?string $path = null): void {
    // .env dos niveles arriba de src/lib
    $path = $path ?? dirname(__DIR__, 2) . '/.env';
    if (!is_readable($path)) return;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
      $line = trim($line);
      if ($line === '' || $line[0] === '#') continue;

      [$k, $v] = array_pad(explode('=', $line, 2), 2, null);
      if ($k !== null && $v !== null) {
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'"); // quita comillas
        $_ENV[$k] = $v;
        putenv("$k=$v");
      }
    }
  }

  function env_get(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
  }
}

// carga inmediata
env_load();
