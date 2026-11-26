<section class="container page">
    <p class="eyebrow">TIN TỨC & KHUYẾN MÃI</p>
    <h1>Tin tức</h1>
    <form class="filter-bar" method="get" action="/news">
        <label>Tìm bài viết
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Từ khoá...">
        </label>
        <button class="button" type="submit">Tìm</button>
    </form>
    <div class="post-grid">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <h2><a href="/news/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                <p><?= e($post['excerpt']) ?></p>
                <a href="/news/<?= e($post['slug']) ?>">Đọc tiếp →</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
