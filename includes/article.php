<?php
// Detail článku. Proměnné: $post, $older (starší článek nebo null), $newer (novější článek nebo null)
?>
    <section class="section section-alt">
      <div class="container layout">
        <article class="post">
          <header class="post-header">
            <p class="post-meta"><a href="./">← Všechny články</a></p>
            <h1><?= e($post['title']) ?></h1>
            <p class="post-meta"><time datetime="<?= date('Y-m-d', $post['date']) ?>"><?= czech_date($post['date']) ?></time> · <?= $post['minutes'] ?> min čtení</p>
<?php render('tags.php', ['tags' => $post['tags']]); ?>
          </header>

          <div class="prose-body">
<?= $post['body'] ?>

          </div>

          <aside class="post-contact">
            <p>Chcete se k článku vyjádřit nebo se na něco zeptat? <a href="https://nespor-levova.cz/#kontakt">Napište mi</a>.</p>
          </aside>

          <nav class="post-nav" aria-label="Další články">
<?php if ($older): ?>
            <a class="post-nav-prev" href="<?= $older['url'] ?>"><span>Starší článek</span><?= e($older['title']) ?></a>
<?php endif; ?>
<?php if ($newer): ?>
            <a class="post-nav-next" href="<?= $newer['url'] ?>"><span>Novější článek</span><?= e($newer['title']) ?></a>
<?php endif; ?>
          </nav>
        </article>

<?php render('sidebar.php'); ?>
      </div>
    </section>
