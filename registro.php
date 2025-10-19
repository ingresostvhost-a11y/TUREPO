<?php
require_once __DIR__ . '/src/lib/env.php';
require_once __DIR__ . '/src/lib/mailer.php';


header('Content-Type: text/plain; charset=UTF-8');

// --- Loader robusto: toma el primer archivo que exista
function require_first(array $candidates) {
  foreach ($candidates as $p) {
    $abs = __DIR__ . '/' . ltrim($p, '/');
    if (file_exists($abs)) { require_once $abs; return $abs; }
  }
  http_response_code(500);
  echo "bootstrap error: no se encontró ninguno de: " . implode(', ', $candidates);
  exit;
}

// Carga env y mailer desde la primera ruta disponible (según cómo quedó el deploy)
require_first(['src/lib/env.php',   'feat/src/lib/env.php']);
require_first(['src/lib/mailer.php','feat/src/lib/mailer.php']);

// Logger básico a /logs/registro.log (no bloquea si falla)
function logf($msg) {
  @file_put_contents(__DIR__ . '/logs/registro.log', '['.date('c')."] $msg\n", FILE_APPEND);
}

// --- Solo aceptar POST (mantengo tu comportamiento de devolver 405 y 'ok')
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo 'ok';
  exit;
}

// --- Datos del formulario
$nombre   = trim($_POST['nombre']   ?? '');
$email    = trim($_POST['email']    ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$pais     = trim($_POST['pais']     ?? '');
$ciudad   = trim($_POST['ciudad']   ?? '');

// --- Destinatario y asunto
$to = env_get('MAIL_TO') ?: (env_get('MAIL_FROM') ?: 'notificaciones@ingresosai.info');
$subject = 'Nuevo registro en ingresosai.info';

// --- Contenido HTML (simple)
$esc = fn($s) => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$html = "
  <h1>Nuevo registro</h1>
  <ul>
    <li><strong>Nombre:</strong> {$esc($nombre)}</li>
    <li><strong>Email:</strong> {$esc($email)}</li>
    <li><strong>Teléfono:</strong> {$esc($telefono)}</li>
    <li><strong>País:</strong> {$esc($pais)}</li>
    <li><strong>Ciudad:</strong> {$esc($ciudad)}</li>
  </ul>
";

// --- Envío
$ok = send_mail($to, $subject, $html);

// --- Respuesta HTTP + log
if ($ok) {
  http_response_code(200);
  echo 'ok';
  logf("OK: {$email} {$nombre} {$telefono} {$pais} {$ciudad}");
} else {
  http_response_code(500);
  echo 'error';
  logf("ERROR: fallo al enviar - {$email} {$nombre}");
}
