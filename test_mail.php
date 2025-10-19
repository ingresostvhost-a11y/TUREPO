<?php
// /httpdocs/test_mail.php
require_once __DIR__ . '/src/lib/env.php';
require_once __DIR__ . '/src/lib/mailer.php';

$to = env_get('MAIL_TO', 'cashleads@hotmail.com');
$ok = send_mail($to, 'Prueba IngresosAI', '<p>ok</p>');

echo $ok ? "ok\n" : "fail\n";
