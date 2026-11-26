<article class="container page post-detail">
    <p class="eyebrow">TIN TỨC</p>
    <h1><?= e($post['title']) ?></h1>
    <p class="lead"><?= e($post['excerpt']) ?></p>
    <p><?= nl2br(e($post['content'])) ?></p>
</article>
