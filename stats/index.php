<?php
// Přehled návštěvnosti blogu. Otevírá se přes /stats/?k=KLÍČ (klíč je v config.php).

require __DIR__ . '/config.php';
require dirname(__DIR__) . '/includes/functions.php';

$key = (string) ($_GET['k'] ?? '');
if (!hash_equals($STATS_KEY, $key)) {
    http_response_code(404);
    exit('Stránka nenalezena.');
}

$ranges = [7 => '7 dní', 30 => '30 dní', 90 => '90 dní', 365 => 'Rok'];
$days = (int) ($_GET['d'] ?? 30);
if (!isset($ranges[$days])) {
    $days = 30;
}

$today = new DateTimeImmutable('today');
$from = $today->modify('-' . ($days - 1) . ' days');
$monthly = $days > 90;

// Prázdné sloupce pro celé období, aby byly vidět i dny bez návštěv
$buckets = [];
for ($d = $from; $d <= $today; $d = $d->modify('+1 day')) {
    $buckets[$d->format($monthly ? 'Y-m' : 'Y-m-d')] = ['views' => 0, 'visits' => 0];
}

$pages = [];
$sources = [];
$totalViews = 0;
$totalVisits = 0;
$fromStr = $from->format('Y-m-d');

for ($m = $from->modify('first day of this month'); $m <= $today; $m = $m->modify('+1 month')) {
    $file = __DIR__ . '/data/' . $m->format('Y-m') . '.tsv';
    if (!is_file($file)) {
        continue;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        [$date, $path, $source, $entry] = array_pad(explode("\t", $line), 4, '');
        if ($date < $fromStr) {
            continue;
        }
        $bucket = $monthly ? substr($date, 0, 7) : $date;
        if (!isset($buckets[$bucket])) {
            continue;
        }
        $buckets[$bucket]['views']++;
        $pages[$path] = ($pages[$path] ?? 0) + 1;
        $totalViews++;
        if ($entry === '1') {
            $buckets[$bucket]['visits']++;
            $totalVisits++;
            $label = $source === '' ? '' : $source;
            $sources[$label] = ($sources[$label] ?? 0) + 1;
        }
    }
}
arsort($pages);
arsort($sources);

function num($n) { return number_format($n, 0, ',', ' '); }

function page_title($path) {
    return known_pages()[$path] ?? $path;
}

$months = ['led', 'úno', 'bře', 'dub', 'kvě', 'čvn', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'];
function bucket_label($b, $monthly, $months) {
    if ($monthly) {
        return $months[(int) substr($b, 5, 2) - 1] . ' ' . substr($b, 0, 4);
    }
    return (int) substr($b, 8, 2) . '. ' . (int) substr($b, 5, 2) . '.';
}

$maxVisits = max(1, max(array_column($buckets, 'visits')));
// "Hezké" maximum osy: 2, 4, 6, 8, 10, 20, 40… (polovina je vždy celé číslo)
$axisMax = 2;
for ($scale = 1; $axisMax < $maxVisits; $scale *= 10) {
    foreach ([2, 4, 6, 8, 10] as $m) {
        $axisMax = $m * $scale;
        if ($axisMax >= $maxVisits) {
            break;
        }
    }
}
$bucketCount = count($buckets);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Návštěvnost blogu</title>
<link rel="icon" href="<?= e(BASE) ?>img/lvice-fialova.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --violet: #6b2150; --violet-dark: #4a1637; --violet-soft: #efeaee;
    --bar: #8e3a6f; --bar-hover: #4a1637;
    --text: #374151; --muted: #6b6570; --line: rgba(61, 11, 79, 0.12); --grid: rgba(61, 11, 79, 0.08);
  }
  * { box-sizing: border-box; }
  body { margin: 0; font-family: "Inter", sans-serif; color: var(--text); background: var(--violet-soft); line-height: 1.5; }
  .wrap { max-width: 1040px; margin: 0 auto; padding: 32px 16px 64px; }
  header { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; margin-bottom: 24px; }
  h1, h2 { font-family: "Playfair Display", serif; color: var(--violet-dark); margin: 0; }
  h1 { font-size: 2rem; }
  h1 a { color: inherit; text-decoration: none; }
  h2 { font-size: 1.25rem; margin-bottom: 16px; }
  .ranges { display: flex; gap: 6px; flex-wrap: wrap; }
  .ranges a { padding: 7px 16px; border-radius: 999px; background: #fff; color: var(--violet-dark); text-decoration: none; font-size: 14px; font-weight: 600; }
  .ranges a:hover { background: var(--violet-dark); color: #fff; }
  .ranges a[aria-current] { background: var(--violet); color: #fff; }
  .card { background: #fff; padding: 28px; margin-bottom: 20px; box-shadow: 0 18px 40px -26px rgba(74, 22, 55, 0.3); }
  .tiles { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
  .tiles .card { margin: 0; }
  .tile-label { font-size: 14px; font-weight: 600; color: var(--muted); margin: 0 0 4px; }
  .tile-value { font-family: "Playfair Display", serif; font-size: 2.4rem; font-weight: 700; color: var(--violet-dark); margin: 0; line-height: 1.1; }
  .tile-note { font-size: 13px; color: var(--muted); margin: 6px 0 0; }

  .chart { display: grid; grid-template-columns: auto 1fr; gap: 0 10px; }
  .y-axis { display: flex; flex-direction: column; justify-content: space-between; font-size: 12px; color: var(--muted); text-align: right; height: 240px; margin-top: -8px; margin-bottom: -8px; font-variant-numeric: tabular-nums; }
  .plot { position: relative; height: 240px; border-bottom: 1px solid var(--line); }
  .gridline { position: absolute; left: 0; right: 0; border-top: 1px solid var(--grid); }
  .bars { position: absolute; inset: 0; display: flex; align-items: flex-end; gap: 2px; }
  .bar { position: relative; flex: 1 1 0; height: 100%; display: flex; align-items: flex-end; outline: none; }
  .bar span { display: block; width: 100%; background: var(--bar); border-radius: 4px 4px 0 0; }
  .bar:hover span, .bar:focus span { background: var(--bar-hover); }
  .tip { display: none; position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); background: #fff; color: var(--text); border: 1px solid var(--line); box-shadow: 0 10px 30px -12px rgba(0,0,0,.3); padding: 8px 12px; font-size: 13px; white-space: nowrap; z-index: 2; border-radius: 6px; pointer-events: none; }
  .tip strong { display: block; color: var(--violet-dark); }
  .bar:hover .tip, .bar:focus .tip { display: block; }
  .bar:first-child .tip, .bar:nth-child(2) .tip { left: 0; transform: none; }
  .bar:last-child .tip, .bar:nth-last-child(2) .tip { left: auto; right: 0; transform: none; }
  .x-axis { grid-column: 2; display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-top: 6px; }
  details { margin-top: 18px; font-size: 14px; }
  summary { cursor: pointer; color: var(--violet); font-weight: 600; }

  .cols { display: grid; grid-template-columns: 3fr 2fr; gap: 20px; }
  .cols .card { margin: 0; }
  table { width: 100%; border-collapse: collapse; font-size: 15px; }
  th { text-align: left; font-size: 13px; color: var(--muted); font-weight: 600; padding: 0 0 8px; border-bottom: 1px solid var(--line); }
  td { padding: 9px 0; border-bottom: 1px solid var(--line); vertical-align: top; }
  td.n, th.n { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; padding-left: 12px; }
  td a { color: var(--violet-dark); text-decoration: none; }
  td a:hover { text-decoration: underline; }
  .empty { color: var(--muted); margin: 0; }
  .note { font-size: 13px; color: var(--muted); }
  .skip { margin-top: 28px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px; font-size: 14px; }
  .skip button { font: inherit; font-weight: 600; padding: 8px 18px; border-radius: 999px; border: 1.5px solid var(--violet); background: #fff; color: var(--violet); cursor: pointer; }
  .skip button:hover { background: var(--violet); color: #fff; }

  @media (max-width: 760px) {
    .tiles, .cols { grid-template-columns: 1fr; }
    .card { padding: 20px 16px; }
    .plot, .y-axis { height: 180px; }
  }
</style>
</head>
<body>
<div class="wrap">
  <header>
    <h1><a href="<?= e(BASE) ?>">Návštěvnost blogu</a></h1>
    <nav class="ranges" aria-label="Období">
      <?php foreach ($ranges as $d => $label): ?>
        <a href="?k=<?= e($key) ?>&amp;d=<?= $d ?>"<?= $d === $days ? ' aria-current="page"' : '' ?>><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
  </header>

  <div class="tiles">
    <div class="card">
      <p class="tile-label">Návštěvy</p>
      <p class="tile-value"><?= num($totalVisits) ?></p>
      <p class="tile-note">příchody na blog za <?= $days === 365 ? 'poslední rok' : "posledních $days dní" ?></p>
    </div>
    <div class="card">
      <p class="tile-label">Zobrazení stránek</p>
      <p class="tile-value"><?= num($totalViews) ?></p>
      <p class="tile-note">včetně přechodů mezi články</p>
    </div>
    <div class="card">
      <p class="tile-label">Průměrně za den</p>
      <p class="tile-value"><?= num(round($totalVisits / $days)) ?></p>
      <p class="tile-note">návštěv denně</p>
    </div>
  </div>

  <section class="card">
    <h2>Návštěvy <?= $monthly ? 'po měsících' : 'po dnech' ?></h2>
    <div class="chart">
      <div class="y-axis" aria-hidden="true">
        <span><?= num($axisMax) ?></span><span><?= num($axisMax / 2) ?></span><span>0</span>
      </div>
      <div class="plot">
        <div class="gridline" style="top:0"></div>
        <div class="gridline" style="top:50%"></div>
        <div class="bars">
          <?php foreach ($buckets as $b => $v): ?>
            <div class="bar" tabindex="0" aria-label="<?= e(bucket_label($b, $monthly, $months)) ?>: <?= $v['visits'] ?> návštěv, <?= $v['views'] ?> zobrazení">
              <span style="height:<?= round($v['visits'] / $axisMax * 100, 2) ?>%"></span>
              <div class="tip"><strong><?= e(bucket_label($b, $monthly, $months)) ?></strong><?= num($v['visits']) ?> návštěv · <?= num($v['views']) ?> zobrazení</div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="x-axis" aria-hidden="true">
        <?php $keys = array_keys($buckets); ?>
        <span><?= e(bucket_label($keys[0], $monthly, $months)) ?></span>
        <?php if ($bucketCount > 2): ?><span><?= e(bucket_label($keys[intdiv($bucketCount, 2)], $monthly, $months)) ?></span><?php endif; ?>
        <span><?= e(bucket_label(end($keys), $monthly, $months)) ?></span>
      </div>
    </div>
    <details>
      <summary>Zobrazit jako tabulku</summary>
      <table>
        <thead><tr><th><?= $monthly ? 'Měsíc' : 'Den' ?></th><th class="n">Návštěvy</th><th class="n">Zobrazení</th></tr></thead>
        <tbody>
          <?php foreach (array_reverse($buckets, true) as $b => $v): ?>
            <tr><td><?= e(bucket_label($b, $monthly, $months)) ?></td><td class="n"><?= num($v['visits']) ?></td><td class="n"><?= num($v['views']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </details>
  </section>

  <div class="cols">
    <section class="card">
      <h2>Nejčtenější stránky</h2>
      <?php if (!$pages): ?>
        <p class="empty">Zatím žádná data.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Stránka</th><th class="n">Zobrazení</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($pages, 0, 15, true) as $path => $n): ?>
              <tr><td><a href="<?= e(BASE . ltrim($path, '/')) ?>"><?= e(page_title($path)) ?></a></td><td class="n"><?= num($n) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Odkud lidé přišli</h2>
      <?php if (!$sources): ?>
        <p class="empty">Zatím žádná data.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Zdroj</th><th class="n">Návštěvy</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($sources, 0, 15, true) as $src => $n): ?>
              <tr><td><?= $src === '' ? 'Napřímo / neznámý zdroj' : e($src) ?></td><td class="n"><?= num($n) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </div>

  <div class="skip">
    <span id="skip-state"></span>
    <button type="button" id="skip-toggle"></button>
  </div>
  <p class="note">Počítadlo neukládá IP adresy, údaje o prohlížeči ani cookies. Roboti a vyhledávače se nepočítají.
    „Návštěva“ je příchod na blog z jiného webu nebo napřímo, takže jde o orientační číslo, ne o počet unikátních lidí.</p>
</div>
<script>
  // Vlastní návštěvy se dají vypnout pro tento prohlížeč (uloží se jen v něm)
  const state = document.getElementById('skip-state');
  const btn = document.getElementById('skip-toggle');
  const read = () => { try { return localStorage.getItem('stats-skip') === '1'; } catch (e) { return false; } };
  const paint = () => {
    const skip = read();
    state.textContent = skip ? 'Vaše návštěvy z tohoto prohlížeče se nepočítají.' : 'Vaše návštěvy z tohoto prohlížeče se počítají.';
    btn.textContent = skip ? 'Znovu počítat' : 'Nepočítat moje návštěvy';
  };
  btn.addEventListener('click', () => {
    try { read() ? localStorage.removeItem('stats-skip') : localStorage.setItem('stats-skip', '1'); } catch (e) {}
    paint();
  });
  paint();
</script>
</body>
</html>
