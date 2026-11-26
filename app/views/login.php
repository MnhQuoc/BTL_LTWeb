<section class="container auth-page">
    <form class="form-card" method="post" action="/login" data-validate>
        <h1>Đăng nhập</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Email<input type="email" name="email" autocomplete="email" required></label>
        <label>Mật khẩu<input type="password" name="password" minlength="6" autocomplete="current-password" required></label>
        <button class="button" type="submit">Đăng nhập</button>
        <p>Chưa có tài khoản? <a href="/register">Đăng ký ngay</a></p>
    </form>
</section>
