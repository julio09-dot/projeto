<?php
// Mensagens flash entre redirect (padrão PRG) — incluir após o csrf.php

require_once __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function flash_set(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function flash_get(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function flash_render(bool $linkInicio = false): string
{
    $f = flash_get();
    if ($f === null) {
        return '';
    }

    $html = '<div class="alert alert-' . htmlspecialchars($f['tipo']) . '"'
          . ' style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">'
          . '<span>' . htmlspecialchars($f['texto']) . '</span>';

    if ($linkInicio && $f['tipo'] === 'success') {
        $html .= '<a href="' . url('index.php') . '" class="btn btn-secondary btn-sm" style="margin-left: auto;">Ver no início →</a>';
    }

    $html .= '</div>';
    return $html;
}
