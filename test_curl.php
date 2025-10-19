<?php
// /httpdocs/test_curl.php
require_once __DIR__ . '/src/lib/env.php';

$SG = env_get('SENDGRID_API_KEY', '');

$urls = [
  'sg_user' => 'https://api.sendgrid.com/v3/user/account',
  'google'  => 'https://www.google.com'
];

foreach ($urls as $name => $url) {
  $ch = curl_init($url);
  $hdrs = [
    'Authorization: Bearer ' . $SG,
    'Content-Type: application/json'
  ];
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
  ]);
  if ($name === 'sg_user' && $SG) curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
  $resp = curl_exec($ch);
  $err  = curl_error($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);

  echo "=== $name ===\n";
  echo "code: " . ($info['http_code'] ?? 0) . "\n";
  echo "err : " . ($err ?: '') . "\n";
  echo "ip  : " . ($info['primary_ip'] ?? 'n/a') . "\n\n";
}
