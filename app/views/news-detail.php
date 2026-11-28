<section class="container page news-detail">
    <a class="news-back" href="/news">← Quay lại tin tức</a>
    <?php if (!empty($post['image'])): ?><img class="news-detail-image" src="<?= e($post['image']) ?>" alt="<?= e($post['title']) ?>"><?php endif; ?>
    <p class="eyebrow"><?= e(date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at']))) ?> · <?= e($post['author_name'] ?? 'FoodStore') ?></p>
    <h1><?= e($post['title']) ?></h1>
    <?php if (!empty($post['excerpt'])): ?><p class="news-excerpt"><?= e($post['excerpt']) ?></p><?php endif; ?>
    <article class="news-content"><?= nl2br(e($post['content'])) ?></article>

    <section class="comments-section" id="comments">
        <h2>Bình luận (<?= count($comments) ?>)</h2>
        <?php if (isset($_GET['comment_error'])): ?><p class="alert alert-error">Nội dung bình luận tối thiểu 3 ký tự.</p><?php endif; ?>
        <?php if (isset($currentUser)): ?>
            <?php if ($currentUser): ?>
                <form class="form-card comment-form" method="post" action="/news/comment">
                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                    <label>Viết bình luận
                        <textarea name="content" required minlength="3" placeholder="Chia sẻ ý kiến của bạn..."></textarea>
                    </label>
                    <button class="button" type="submit">Gửi bình luận</button>
                </form>
            <?php else: ?>
                <p>Vui lòng <a href="/login"><strong>đăng nhập</strong></a> để bình luận.</p>
            <?php endif; ?>
        <?php endif; ?>
        <div class="comment-list">
            <?php foreach ($comments as $comment): ?>
                <article class="comment-card">
                    <strong><?= e($comment['author_name'] ?? 'Thành viên') ?></strong>
                    <time><?= e(date('d/m/Y H:i', strtotime($comment['created_at']))) ?></time>
                    <p><?= nl2br(e($comment['content'])) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (!$comments): ?><p class="empty-state">Chưa có bình luận.</p><?php endif; ?>
        </div>
    </section>
</section>
