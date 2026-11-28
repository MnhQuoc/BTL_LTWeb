<section class="hero">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow">ẨM THỰC VIỆT · GIAO TẬN NƠI</p>
            <h1>Món ngon mỗi ngày, <span>giao nhanh trong 30 phút</span></h1>
            <p>Nguyên liệu tươi mới, chế biến theo đơn và giao tận nhà.</p>
            <div class="hero-actions">
                <a class="button" href="/products">Xem thực đơn</a>
                <a class="button button-outline" href="/about">Tìm hiểu FoodStore</a>
            </div>
        </div>
        <div class="hero-card">
            <strong>Combo trưa</strong>
            <b>Chỉ 85.000đ</b>
            <span>Cơm gà + Trà đào</span>
        </div>
    </div>
</section>
<section class="container section">
    <div class="section-heading">
        <div><p class="eyebrow">THỰC ĐƠN</p><h2>Món được yêu thích</h2></div>
        <a href="/products">Xem tất cả →</a>
    </div>
    <div class="product-grid">
        <?php foreach ($featured as $product): ?>
            <article class="product-card">
                <div class="product-image">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php else: ?>
                        <?= e(mb_substr($product['name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="product-body">
                    <h3><a href="/products/<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></h3>
                    <p><?= e($product['short_description']) ?></p>
                    <div class="product-footer">
                        <strong><?= money($product['price']) ?></strong>
                        <?php if ((int) $product['is_available'] === 1 && (int) $product['stock'] > 0): ?>
                            <form method="post" action="/cart/add">
                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="return_to" value="/">
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
</section>
