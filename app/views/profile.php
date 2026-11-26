<section class="container page">
    <h1>Tài khoản cá nhân</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php $user = $_SESSION['user']; ?>
    <form class="form-card" method="post" action="/profile" data-validate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Email<input type="email" value="<?= e($user['email']) ?>" disabled></label>
        <label>Họ tên<input type="text" name="name" value="<?= e($user['name']) ?>" minlength="2" required></label>
        <label>Số điện thoại<input type="tel" name="phone" pattern="[0-9]{8,15}"></label>
        <label>Mật khẩu mới (bỏ trống nếu không đổi)<input type="password" name="password" minlength="6"></label>
        <button class="button" type="submit">Lưu thay đổi</button>
    </form>
</section>
