<?php
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function money($value): string { return number_format((float) $value, 0, ',', '.') . 'đ'; }
function cartCount(): int
{
    return array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
}
function renderLayout(string $title, string $view, array $data = []): void
{
    extract($data);
    $currentUser = $_SESSION['user'] ?? null;
    $pageTitle = $title;
    require __DIR__ . '/header.php';
    require $view;
    require __DIR__ . '/footer.php';
}
