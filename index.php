<?php
// Blog Ivety Nešpor Levové – všechny adresy webu obsluhuje tento soubor:
//   /                  výpis článků
//   /nazev-clanku/     článek ze souboru clanky/nazev-clanku.html
//   /tag/nazev-stitku/ články se štítkem
//   /sitemap.xml       mapa webu pro vyhledávače

// Lokální náhled (php -S): existující soubory (CSS, obrázky, statistiky…) servíruje přímo server
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) || is_file(rtrim($file, '/') . '/index.php') && $file !== __DIR__ . '/') {
        return false;
    }
}

require __DIR__ . '/includes/functions.php';

// Adresa uvnitř blogu, např. "/webexpo-2022/" (bez případné složky /blog/ na localhostu)
$path = '/' . substr(rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), strlen(BASE));
$posts = load_posts();

// Úvodní stránka
if ($path === '/') {
    render('header.php', ['title' => '', 'path' => '/']);
    render('list.php', [
        'eyebrow' => 'Iveta Nešpor Levová',
        'heading' => 'Blog',
        'lead' => SITE_DESCRIPTION,
        'posts' => $posts,
        'activeTag' => '',
    ]);
    render('footer.php');
    exit;
}

// Mapa webu
if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo '<url><loc>' . SITE_URL . '/</loc></url>' . "\n";
    foreach ($posts as $post) {
        echo '<url><loc>' . SITE_URL . '/' . $post['url'] . '</loc><lastmod>' . date('Y-m-d', $post['modified']) . '</lastmod></url>' . "\n";
    }
    foreach (array_keys(all_tags()) as $tag) {
        echo '<url><loc>' . SITE_URL . '/tag/' . slugify($tag) . '/</loc></url>' . "\n";
    }
    echo '</urlset>' . "\n";
    exit;
}

// Stránka štítku
if (preg_match('#^/tag/([a-z0-9-]+)/$#', $path, $m)) {
    foreach (array_keys(all_tags()) as $tag) {
        if (slugify($tag) === $m[1]) {
            $tagged = array_filter($posts, function ($post) use ($tag) {
                return in_array($tag, $post['tags'], true);
            });
            render('header.php', ['title' => 'Štítek ' . $tag, 'path' => $path]);
            render('list.php', [
                'eyebrow' => 'Štítek',
                'heading' => $tag,
                'lead' => post_count_label(count($tagged)) . ' se štítkem ' . $tag . '.',
                'posts' => $tagged,
                'activeTag' => $tag,
            ]);
            render('footer.php');
            exit;
        }
    }
}

// Článek
if (preg_match('#^/([a-z0-9-]+)/$#', $path, $m) && isset($posts[$m[1]])) {
    $slugs = array_keys($posts);
    $i = array_search($m[1], $slugs, true);
    $post = $posts[$m[1]];
    render('header.php', [
        'title' => $post['title'],
        'description' => $post['excerpt'],
        'path' => '/' . $post['url'],
        'image' => $post['image'],
    ]);
    render('article.php', [
        'post' => $post,
        'newer' => $i > 0 ? $posts[$slugs[$i - 1]] : null,
        'older' => $i < count($slugs) - 1 ? $posts[$slugs[$i + 1]] : null,
    ]);
    render('footer.php');
    exit;
}

// Adresa bez lomítka na konci -> přesměrovat na verzi s lomítkem
if (preg_match('#^/(?:tag/)?[a-z0-9-]+$#', $path)) {
    header('Location: ' . BASE . ltrim($path, '/') . '/', true, 301);
    exit;
}

// Nic nenalezeno
http_response_code(404);
render('header.php', ['title' => 'Stránka nenalezena', 'path' => $path]);
render('not-found.php');
render('footer.php');
