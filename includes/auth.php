<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser(): array {
    return [
        'id'   => $_SESSION['usuario_id']  ?? 0,
        'nome' => $_SESSION['usuario_nome'] ?? '',
    ];
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function renderFlash(): void {
    $f = getFlash();
    if (!$f) return;
    $icons = ['success' => '✓', 'error' => '✕', 'warning' => '⚠'];
    $icon  = $icons[$f['type']] ?? 'ℹ';
    echo '<div class="flash flash--' . htmlspecialchars($f['type']) . '">'
       . '<span class="flash__icon">' . $icon . '</span>'
       . '<span>' . htmlspecialchars($f['msg']) . '</span>'
       . '</div>';
}

function h(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function formatMoney(mixed $v): string {
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function formatDate(?string $d): string {
    if (!$d) return '—';
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('d/m/Y') : $d;
}

function statusBadge(string $status): string {
    $map = [
        'pendente'  => ['label' => 'Pendente',  'class' => 'badge--warning'],
        'aprovado'  => ['label' => 'Aprovado',  'class' => 'badge--info'],
        'pago'      => ['label' => 'Pago',      'class' => 'badge--success'],
        'cancelado' => ['label' => 'Cancelado', 'class' => 'badge--neutral'],
        'vencido'   => ['label' => 'Vencido',   'class' => 'badge--danger'],
    ];
    $s = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge--neutral'];
    return '<span class="badge ' . $s['class'] . '">' . $s['label'] . '</span>';
}
