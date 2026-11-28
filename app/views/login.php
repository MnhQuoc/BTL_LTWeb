<section class="container auth-page login-page">
    <div class="login-visual">
        <img src="/assets/images/login.avif" alt="Minh họa ẩm thực FoodStore">
        <div class="login-visual-copy">
            <span>ẨM THỰC VIỆT · FOODSTORE</span>
            <h2>Thưởng thức bữa ăn nóng hổi chuẩn vị mỗi ngày</h2>
        </div>
    </div>
    <form class="form-card login-form" method="post" action="/login" data-validate>
        <p class="login-welcome">Chào mừng bạn đến với FoodStore</p>
        <h1>Đăng nhập</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Email<input type="email" name="email" autocomplete="email" required></label>
        <label>Mật khẩu<input type="password" name="password" minlength="6" autocomplete="current-password" required></label>
        <button class="button" type="submit">Đăng nhập</button>
        <p class="login-register">Chưa có tài khoản? <a href="/register">Đăng ký ngay</a></p>
    </form>
</section>
