<?php
// Nastavení blogu a pomocné funkce. Články se načítají ze složky clanky/.

const SITE_URL = 'https://blog.nespor-levova.cz';
const SITE_TITLE = 'Blog – Iveta Nešpor Levová';
const SITE_DESCRIPTION = 'Můj nepravidelný osobní blog o technologiích, práci i věcech, které mě zrovna zaujmou. Zároveň je malým archivem mojí cesty od knihovnictví k webům.';
const POSTS_DIR = __DIR__ . '/../clanky';

// Adresa složky blogu na serveru: "/" na blog.nespor-levova.cz, "/blog/" na localhost/blog/.
// Díky tomu (a značce <base> v hlavičce) se v HTML píšou cesty bez úvodního lomítka: img/…, css/…
// Počítá se z adresy spuštěného skriptu (index.php nebo stats/…), ne z DOCUMENT_ROOT – ten má
// na hostingu u poddomény nastavenou nadřazenou složku domény.
define('BASE', (function () {
    $root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $scriptDir = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
    $inside = substr($scriptDir, strlen($root));                            // "" nebo "/stats"
    $urlDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'); // "/blog", "/blog/stats", ""
    return rtrim(substr($urlDir, 0, strlen($urlDir) - strlen($inside)), '/') . '/';
})());

date_default_timezone_set('Europe/Prague');
mb_internal_encoding('UTF-8');

function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/**
 * Všechny zveřejněné články, od nejnovějšího. Klíčem je adresa článku (název souboru).
 */
function load_posts()
{
    static $posts = null;
    if ($posts !== null) {
        return $posts;
    }

    $posts = [];
    foreach (glob(POSTS_DIR . '/*.html') as $file) {
        $slug = basename($file, '.html');
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            continue;
        }

        $raw = file_get_contents($file);
        $meta = [];
        $body = $raw;
        if (preg_match('/^\s*<!--(.*?)-->/s', $raw, $m)) {
            foreach (explode("\n", $m[1]) as $line) {
                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $meta[strtolower(trim($key))] = trim($value);
                }
            }
            $body = substr($raw, strlen($m[0]));
        }
        $body = trim($body);

        if (in_array(strtolower($meta['koncept'] ?? ''), ['ano', 'true', '1'], true)) {
            continue;
        }

        $text = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags($body), ENT_QUOTES, 'UTF-8')));
        preg_match('/<img[^>]+src="([^"]+)"/', $body, $img);

        $posts[$slug] = [
            'slug' => $slug,
            'url' => $slug . '/',
            'title' => $meta['nazev'] ?? $slug,
            'date' => strtotime($meta['datum'] ?? '') ?: filemtime($file),
            'tags' => array_values(array_filter(array_map('trim', explode(',', $meta['stitky'] ?? '')))),
            'excerpt' => $meta['perex'] ?? make_excerpt($body),
            'image' => $meta['obrazek'] ?? ($img[1] ?? ''),
            'image_ai' => in_array(strtolower($meta['obrazek-ai'] ?? ''), ['ano', 'true', '1'], true),
            'minutes' => max(1, (int) round(count(preg_split('/\s+/u', $text)) / 200)),
            'body' => $body,
            'modified' => filemtime($file),
        ];
    }

    uasort($posts, function ($a, $b) {
        return $b['date'] <=> $a['date'];
    });
    return $posts;
}

/** Perex = začátek prvního odstavce článku. */
function make_excerpt($body, $limit = 220)
{
    if (!preg_match('/<p>(.*?)<\/p>/s', $body, $m)) {
        return '';
    }
    $text = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    $cut = mb_substr($text, 0, $limit);
    $cut = mb_substr($cut, 0, mb_strrpos($cut, ' '));
    return rtrim($cut, ',.;:–- ') . '…';
}

/** Všechny štítky s počtem článků, nejpoužívanější první. */
function all_tags()
{
    $counts = [];
    foreach (load_posts() as $post) {
        foreach ($post['tags'] as $tag) {
            $counts[$tag] = ($counts[$tag] ?? 0) + 1;
        }
    }
    uksort($counts, function ($a, $b) use ($counts) {
        return $counts[$b] <=> $counts[$a] ?: strcoll($a, $b);
    });
    return $counts;
}

/** Adresy všech stránek blogu a jejich názvy (používá počítadlo návštěv). */
function known_pages()
{
    $pages = ['/' => 'Úvodní stránka'];
    foreach (load_posts() as $post) {
        $pages['/' . $post['url']] = $post['title'];
    }
    foreach (array_keys(all_tags()) as $tag) {
        $pages['/tag/' . slugify($tag) . '/'] = 'Štítek ' . $tag;
    }
    return $pages;
}

/** "Životní cesta" -> "zivotni-cesta" */
function slugify($text)
{
    $text = mb_strtolower($text);
    $text = strtr($text, [
        'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'í' => 'i', 'ň' => 'n', 'ó' => 'o',
        'ř' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z',
        'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ľ' => 'l', 'ĺ' => 'l', 'ŕ' => 'r', 'ô' => 'o',
    ]);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $text), '-');
}

function czech_date($timestamp)
{
    $months = ['ledna', 'února', 'března', 'dubna', 'května', 'června', 'července',
               'srpna', 'září', 'října', 'listopadu', 'prosince'];
    return date('j', $timestamp) . '. ' . $months[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
}

function post_count_label($n)
{
    return $n . ' ' . ($n === 1 ? 'článek' : ($n < 5 ? 'články' : 'článků'));
}

/** Vypíše šablonu z includes/ s danými proměnnými. */
function render($template, array $vars = [])
{
    extract($vars);
    require __DIR__ . '/' . $template;
}
