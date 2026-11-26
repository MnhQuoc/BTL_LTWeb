<section class="container auth-page">
    <form class="form-card" method="post" action="/register" data-validate>
        <h1>Đăng ký thành viên</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Họ tên<input type="text" name="name" minlength="2" required></label>
        <label>Email<input type="email" name="email" autocomplete="email" required></label>
        <label>Mật khẩu<input type="password" name="password" minlength="6" autocomplete="new-password" required></label>
        <button class="button" type="submit">Tạo tài khoản</button>
        <p>Đã có tài khoản? <a href="/login">Đăng nhập</a></p>
    </form>
</section>
