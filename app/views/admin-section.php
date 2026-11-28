<section class="container page admin-page">
    <?php $orderStatusLabels = orderStatusLabels(); ?>
    <p class="eyebrow">BẢNG ĐIỀU HÀNH</p>
    <h1><?= e($sectionTitle) ?></h1>
    <div class="table-wrap">
        <table>
            <?php if ($section === 'users'): ?>
                <thead><tr><th>Thành viên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= e($record['name']) ?></td><td><?= e($record['email']) ?></td><td><?= e($record['role']) ?></td>
                        <td><?= $record['blocked'] ? 'Đã khoá' : 'Đang hoạt động' ?></td>
                        <td><?php if ($record['role'] !== 'admin'): ?><form method="post" action="/admin/action" class="admin-inline-form">
                            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="section" value="users">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>"><input type="hidden" name="action" value="toggle">
                            <button type="submit"><?= $record['blocked'] ? 'Mở khoá' : 'Khoá tài khoản' ?></button>
                        </form><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php elseif ($section === 'products'): ?>
                <thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Người bán</th><th>Giá</th><th>Tồn kho</th><th>Nổi bật</th></tr></thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr><td><?= e($record['name']) ?></td><td><?= e($record['category_name'] ?? 'Chưa phân loại') ?></td>
                        <td><?= e($record['seller_name'] ?? 'Quản trị viên') ?></td><td><?= money($record['price']) ?></td><td><?= (int) $record['stock'] ?></td>
                        <td><form method="post" action="/admin/action" class="admin-inline-form">
                            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="section" value="products">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>"><input type="hidden" name="action" value="toggle">
                            <button class="<?= $record['featured'] ? 'admin-featured-button' : '' ?>" type="submit"><?= $record['featured'] ? 'Bỏ nổi bật' : 'Đặt nổi bật' ?></button>
                        </form></td></tr>
                <?php endforeach; ?>
                </tbody>
            <?php elseif ($section === 'orders'): ?>
                <thead><tr><th>Mã</th><th>Khách hàng</th><th>Người bán</th><th>Điện thoại</th><th>Địa chỉ</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr><td>#<?= (int) $record['id'] ?></td><td><?= e($record['customer_name']) ?></td><td><?= e($record['seller_names']) ?></td><td><?= e($record['phone']) ?></td>
                        <td><?= e($record['address']) ?></td><td><?= money($record['total']) ?></td><td>
                            <form method="post" action="/admin/action" class="admin-inline-form">
                                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="section" value="orders">
                                <input type="hidden" name="id" value="<?= (int) $record['id'] ?>"><input type="hidden" name="action" value="status">
                                <select name="status" class="order-status-select order-status--<?= e($record['status']) ?>"><?php foreach ($orderStatusLabels as $status => $label): ?>
                                    <option value="<?= e($status) ?>" <?= $record['status'] === $status ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?></select><button type="submit">Lưu</button>
                            </form>
                        </td></tr>
                <?php endforeach; ?>
                </tbody>
            <?php elseif ($section === 'posts'): ?>
                <div class="admin-section-actions">
                    <a class="button" href="/admin/posts/new">Thêm bài viết</a>
                </div>
                <thead><tr><th>Tiêu đề</th><th>Tác giả</th><th>Ngày đăng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><a href="/news/<?= e($record['slug']) ?>" target="_blank"><?= e($record['title']) ?></a></td>
                        <td><?= e($record['author_name'] ?? 'Quản trị viên') ?></td>
                        <td><?= e($record['published_at'] ?? $record['created_at']) ?></td>
                        <td><?= (int) $record['is_published'] ? 'Đang hiển thị' : 'Đã ẩn' ?></td>
                        <td>
                            <div class="seller-product-actions">
                                <a class="seller-edit-action" href="/admin/posts/<?= (int) $record['id'] ?>/edit">Sửa</a>
                                <form method="post" action="/admin/action" class="seller-delete-form">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="section" value="posts">
                                    <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="seller-toggle-action"><?= (int) $record['is_published'] ? 'Ẩn' : 'Hiện' ?></button>
                                </form>
                                <form method="post" action="/admin/posts/delete" class="seller-delete-form" data-confirm="Bạn có chắc chắn muốn xóa bài viết này không?">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                                    <button type="submit" class="seller-delete-action">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php elseif ($section === 'comments'): ?>
                <thead><tr><th>Bài viết</th><th>Tác giả</th><th>Nội dung</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= e($record['post_title']) ?></td>
                        <td><?= e($record['author_name'] ?? 'Thành viên') ?></td>
                        <td><?= nl2br(e($record['content'])) ?></td>
                        <td><?= (int) $record['is_approved'] ? 'Đang hiển thị' : 'Đã ẩn' ?></td>
                        <td>
                        <div class="comment-action-group">
                            <form method="post" action="/admin/action" class="seller-delete-form">
                                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="section" value="comments">
                                <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                                <input type="hidden" name="action" value="toggle">
                                <button type="submit" class="seller-toggle-action"><?= (int) $record['is_approved'] ? 'Ẩn' : 'Duyệt' ?></button>
                            </form>
                            <form method="post" action="/admin/action" class="seller-delete-form" data-confirm="Bạn có chắc chắn muốn xóa bình luận này không?">
                                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="section" value="comments">
                                <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="seller-delete-action">Xóa</button>
                            </form>
                        </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php else: ?>
                <thead><tr><th>Câu hỏi</th><th>Câu trả lời</th><th>Trạng thái</th></tr></thead>
                <tbody>
                <?php foreach ($records as $record): ?>
                    <tr><td><?= e($record['question']) ?></td><td><?= nl2br(e($record['answer'])) ?></td><td>
                        <form method="post" action="/admin/action" class="admin-inline-form">
                            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>"><input type="hidden" name="section" value="faqs">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>"><input type="hidden" name="action" value="toggle">
                            <button type="submit"><?= $record['is_active'] ? 'Đang hiển thị' : 'Đang ẩn' ?></button>
                        </form>
                    </td></tr>
                <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
        </table>
        <?php if (!$records): ?><p class="empty-state">Chưa có dữ liệu.</p><?php endif; ?>
    </div>
    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Phân trang">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="/admin/<?= e($section) ?>?page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
