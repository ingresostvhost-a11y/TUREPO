<?php
// /httpdocs/src/lib/mailer.php
require_once __DIR__ . '/env.php';

function send_mail(string $to, string $subject, string $html): bool {
  $apiKey   = env_get('SENDGRID_API_KEY', '');
  $from     = env_get('MAIL_FROM', 'notificaciones@ingresosai.info');
  $fromName = env_get('MAIL_FROM_NAME', 'IngresosAI');

  if ($apiKey === '' || $from === '') {
    app_log('mailer', 'FALTAN_ENV', [
      'apiKey' => $apiKey !== '',
      'from'   => $from !== ''
    ]);
    return false;
  }

  $payload = [
    "personalizations" => [[ "to" => [[ "email" => $to ]] ]],
    "from"             => [ "email" => $from, "name" => $fromName ],
    "subject"          => $subject,
    "content"          => [[ "type" => "text/html", "value" => $html ]]
  ];

  $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
  curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_HTTPHEADER     => [
      'Authorization: Bearer ' . $apiKey,
      'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
  ]);

  $resp = curl_exec($ch);
  $err  = curl_error($ch);
  $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);

  // Log resumido de la respuesta
  app_log('mailer', 'RESP', [
    'http_code' => $code,
    'curl_err'  => $err,
    'resp_snip' => substr((string)$resp, 0, 1000),
    'to'        => $to,
    'from'      => $from,
    'subject'   => $subject
  ]);

  return $code === 202; // SendGrid OK
}
