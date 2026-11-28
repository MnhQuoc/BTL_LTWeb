<section class="container page">
    <p class="eyebrow">SELLER</p>
    <h1>Khu vực người bán</h1>
    <div class="stats-grid">
        <article><span>Món đang quản lý</span><strong><?= (int) $productTotal ?></strong></article>
        <article><span>Đơn hàng</span><strong><?= (int) $stats['orders'] ?></strong></article>
        <article><span>Đơn mới cần xử lý</span><strong><?= (int) $stats['pending_orders'] ?></strong></article>
        <article><span>Doanh thu đã hoàn tất</span><strong><?= money($stats['revenue']) ?></strong></article>
    </div>
    <?php if ($notice): ?>
        <div class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= e($notice) ?></div>
    <?php endif; ?>

    <section id="products">
        <div class="section-heading">
            <h2>Sản phẩm của tôi</h2>
            <a class="button" href="/seller/products/new">Thêm sản phẩm</a>
        </div>
        <?php if (!$products): ?>
            <p class="empty-state">Bạn chưa có sản phẩm nào.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= e($product['name']) ?></td>
                            <td><?= e($product['category_name'] ?? 'Chưa phân loại') ?></td>
                            <td><?= money($product['price']) ?></td>
                            <td><?= (int) $product['stock'] ?></td>
                            <td>
                                <?php if ((int) $product['stock'] === 0): ?>
                                    Hết hàng (tồn kho 0)
                                <?php elseif ((int) $product['is_available'] === 1): ?>
                                    Đang bán
                                <?php else: ?>
                                    Tạm hết món
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="seller-product-actions">
                                    <a class="seller-edit-action" href="/seller/products/<?= (int) $product['id'] ?>/edit">Sửa</a>
                                    <form method="post" action="/seller/products/availability" class="seller-inline-form">
                                        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                        <button type="submit"><?= (int) $product['is_available'] === 1 ? 'Đánh dấu hết món' : 'Mở bán lại' ?></button>
                                    </form>
                                    <form method="post" action="/seller/products/delete" class="seller-delete-form">
                                        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                        <button class="seller-delete-action" type="submit">Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if ($productPages > 1): ?>
            <nav class="pagination" aria-label="Phân trang sản phẩm">
                <?php for ($i = 1; $i <= $productPages; $i++): ?>
                    <a class="<?= $i === $productPage ? 'active' : '' ?>" href="/seller?product_page=<?= $i ?>&order_page=<?= $orderPage ?>#products"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>

    <section id="orders">
        <h2>Đơn hàng có sản phẩm của tôi</h2>
        <?php if (!$orderItems): ?>
            <p class="empty-state">Chưa có đơn hàng cho sản phẩm của bạn.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Đơn hàng / món</th><th>Khách hàng</th><th>Giao đến</th><th>Số lượng</th><th>Trạng thái món</th></tr></thead>
                    <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                #<?= (int) $item['order_id'] ?> · <?= e($item['product_name']) ?>
                                <br><small><?= e($item['created_at']) ?></small>
                            </td>
                            <td><?= e($item['customer_name']) ?><br><?= e($item['phone']) ?></td>
                            <td><?= e($item['address']) ?></td>
                            <td><?= (int) $item['quantity'] ?> × <?= money($item['price']) ?></td>
                            <td>
                                <form class="quantity-form" method="post" action="/seller/orders/status">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="order_item_id" value="<?= (int) $item['order_item_id'] ?>">
                                    <select name="status" class="order-status-select order-status--<?= e($item['item_status']) ?>" aria-label="Trạng thái món trong đơn">
                                        <?php foreach (orderStatusLabels() as $status => $label): ?>
                                            <option value="<?= e($status) ?>" <?= $item['item_status'] === $status ? 'selected' : '' ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Lưu</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if ($orderPages > 1): ?>
            <nav class="pagination" aria-label="Phân trang đơn hàng">
                <?php for ($i = 1; $i <= $orderPages; $i++): ?>
                    <a class="<?= $i === $orderPage ? 'active' : '' ?>" href="/seller?product_page=<?= $productPage ?>&order_page=<?= $i ?>#orders"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</section>
