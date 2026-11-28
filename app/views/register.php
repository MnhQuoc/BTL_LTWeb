<section class="container auth-page login-page">
    <div class="login-visual">
        <img src="/assets/images/login.avif" alt="Minh họa ẩm thực FoodStore">
        <div class="login-visual-copy">
            <span>ẨM THỰC VIỆT · FOODSTORE</span>
            <h2>Thưởng thức bữa ăn nóng hổi chuẩn vị mỗi ngày</h2>
        </div>
    </div>
    <form class="form-card login-form" method="post" action="/register" data-validate>
        <p class="login-welcome">Bắt đầu cùng FoodStore</p>
        <h1>Đăng ký thành viên</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Họ tên<input type="text" name="name" minlength="2" required></label>
        <label>Email<input type="email" name="email" autocomplete="email" required></label>
        <label>Mật khẩu<input type="password" name="password" minlength="6" autocomplete="new-password" required></label>
        <label>Nhập lại mật khẩu<input type="password" name="password_confirmation" minlength="6" autocomplete="new-password" required></label>
        <button class="button" type="submit">Tạo tài khoản</button>
        <p class="login-register">Đã có tài khoản? <a href="/login">Đăng nhập</a></p>
    </form>
</section>
