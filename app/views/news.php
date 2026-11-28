<section class="container page">
    <p class="eyebrow">TIN TỨC &amp; BÀI VIẾT</p>
    <h1>Tin tức FoodStore</h1>
    <form class="filter-bar" method="get" action="/news">
        <label>Tìm bài viết
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Nhập tiêu đề hoặc nội dung...">
        </label>
        <button class="button" type="submit">Tìm kiếm</button>
    </form>
    <div class="news-grid">
        <?php foreach ($posts as $post): ?>
            <article class="news-card">
                <?php if (!empty($post['image'])): ?>
                    <img class="news-card-image" src="<?= e($post['image']) ?>" alt="<?= e($post['title']) ?>">
                <?php else: ?>
                    <div class="news-card-placeholder"><?= e(mb_substr($post['title'], 0, 1)) ?></div>
                <?php endif; ?>
                <div class="news-card-body">
                    <time><?= e(date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at']))) ?></time>
                    <h2><a href="/news/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                    <p><?= e($post['excerpt'] ?: mb_substr(strip_tags($post['content']), 0, 150)) ?></p>
                    <a class="news-read-more" href="/news/<?= e($post['slug']) ?>">Đọc tiếp →</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (!$posts): ?><p class="empty-state">Không tìm thấy bài viết phù hợp.</p><?php endif; ?>
    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Phân trang">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="/news?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
