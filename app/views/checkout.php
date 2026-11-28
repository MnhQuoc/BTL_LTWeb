<section class="container page">
    <h1>Thông tin thanh toán</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <div class="checkout-grid">
        <form class="form-card" method="post" action="/checkout" data-validate>
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <label>Người nhận<input type="text" name="name" value="<?= e($checkoutData['name']) ?>" minlength="2" maxlength="100" required></label>
            <label>Số điện thoại<input type="tel" name="phone" value="<?= e($checkoutData['phone']) ?>" pattern="[0-9]{8,15}" required></label>
            <label>Địa chỉ giao hàng<textarea name="address" minlength="10" maxlength="255" required><?= e($checkoutData['address']) ?></textarea></label>
            <label class="checkbox-label">
                <input type="checkbox" name="save_default" value="1" <?= $saveDefaultChecked ? 'checked' : '' ?>>
                Lưu thông tin này làm địa chỉ mặc định cho lần đặt sau
            </label>
            <label>Ghi chú<textarea name="note" placeholder="Ví dụ: Ít hành, giao giờ hành chính..."><?= e($checkoutData['note']) ?></textarea></label>
            <button class="button" type="submit">Xác nhận đặt hàng</button>
        </form>
        <aside class="info-card">
            <h2>Đơn hàng</h2>
            <?php foreach ($items as $item): ?>
                <p><?= e($item['name']) ?> × <?= (int) $item['quantity'] ?> <strong><?= money($item['price'] * $item['quantity']) ?></strong></p>
            <?php endforeach; ?>
            <hr>
            <p>Tổng cộng <strong><?= money($total) ?></strong></p>
            <p>Thanh toán khi nhận hàng (COD).</p>
        </aside>
    </div>
</section>
