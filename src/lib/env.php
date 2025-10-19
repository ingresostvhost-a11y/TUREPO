<?php
// /httpdocs/src/lib/env.php

// Cache en memoria para variables de entorno
static $ENV_CACHE = null;

function __env_load(): array {
  global $ENV_CACHE;
  if ($ENV_CACHE !== null) return $ENV_CACHE;

  $ENV_CACHE = [];
  $envFile = dirname(__DIR__, 2) . '/.env'; // /httpdocs/.env

  if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '' || str_starts_with($line, '#')) continue;
      $parts = explode('=', $line, 2);
      if (count($parts) !== 2) continue;
      [$k, $v] = $parts;
      $k = trim($k);
      $v = trim($v);
      // Quita comillas si vienen "..." o '...'
      if ((str_starts_with($v, '"') && str_ends_with($v, '"')) ||
          (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
        $v = substr($v, 1, -1);
      }
      $ENV_CACHE[$k] = $v;
    }
  }

  return $ENV_CACHE;
}

function env_get(string $key, $default = '') {
  $env = __env_load();
  return array_key_exists($key, $env) ? $env[$key] : $default;
}

function app_log(string $channel, string $event, array $data = []): void {
  $logFile = dirname(__DIR__, 2) . '/logs/registro.log'; // /httpdocs/logs/registro.log
  $line = sprintf(
    "[%s] %s.%s %s\n",
    date('c'),
    $channel,
    $event,
    json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
  );
  @file_put_contents($logFile, $line, FILE_APPEND);
}
