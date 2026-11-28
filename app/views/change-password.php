<section class="container auth-page">
    <form class="form-card" method="post" action="/profile/password" data-validate>
        <h1>Đổi mật khẩu</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Mật khẩu hiện tại
            <input type="password" name="current_password" autocomplete="current-password" required>
        </label>
        <label>Mật khẩu mới
            <input type="password" name="new_password" minlength="6" maxlength="255" autocomplete="new-password" required>
        </label>
        <label>Nhập lại mật khẩu mới
            <input type="password" name="confirm_password" minlength="6" maxlength="255" autocomplete="new-password" required>
        </label>
        <button class="button" type="submit">Cập nhật mật khẩu</button>
        <a class="button button-outline" href="/profile">Quay lại tài khoản</a>
    </form>
</section>
