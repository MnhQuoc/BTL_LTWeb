<section class="container page">
    <h1><?= $product['id'] ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form class="form-card" method="post" action="/seller/products/save" enctype="multipart/form-data" data-validate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
        <label>Tên sản phẩm
            <input type="text" name="name" value="<?= e($product['name']) ?>" maxlength="150" required>
        </label>
        <label>Slug (chữ thường, số và dấu gạch ngang)
            <input type="text" name="slug" value="<?= e($product['slug']) ?>" maxlength="180" pattern="[a-z0-9]+(-[a-z0-9]+)*" required>
        </label>
        <label>Danh mục
            <select name="category_id">
                <option value="0">Chưa phân loại</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Mô tả ngắn
            <input type="text" name="short_description" value="<?= e($product['short_description']) ?>" maxlength="255">
        </label>
        <label>Mô tả chi tiết
            <textarea name="description"><?= e($product['description']) ?></textarea>
        </label>
        <label>Ảnh món ăn (JPG, PNG hoặc WebP, tối đa 5 MB)
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        </label>
        <?php if (!empty($product['image'])): ?>
            <div class="seller-image-preview">
                <span>Ảnh hiện tại</span>
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            </div>
        <?php endif; ?>
        <label>Giá (VND)
            <input type="number" name="price" value="<?= e($product['price']) ?>" min="1" step="1" required>
        </label>
        <label>Tồn kho
            <input type="number" name="stock" value="<?= e($product['stock']) ?>" min="0" step="1" required>
        </label>
        <div>
            <button class="button" type="submit">Lưu sản phẩm</button>
            <a class="button button-outline" href="/seller">Huỷ</a>
        </div>
    </form>
</section>
