<?php
// --- debug temporal y logging simple ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

function logf($msg) {
  $file = __DIR__ . '/logs/registro.log';
  @file_put_contents($file, '['.date('c')."] ".$msg.PHP_EOL, FILE_APPEND);
}

// Carga env y mailer (rutas relativas a /httpdocs)
require_once __DIR__ . '/feat/src/lib/env.php';
require_once __DIR__ . '/feat/src/lib/mailer.php';

try {
  // Solo aceptar POST
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo 'ok';
    exit;
  }

  // Datos del formulario
  $nombre   = $_POST['nombre']   ?? '';
  $email    = $_POST['email']    ?? '';
  $telefono = $_POST['telefono'] ?? '';
  $pais     = $_POST['pais']     ?? '';
  $ciudad   = $_POST['ciudad']   ?? '';

  // Destinatario y asunto
  $to      = env_get('MAIL_TO', env_get('MAIL_FROM', 'notificaciones@ingresosai.info'));
  $subject = 'Nuevo registro en ingresosai.info';

  // Contenido HTML
  $html = "
    <h2>Nuevo registro</h2>
    <ul>
      <li><b>Nombre:</b> ".htmlspecialchars($nombre)."</li>
      <li><b>Email:</b> ".htmlspecialchars($email)."</li>
      <li><b>Teléfono:</b> ".htmlspecialchars($telefono)."</li>
      <li><b>País:</b> ".htmlspecialchars($pais)."</li>
      <li><b>Ciudad:</b> ".htmlspecialchars($ciudad)."</li>
    </ul>
  ";

  // Envía
  $ok = send_mail($to, $subject, $html);

  if ($ok) {
    echo 'ok';
  } else {
    logf('send_mail devolvió false');
    http_response_code(500);
    echo 'mail_failed';
  }
} catch (Throwable $e) {
  logf('EXCEPTION: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
  http_response_code(500);
  echo 'error';
}
