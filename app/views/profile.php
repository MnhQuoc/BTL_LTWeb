<section class="container page">
    <h1>Tài khoản cá nhân</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <form class="form-card" method="post" action="/profile" data-validate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Email<input type="email" value="<?= e($user['email']) ?>" disabled></label>
        <label>Họ tên<input type="text" name="name" value="<?= e($user['name']) ?>" minlength="2" required></label>
        <label>Số điện thoại<input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" pattern="[0-9]{8,15}"></label>
        <label>Địa chỉ mặc định<textarea name="address" maxlength="255"><?= e($user['address'] ?? '') ?></textarea></label>
        <a class="button button-outline" href="/profile/password">Thay đổi mật khẩu</a>
        <button class="button" type="submit">Lưu thay đổi</button>
    </form>
</section>
