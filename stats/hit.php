<?php
// Zaznamená jedno zobrazení stránky blogu.
// Neukládá IP adresu, prohlížeč ani cookies – jen datum, stránku a web, ze kterého návštěvník přišel.

date_default_timezone_set('Europe/Prague');
http_response_code(204);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|headless|lighthouse|fetch/i', $ua)) {
    exit;
}

// Počítají se jen existující stránky blogu (úvod, články, štítky)
require dirname(__DIR__) . '/includes/functions.php';
$path = (string) ($_POST['p'] ?? '');
if (strpos($path, BASE) === 0) {
    $path = '/' . substr($path, strlen(BASE));
}
if (!array_key_exists($path, known_pages())) {
    exit;
}

// Odkud návštěvník přišel (jen doména). Příchod z jiného webu nebo napřímo = nová návštěva.
$ownHost = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
$refHost = strtolower((string) parse_url($_POST['r'] ?? '', PHP_URL_HOST));
$internal = $refHost !== '' && $refHost === $ownHost;
$source = '';
if (!$internal && preg_match('/^[a-z0-9.-]{1,100}$/', $refHost)) {
    $source = preg_replace('/^www\./', '', $refHost);
}

$file = __DIR__ . '/data/' . date('Y-m') . '.tsv';
if (is_file($file) && filesize($file) > 20 * 1024 * 1024) {
    exit; // pojistka proti zahlcení disku
}

$line = implode("\t", [date('Y-m-d'), $path, $source, $internal ? '0' : '1']) . "\n";
file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
