<?php
<?php
require_once dirname(__DIR__) . '/env.php'; // <- en vez de __DIR__ . '/env.php'

function send_mail($to, $subject, $html, $fromEmail1 = null, $fromName1 = null) {
  $apiKey = env_get('SENDGRID_API_KEY');
  if (!$apiKey) return false;

  $fromEmail = $fromEmail1 ?: env_get('MAIL_FROM', 'notificaciones@ingresosai.info');
  $fromName  = $fromName1  ?: env_get('MAIL_FROM_NAME', 'IngresosAI');

  $payload = [
    "personalizations" => [[ "to" => [[ "email" => $to ]] ]],
    "from"  => [ "email" => $fromEmail, "name" => $fromName ],
    "subject" => $subject,
    "content" => [[ "type" => "text/html", "value" => $html ]]
  ];

  $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
  curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . $apiKey,
      'Content-Type: application/json'
    ],
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
  ]);
  curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  return ($code >= 200 && $code < 300);
}
