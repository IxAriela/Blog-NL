<?php
// Pravý sloupec: představení, nejnovější články, štítky. Proměnná: $activeTag (zvýrazněný štítek)
$activeTag = $activeTag ?? '';
?>
        <aside class="sidebar">
          <section class="widget widget-about">
            <img src="img/o-mne.webp" alt="Iveta Nešpor Levová" width="96" height="96">
            <h2>Iveta Nešpor Levová</h2>
            <p>Jmenuji se Iveta Nešpor Levová, baví mě weby, technologie a&nbsp;všechno kolem nich. Krom internetu mám ráda knihy, přírodu a&nbsp;focení, kaktusy a&nbsp;sukulenty, koloběžku a&nbsp;občas i&nbsp;nějakou tu aktivnější zábavu. V&nbsp;minulosti mě dostaly i&nbsp;běžecké a&nbsp;OCR závody. Poslední dobou si navíc hodně hraju s&nbsp;AI a&nbsp;stavím vlastní virtuální stáj Ranč snů.</p>
            <a href="https://nespor-levova.cz/" class="card-link">Více o mně&nbsp;→</a>
          </section>

          <section class="widget">
            <h2>Nejnovější články</h2>
            <ul class="widget-posts">
<?php foreach (array_slice(load_posts(), 0, 5) as $recent): ?>
              <li>
                <a href="<?= $recent['url'] ?>"><?= e($recent['title']) ?></a>
                <time datetime="<?= date('Y-m-d', $recent['date']) ?>"><?= czech_date($recent['date']) ?></time>
              </li>
<?php endforeach; ?>
            </ul>
          </section>

          <section class="widget">
            <h2>Štítky</h2>
            <ul class="tag-cloud">
<?php foreach (all_tags() as $tag => $count): ?>
              <li><a href="tag/<?= slugify($tag) ?>/"<?= $tag === $activeTag ? ' aria-current="page"' : '' ?>><?= e($tag) ?> <span><?= $count ?></span></a></li>
<?php endforeach; ?>
            </ul>
          </section>
        </aside>
