<?php
// Carga config y mailer (única fuente)
require_once __DIR__ . '/src/lib/env.php';
require_once __DIR__ . '/src/lib/mailer.php';

// Solo aceptar POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo "ok";
  exit;
}

// Datos del formulario
$nombre  = $_POST['nombre']  ?? '';
$email   = $_POST['email']   ?? '';
$telefono= $_POST['telefono']?? '';
$pais    = $_POST['pais']    ?? '';
$ciudad  = $_POST['ciudad']  ?? '';

// Destinatario y asunto
$to       = env_get('MAIL_TO', env_get('MAIL_FROM', 'notificaciones@ingresosai.info'));
$subject  = 'Nuevo registro en ingresosai.info';

// Contenido HTML
$html = "
<h2>Nuevo registro</h2>
<ul>
  <li><b>Nombre:</b> ".htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')."</li>
  <li><b>Email:</b> ".htmlspecialchars($email, ENT_QUOTES, 'UTF-8')."</li>
  <li><b>Teléfono:</b> ".htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8')."</li>
  <li><b>País:</b> ".htmlspecialchars($pais, ENT_QUOTES, 'UTF-8')."</li>
  <li><b>Ciudad:</b> ".htmlspecialchars($ciudad, ENT_QUOTES, 'UTF-8')."</li>
</ul>
";

// Enviar
$ok = send_mail($to, $subject, $html);

// Respuesta
http_response_code($ok ? 200 : 500);
echo $ok ? 'ok' : 'error';
