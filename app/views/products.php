<section class="container page">
    <p class="eyebrow">THỰC ĐƠN</p>
    <h1>Sản phẩm</h1>
    <form class="filter-bar" method="get" action="/products">
        <label>Từ khoá
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Tên món...">
        </label>
        <label>Danh mục
            <select name="category">
                <option value="0">Tất cả</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= $category === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Tìm kiếm</button>
    </form>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <div class="product-image">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php else: ?>
                        <?= e(mb_substr($product['name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="product-body">
                    <h2><a href="/products/<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></h2>
                    <p><?= e($product['short_description']) ?></p>
                    <div class="product-footer">
                        <strong><?= money($product['price']) ?></strong>
                        <?php if ((int) $product['is_available'] === 1 && (int) $product['stock'] > 0): ?>
                            <form method="post" action="/cart/add">
                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                                <button type="submit">Thêm</button>
                            </form>
                        <?php else: ?>
                            <span class="status">Tạm hết món</span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (!$products): ?><p class="empty-state">Không tìm thấy món phù hợp.</p><?php endif; ?>
    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Phân trang">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="/products?q=<?= urlencode($search) ?>&category=<?= $category ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
