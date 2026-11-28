<section class="container page">
    <p class="eyebrow">BÁO CÁO</p>
    <h1>Doanh thu</h1>
    <div class="stats-grid">
        <article><span>Đơn đã hoàn tất</span><strong><?= (int) $summary['completed_orders'] ?></strong></article>
        <article><span>Tổng doanh thu</span><strong><?= money($summary['total_revenue']) ?></strong></article>
    </div>

    <section>
        <h2>Doanh thu theo ngày</h2>
        <?php if (!$dailyRevenue): ?>
            <p class="empty-state">Chưa có đơn hàng hoàn tất.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Ngày</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
                    <tbody>
                    <?php foreach ($dailyRevenue as $row): ?>
                        <tr><td><?= e($row['period']) ?></td><td><?= (int) $row['orders'] ?></td><td><?= money($row['revenue']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h2>Doanh thu theo tháng</h2>
        <?php if (!$monthlyRevenue): ?>
            <p class="empty-state">Chưa có doanh thu theo tháng.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tháng</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
                    <tbody>
                    <?php foreach ($monthlyRevenue as $row): ?>
                        <tr><td><?= e($row['period']) ?></td><td><?= (int) $row['orders'] ?></td><td><?= money($row['revenue']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
