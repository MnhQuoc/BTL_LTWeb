<section class="container page">
    <div class="detail-grid">
        <div class="detail-image">
            <?php if (!empty($product['image'])): ?>
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <?= e(mb_substr($product['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div>
            <p class="eyebrow">CHI TIẾT MÓN ĂN</p>
            <h1><?= e($product['name']) ?></h1>
            <p><?= e($product['description']) ?></p>
            <?php if ((int) $product['is_available'] !== 1 || (int) $product['stock'] === 0): ?>
                <p class="status">Tạm hết món</p>
            <?php endif; ?>
            <strong class="detail-price"><?= money($product['price']) ?></strong>
            <?php if ((int) $product['is_available'] === 1 && (int) $product['stock'] > 0): ?>
                <form class="inline-form" method="post" action="/cart/add">
                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="return_to" value="<?= e('/products/' . rawurlencode($product['slug'])) ?>">
                    <label>Số lượng
                        <input type="number" name="quantity" value="1" min="1" max="20" required>
                    </label>
                    <button class="button" type="submit">Thêm vào giỏ</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
