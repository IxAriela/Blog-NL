<?php
// Výpis článků (úvodní stránka a stránky štítků).
// Proměnné: $eyebrow, $heading, $lead, $posts, $activeTag
?>
    <section class="hero-blog">
      <div class="hero-image" aria-hidden="true"></div>
      <div class="container">
        <div class="hero-text">
          <p class="eyebrow"><?= e($eyebrow) ?></p>
          <h1><?= e($heading) ?></h1>
          <p class="lead"><?= e($lead) ?></p>
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container layout">
        <div class="post-list">
<?php foreach ($posts as $post): ?>
          <article class="post-card">
<?php if ($post['image']): ?>
<?php if ($post['image_ai']): ?>
            <a class="post-card-media" href="<?= $post['url'] ?>" tabindex="-1"><img src="<?= e($post['image']) ?>" alt="Ilustrační obrázek vygenerovaný AI" loading="lazy"><span class="ai-badge" aria-hidden="true">Ilustrace · AI</span></a>
<?php else: ?>
            <a class="post-card-media" href="<?= $post['url'] ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($post['image']) ?>" alt="" loading="lazy"></a>
<?php endif; ?>
<?php endif; ?>
            <div class="post-card-body">
              <p class="post-meta"><time datetime="<?= date('Y-m-d', $post['date']) ?>"><?= czech_date($post['date']) ?></time> · <?= $post['minutes'] ?> min čtení</p>
              <h2><a href="<?= $post['url'] ?>"><?= e($post['title']) ?></a></h2>
              <p class="post-excerpt"><?= e($post['excerpt']) ?></p>
<?php render('tags.php', ['tags' => $post['tags']]); ?>
              <a href="<?= $post['url'] ?>" class="card-link">Číst článek&nbsp;→</a>
            </div>
          </article>
<?php endforeach; ?>
        </div>

<?php render('sidebar.php', ['activeTag' => $activeTag]); ?>
      </div>
    </section>
