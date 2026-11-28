<?php $orderStatusLabels = orderStatusLabels(); ?>
<section class="container page admin-page">
    <p class="eyebrow">BẢNG ĐIỀU HÀNH</p>
    <h1>Quản trị FoodStore</h1>
    <div class="stats-grid">
        <article><span>Thành viên</span><strong><?= (int) $stats['users'] ?></strong></article>
        <article><span>Sản phẩm</span><strong><?= (int) $stats['products'] ?></strong></article>
        <article><span>Đơn hàng</span><strong><?= (int) $stats['orders'] ?></strong></article>
        <article><span>Bài viết</span><strong><?= (int) $stats['posts'] ?></strong></article>
        <article><span>Bình luận</span><strong><?= (int) $stats['comments'] ?></strong></article>
    </div>
    <div class="table-wrap">
        <h2>Đơn hàng mới nhất</h2>
        <table>
            <thead><tr><th>Mã</th><th>Khách hàng</th><th>Người bán</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?></td>
                    <td><?= e($order['customer_name']) ?></td>
                    <td><?= e($order['seller_names']) ?></td>
                    <td><?= money($order['total']) ?></td>
                    <td><span class="status order-status order-status--<?= e($order['status']) ?>"><?= e($orderStatusLabels[$order['status']] ?? $order['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
