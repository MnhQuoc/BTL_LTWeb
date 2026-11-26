<section class="container page">
    <p class="eyebrow">SELLER</p>
    <h1>Khu vực người bán</h1>
    <?php if ($notice): ?>
        <div class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= e($notice) ?></div>
    <?php endif; ?>

    <section>
        <div class="section-heading">
            <h2>Sản phẩm của tôi</h2>
            <a class="button" href="/seller/products/new">Thêm sản phẩm</a>
        </div>
        <?php if (!$products): ?>
            <p class="empty-state">Bạn chưa có sản phẩm nào.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= e($product['name']) ?></td>
                            <td><?= e($product['category_name'] ?? 'Chưa phân loại') ?></td>
                            <td><?= money($product['price']) ?></td>
                            <td><?= (int) $product['stock'] ?></td>
                            <td>
                                <a href="/seller/products/<?= (int) $product['id'] ?>/edit">Sửa</a>
                                <form method="post" action="/seller/products/delete" style="display:inline">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                    <button type="submit">Xoá</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section>
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
                                    <select name="status" aria-label="Trạng thái món trong đơn">
                                        <?php foreach (['pending', 'confirmed', 'shipping', 'completed', 'cancelled'] as $status): ?>
                                            <option value="<?= e($status) ?>" <?= $item['item_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
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
    </section>
</section>
