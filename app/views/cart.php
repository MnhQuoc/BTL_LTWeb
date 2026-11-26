<section class="container page">
    <h1>Giỏ hàng</h1>
    <?php if (!$items): ?>
        <div class="empty-state">
            <p>Giỏ hàng đang trống.</p>
            <a class="button" href="/products">Chọn món ngay</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Món</th><th>Giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['name']) ?></td>
                        <td><?= money($item['price']) ?></td>
                        <td>
                            <form class="quantity-form" method="post" action="/cart/update">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="0" max="20">
                                <button type="submit">Cập nhật</button>
                            </form>
                        </td>
                        <td><?= money($item['price'] * $item['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart-summary">
            <strong>Tổng cộng: <?= money($total) ?></strong>
            <a class="button" href="/checkout">Tiến hành thanh toán</a>
        </div>
    <?php endif; ?>
</section>
