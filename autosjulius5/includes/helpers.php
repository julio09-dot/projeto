<?php
// Helpers de caminho/URL — funcionam tanto no root do servidor
// quanto em subpastas (ex.: http://localhost/autosjulius5/)

function app_base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir    = dirname($script);

    // App servido na raiz do servidor
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        $base = '';
        return $base;
    }

    // Scripts dentro de /login devem remover esse trecho do caminho base
    if (substr($dir, -6) === '/login') {
        $dir = substr($dir, 0, -6);
    }

    $base = rtrim($dir, '/');
    return $base;
}

function url(string $path = ''): string
{
    return app_base_path() . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}
