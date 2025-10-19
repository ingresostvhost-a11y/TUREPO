<?php
require_once __DIR__ . '/src/lib/logger.php';
header('Content-Type: text/plain; charset=utf-8');

$rid = bin2hex(random_bytes(8));

function mask_email($e){
  if (!filter_var($e, FILTER_VALIDATE_EMAIL)) return 'invalid';
  [$u,$d] = explode('@',$e,2);
  return substr($u,0,2) . str_repeat('*', max(0, strlen($u)-2)) . '@' . $d;
}

$nombre   = trim($_POST['nombre']  ?? '');
$email    = trim($_POST['email']   ?? '');
$telefono = trim($_POST['telefono']?? '');
$pais     = trim($_POST['pais']    ?? '');
$ciudad   = trim($_POST['ciudad']  ?? '');

if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  log_event('warn','lead.validation_failed',[
    'rid'=>$rid,
    'email'=>mask_email($email)
  ]);
  http_response_code(422);
  echo 'error';
  exit;
}

log_event('info','mail.attempt',[
  'rid'=>$rid,
  'email'=>mask_email($email),
  'ip'=>($_SERVER['REMOTE_ADDR'] ?? ''),
  'ua'=>($_SERVER['HTTP_USER_AGENT'] ?? '')
]);

$ok = false;

try {
  // TODO: integrar SendGrid/SMTP con timeout y retry 1x.
  $ok = true; // placeholder de éxito temporal
} catch (Throwable $e) {
  log_event('error','mail.exception', [ 'rid'=>$rid, 'ex'=>$e->getMessage() ]);
}

if ($ok) {
  log_event('info','mail.success', [ 'rid'=>$rid ]);
  http_response_code(200);
  echo 'ok';
} else {
  log_event('error','mail.error', [ 'rid'=>$rid, 'reason'=>'provider_rejected' ]);
  http_response_code(502);
  echo 'error';
}
