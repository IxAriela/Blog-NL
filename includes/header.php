<?php
// Hlavička každé stránky. Proměnné: $title, $description, $path (adresa stránky), $image (obrázek pro sdílení)
$fullTitle = $title ? $title . ' – Blog Ivety Nešpor Levové' : SITE_TITLE;
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="<?= e(BASE) ?>">
  <title><?= e($fullTitle) ?></title>
  <meta name="description" content="<?= e($description ?? SITE_DESCRIPTION) ?>">
  <link rel="canonical" href="<?= e(SITE_URL . ($path ?? '/')) ?>">
  <meta property="og:title" content="<?= e($fullTitle) ?>">
  <meta property="og:description" content="<?= e($description ?? SITE_DESCRIPTION) ?>">
  <meta property="og:url" content="<?= e(SITE_URL . ($path ?? '/')) ?>">
  <meta property="og:locale" content="cs_CZ">
<?php if (!empty($image)): ?>
  <meta property="og:image" content="<?= e(SITE_URL . '/' . ltrim($image, '/')) ?>">
<?php endif; ?>
  <link rel="icon" href="img/lvice-fialova.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a href="./" class="logo" aria-label="Blog Ivety Nešpor Levové – úvod">
        <img src="img/lvice-fialova.svg" alt="Logo – lvice" width="150" height="72">
        <span class="logo-label">Blog</span>
      </a>
      <a href="https://nespor-levova.cz/#kontakt" class="header-link">Kontakt</a>
    </div>
  </header>

  <main>
