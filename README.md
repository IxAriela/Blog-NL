# Blog – blog.nespor-levova.cz

Články jsou obyčejné HTML soubory ve složce `clanky/`. PHP kolem nich doplní hlavičku, patičku,
výpis článků, štítky, přepínání mezi články, dobu čtení a sitemapu. Žádná databáze, žádná
administrace, nic se negeneruje – co je ve složce, to se nahraje na server.

## Struktura

```
clanky/            články – jeden soubor = jeden článek (nazev-clanku.html -> /nazev-clanku/)
includes/          části stránky: header.php, footer.php, list.php (výpis), article.php (článek),
                   sidebar.php (pravý sloupec: představení, nejnovější články, štítky)
css/style.css      vzhled (barvy v :root nahoře vycházejí z nespor-levova.cz)
img/klavesnice.webp, img/o-mne.webp   obrázek v úvodu a fotka v pravém sloupci
js/main.js         mobilní menu, zvětšení fotek, počítadlo návštěv
img/               obrázky, tříděné podle roku a měsíce
stats/             počítadlo návštěvnosti
index.php          podle adresy zobrazí výpis, článek, štítek nebo sitemapu
.htaccess          hezké adresy + přesměrování starých adres z WordPressu
```

## Nový článek

Vytvořte soubor `clanky/nazev-clanku.html`. Název souboru = adresa článku
(jen malá písmena bez diakritiky, číslice a pomlčky). Nahoře je krátký popis v komentáři:

```html
<!--
nazev: Název článku
datum: 2026-10-01
stitky: IT, Konference
-->

<p>První odstavec se použije jako perex ve výpisu.</p>

<h2>Mezititulek</h2>
<p>Text článku…</p>
```

Volitelné řádky v komentáři:

- `perex: …` – vlastní perex do výpisu (jinak začátek prvního odstavce)
- `obrazek: img/2026/10/fotka.jpg` – obrázek do výpisu a pro sdílení (jinak první obrázek v článku)
- `obrazek-ai: ano` – obrázek je ilustrace vygenerovaná AI; na kartě ve výpisu dostane štítek „Ilustrace · AI“
- `koncept: ano` – článek se nikde nezobrazí, dokud řádek nesmažete
- `datum: 2026-10-01 14:30` – čas se hodí, když vyjdou dva články ve stejný den

Štítky se píšou oddělené čárkou; stránka štítku (`/tag/it/`) vznikne sama.

### Fotka s popiskem

```html
<figure class="post-figure">
  <a href="img/2026/10/fotka.jpg" class="zoom"><img src="img/2026/10/fotka.jpg" alt="Popis fotky" loading="lazy"></a>
  <figcaption>Popisek pod fotkou</figcaption>
</figure>
```

### Galerie

```html
<div class="gallery">
  <figure class="gallery-item">
    <a href="img/2026/10/a.jpg" class="zoom"><img src="img/2026/10/a.jpg" alt="…" loading="lazy"></a>
    <figcaption>Popisek (nepovinný)</figcaption>
  </figure>
  <figure class="gallery-item">…</figure>
</div>
```

Cesty k obrázkům i odkazy v rámci blogu pište **bez lomítka na začátku** (`img/…`, `tag/it/`,
`nazev-clanku/`). Značka `<base>` v hlavičce zajistí, že fungují na serveru i na `localhost/blog/`.

Galerie má 3 sloupce, `<div class="gallery gallery-4">` má 4 (na mobilu vždy 2).

Odkaz s třídou `zoom` otevře fotku ve zvětšení (šipkami se listuje). Fotky před nahráním
zmenšete zhruba na 1600 px na delší straně.

### Obtékaná fotka

Fotku dejte **před** odstavec, který ji má obtékat, a přidejte třídu `float-right` (nebo `float-left`).
Obtékání skončí u nejbližšího nadpisu, čáry `<hr>`, galerie nebo řady fotek. Na mobilu je fotka pod textem na celou šířku.

```html
<figure class="post-figure float-right">
  <a href="img/2026/10/fotka.jpg" class="zoom"><img src="img/2026/10/fotka.jpg" alt="…" loading="lazy"></a>
  <figcaption>Popisek</figcaption>
</figure>
<p>Text, který fotku obtéká…</p>
```

### Fotky vedle sebe

Na rozdíl od galerie se fotky neořezávají do čtverců. Kolik fotek, tolik stejně širokých sloupců.

```html
<div class="figure-row">
  <figure class="post-figure">…</figure>
  <figure class="post-figure">…</figure>
</div>
```

### Obsah ve sloupcích

Tři sloupce (na mobilu dva), každý `<div>` je jeden sloupec.

```html
<div class="columns">
  <div>
    <h4>Nadpis</h4>
    <ul><li>…</li></ul>
  </div>
  <div>…</div>
</div>
```

### Video z YouTube

```html
<div class="video">
  <iframe src="https://www.youtube-nocookie.com/embed/ID-VIDEA" title="Video na YouTube" loading="lazy" allowfullscreen></iframe>
</div>
```

## Náhled u sebe

Spusťte Apache v XAMPP Control Panelu a otevřete **http://localhost/blog/**.
Změny v článcích, šablonách i CSS uvidíte po obnovení stránky (F5).

## Nahrání na server

Nahrajte obsah složky přes FTP do kořene subdomény `blog.nespor-levova.cz`, včetně skrytých
souborů `.htaccess`. Hosting musí umět PHP (7.4 nebo novější) a Apache s `mod_rewrite`.

Pozor: na serveru ve složce `stats/data/` přibývají soubory s návštěvností. Při nahrávání
nepoužívejte volbu „smazat soubory, které na serveru navíc“ (synchronizaci), jinak by se smazaly.

### Git a veřejný repozitář

Soubor `stats/config.php` (klíč ke statistikám) a data ve `stats/data/` se do gitu nenahrávají
(viz `.gitignore`). Na serveru je potřeba `config.php` jednou vytvořit ručně podle
`stats/config.example.php`. Při nahrávání z gitu se na něj ani na data nesahá, protože je git nezná.
Rozepsané články, které nemají být vidět ani v repozitáři, patří do složky `koncepty/`.

## Návštěvnost

Přehled: `https://blog.nespor-levova.cz/stats/?k=KLÍČ` – klíč je v `stats/config.php`.
Kdo adresu zná, vidí statistiky; pro změnu stačí přepsat klíč v `config.php`.

- Ukládá se jen datum, stránka a doména, odkud návštěvník přišel. Žádné IP adresy, údaje
  o prohlížeči ani cookies, proto není potřeba cookie lišta. Roboti se nepočítají.
- „Návštěva“ = příchod z jiného webu nebo napřímo; proklikávání mezi články se počítá jen do zobrazení.
- Na stránce statistik je tlačítko **Nepočítat moje návštěvy** – použijte ho v každém prohlížeči,
  kde blog čtete.
- Při lokálním náhledu (`localhost`) se nic nepočítá.

## Staré adresy z WordPressu

Adresy článků i štítků zůstaly stejné. `.htaccess` přesměruje staré RSS (`/feed/`), kategorie,
autory a obrázky z `/wp-content/uploads/…`; `/kontakt/` vede na formulář na nespor-levova.cz,
`/ochrana-osobnich-udaju/` na úvod blogu.
