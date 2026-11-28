<section class="container page admin-page">
    <p class="eyebrow">QUẢN TRỊ BÀI VIẾT</p>
    <h1><?= $post ? 'Sửa bài viết' : 'Thêm bài viết' ?></h1>
    <?php if (!empty($error)): ?><p class="alert alert-error"><?= e($error) ?></p><?php endif; ?>
    <form class="form-card post-form" method="post" action="/admin/posts/save">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) ($post['id'] ?? 0) ?>">
        <label>Tiêu đề
            <input type="text" name="title" value="<?= e($post['title'] ?? '') ?>" required minlength="3">
        </label>
        <label>Tóm tắt
            <textarea name="excerpt"><?= e($post['excerpt'] ?? '') ?></textarea>
        </label>
        <label>Nội dung
            <textarea name="content" class="post-content" required minlength="10"><?= e($post['content'] ?? '') ?></textarea>
        </label>
        <label>Đường dẫn ảnh
            <input type="text" name="image" value="<?= e($post['image'] ?? '') ?>" placeholder="/assets/images/...">
        </label>
        <label class="checkbox-label">
            <input type="checkbox" name="is_published" value="1" <?= (int) ($post['is_published'] ?? 1) ? 'checked' : '' ?>>
            Công khai bài viết
        </label>
        <div class="seller-product-actions">
            <button class="button" type="submit">Lưu bài viết</button>
            <a class="button button-outline" href="/admin/posts">Hủy</a>
        </div>
    </form>
</section>
