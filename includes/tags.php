<?php if ($tags): ?>
              <ul class="post-tags">
<?php foreach ($tags as $tag): ?>
                <li><a href="tag/<?= slugify($tag) ?>/"><?= e($tag) ?></a></li>
<?php endforeach; ?>
              </ul>
<?php endif; ?>
