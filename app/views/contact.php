<section class="container page">
    <p class="eyebrow">LIÊN HỆ</p>
    <h1>Gửi yêu cầu cho chúng tôi</h1>
    <?php if ($contactError): ?><div class="alert alert-error"><?= e($contactError) ?></div><?php endif; ?>
    <?php if ($contactSuccess): ?><div class="alert alert-success"><?= e($contactSuccess) ?></div><?php endif; ?>
    <form class="form-card" method="post" action="/contact" data-validate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <label>Họ tên<input type="text" name="name" minlength="2" required></label>
        <label>Email<input type="email" name="email" required></label>
        <label>Số điện thoại<input type="tel" name="phone" pattern="[0-9]{8,15}" required></label>
        <label>Nội dung<textarea name="message" minlength="10" required></textarea></label>
        <button class="button" type="submit">Gửi liên hệ</button>
    </form>
</section>
