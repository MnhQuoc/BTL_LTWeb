<section class="container page admin-page">
    <p class="eyebrow">BẢNG ĐIỀU HÀNH</p>
    <h1>Quản trị FoodStore</h1>
    <div class="stats-grid">
        <article><span>Thành viên</span><strong><?= (int) $stats['users'] ?></strong></article>
        <article><span>Sản phẩm</span><strong><?= (int) $stats['products'] ?></strong></article>
        <article><span>Đơn hàng</span><strong><?= (int) $stats['orders'] ?></strong></article>
        <article><span>Liên hệ mới</span><strong><?= (int) $stats['contacts'] ?></strong></article>
    </div>
    <div class="admin-grid">
        <section>
            <h2>Đơn hàng mới nhất</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mã</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= money($order['total']) ?></td>
                            <td><span class="status"><?= e($order['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <aside class="info-card">
            <h2>Menu quản trị</h2>
            <ul>
                <li>Quản lý thành viên</li>
                <li>Quản lý sản phẩm</li>
                <li>Quản lý đơn hàng</li>
                <li>Quản lý liên hệ</li>
                <li>Quản lý tin tức</li>
                <li>Quản lý FAQ</li>
            </ul>
        </aside>
    </div>
</section>
