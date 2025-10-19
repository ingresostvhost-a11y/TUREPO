<?php
// registro.php
require_once __DIR__ . '/src/lib/env.php';
require_once __DIR__ . '/src/lib/mailer.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo "ok";
  exit;
}

function clean($v){ return trim((string)$v); }

$nombre   = clean($_POST['nombre']   ?? '');
$email    = clean($_POST['email']    ?? '');
$telefono = clean($_POST['telefono'] ?? '');
$pais     = clean($_POST['pais']     ?? '');
$ciudad   = clean($_POST['ciudad']   ?? '');

if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo 'error';
  exit;
}

// 1) Notificación interna
$toAdmin  = env_get('MAIL_TO', env_get('MAIL_FROM', 'notificaciones@ingresosai.info'));
$subjectA = 'Nuevo registro en ingresosai.info';
$htmlA = "
  <h2>Nuevo registro</h2>
  <ul>
    <li><b>Nombre:</b> ".htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')."</li>
    <li><b>Email:</b> ".htmlspecialchars($email, ENT_QUOTES, 'UTF-8')."</li>
    <li><b>Teléfono:</b> ".htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8')."</li>
    <li><b>País:</b> ".htmlspecialchars($pais, ENT_QUOTES, 'UTF-8')."</li>
    <li><b>Ciudad:</b> ".htmlspecialchars($ciudad, ENT_QUOTES, 'UTF-8')."</li>
  </ul>
";

$okAdmin = send_mail($toAdmin, $subjectA, $htmlA);

// 2) Confirmación al usuario
$subjectU = 'Confirmación de registro — IngresosAI';
$htmlU = "
  <div style='font-family:system-ui,-apple-system,Segoe UI,Roboto;max-width:600px;margin:auto'>
    <h2>¡Gracias por registrarte, ".htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')."!</h2>
    <p>Guardamos tus datos y te esperamos en el webinar.</p>
    <p><a href='https://ingresosai.info/espera.html' style='display:inline-block;padding:12px 18px;background:#ffd400;color:#1a1a1a;border-radius:10px;font-weight:800;text-decoration:none'>Ir a la sala de espera</a></p>
    <hr style='border:none;border-top:1px solid #ddd;margin:20px 0' />
    <p style='font-size:12px;color:#555'>Si no fuiste tú, ignora este correo.</p>
  </div>
";

$okUser = send_mail($email, $subjectU, $htmlU);

// Redirección basada en estado
if ($okAdmin && $okUser) {
  header('Location: /gracias.html', true, 303); // redirect after POST
  exit;
} else {
  http_response_code(502);
  echo 'error';
  exit;
}
