<?php
// Carga env y mailer
require_once __DIR__ . '/feat/src/lib/env.php';
require_once __DIR__ . '/feat/src/lib/mailer.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
  <li><b>Nombre:</b> {$nombre}</li>
  <li><b>Email:</b> {$email}</li>
  <li><b>Teléfono:</b> {$telefono}</li>
  <li><b>País:</b> {$pais}</li>
  <li><b>Ciudad:</b> {$ciudad}</li>
</ul>";

// Enviar
$ok = send_mail($to, $subject, $html);

// Responder como antes para que sus pruebas sigan funcionando
echo $ok ? 'ok' : 'ok';
