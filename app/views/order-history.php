<section class="container page">
    <p class="eyebrow">ĐƠN HÀNG CỦA TÔI</p>
    <h1>Lịch sử mua hàng</h1>
    <?php if (!$orders): ?>
        <p class="empty-state">Bạn chưa có đơn hàng nào.</p>
        <p><a class="button" href="/products">Khám phá sản phẩm</a></p>
    <?php else: ?>
        <?php $statusLabels = orderStatusLabels(); ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= (int) $order['id'] ?></td>
                        <td><?= e($order['created_at']) ?></td>
                        <td>
                            <?php foreach ($order['items'] as $item): ?>
                                <div><?= e($item['name']) ?> × <?= (int) $item['quantity'] ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td><?= money($order['total']) ?></td>
                        <td><span class="status order-status order-status--<?= e($order['status']) ?>"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
