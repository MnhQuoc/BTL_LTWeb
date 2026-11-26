<section class="container page">
    <div class="detail-grid">
        <div class="detail-image"><?= mb_substr($product['name'], 0, 1) ?></div>
        <div>
            <p class="eyebrow">CHI TIẾT MÓN ĂN</p>
            <h1><?= e($product['name']) ?></h1>
            <p><?= e($product['description']) ?></p>
            <strong class="detail-price"><?= money($product['price']) ?></strong>
            <form class="inline-form" method="post" action="/cart/add">
                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                <label>Số lượng
                    <input type="number" name="quantity" value="1" min="1" max="20" required>
                </label>
                <button class="button" type="submit">Thêm vào giỏ</button>
            </form>
        </div>
    </div>
</section>
