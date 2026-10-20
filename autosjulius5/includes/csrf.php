<?php
// Helper de token CSRF — incluir antes de exibir ou processar formulários

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): void
{
    $enviado = $_POST['csrf_token'] ?? '';
    if (!is_string($enviado) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $enviado)) {
        http_response_code(403);
        die('Falha na verificação de segurança (CSRF). Recarregue a página e tente novamente.');
    }
}
