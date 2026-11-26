<section class="container page">
    <p class="eyebrow">GIÁ SẢN PHẨM</p>
    <h1>Bảng giá</h1>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Món</th><th>Mô tả</th><th>Giá</th></tr></thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['short_description']) ?></td>
                    <td><strong><?= money($product['price']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
