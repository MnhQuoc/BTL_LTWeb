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
function orderStatusLabels(): array
{
    return [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];
}
function cartCount(): int
{
    return array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
}
function renderLayout(string $title, string $view, array $data = []): void
{
    extract($data);
    $currentUser = $_SESSION['user'] ?? null;
    $pageTitle = $title;
    $flashAlert = $_SESSION['flash_alert'] ?? null;
    unset($_SESSION['flash_alert']);
    require __DIR__ . '/header.php';
    if (is_string($flashAlert)) {
        echo '<script>window.alert(' . json_encode($flashAlert, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ');</script>';
    }
    require $view;
    require __DIR__ . '/footer.php';
}
