<section class="container page">
    <p class="eyebrow">HỎI ĐÁP</p>
    <h1>Câu hỏi thường gặp</h1>
    <div class="faq-list">
        <?php foreach ($faqs as $faq): ?>
            <details>
                <summary><?= e($faq['question']) ?></summary>
                <p><?= e($faq['answer']) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>
