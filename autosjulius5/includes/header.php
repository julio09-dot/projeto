<?php
require_once __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Impede o navegador de guardar páginas em cache — a lista de veículos
// deve sempre refletir o estado atual do banco de dados
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$tipoUsuario = $_SESSION['tipo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutosJulius - Revenda de Veículos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0f172a; color: #f8fafc; line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }

        /* Cabeçalho do Admin / Sistema */
        header { background-color: #1e293b; border-bottom: 2px solid #ef4444; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .logo { font-size: 24px; font-weight: 800; color: #ffffff; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; }
        .logo span { color: #ef4444; }

        nav { display: flex; gap: 15px; align-items: center; }
        nav a { color: #cbd5e1; text-decoration: none; font-weight: 600; font-size: 14px; padding: 8px 14px; border-radius: 6px; transition: all 0.2s; }
        nav a:hover { background-color: #334155; color: #ffffff; }
        nav a.active { background-color: #ef4444; color: white; }

        .main-container { flex: 1; max-width: 1100px; width: 100%; margin: 30px auto; padding: 0 20px; }

        /* Botões padronizados */
        .btn { display: inline-block; padding: 12px 24px; border: none; background-color: #ef4444; color: white; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 15px; font-weight: 700; transition: background 0.2s, transform 0.1s; text-align: center; }
        .btn:hover { background-color: #dc2626; transform: translateY(-1px); }
        .btn-outline { background-color: transparent; border: 2px solid #ef4444; color: #ef4444; }
        .btn-outline:hover { background-color: #ef4444; color: white; }
        .btn-secondary { background-color: #475569; }
        .btn-secondary:hover { background-color: #334155; }
        .btn-warning { background-color: #f59e0b; color: white; }
        .btn-warning:hover { background-color: #d97706; }
        .btn-danger { background-color: #dc2626; }
        .btn-danger:hover { background-color: #b91c1c; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        /* Formulários */
        .form-card { background: #1e293b; padding: 30px; border-radius: 12px; border: 1px solid #334155; max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 14px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], select, textarea {
            width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: white; font-size: 15px; outline: none;
        }
        input:focus, select:focus, textarea:focus { border-color: #ef4444; }
        textarea { resize: vertical; min-height: 90px; }
        .required { color: #ef4444; }

        /* Alertas */
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 600; border: 1px solid transparent; }
        .alert-success { background-color: rgba(34, 197, 94, 0.12); color: #22c55e; border-color: rgba(34, 197, 94, 0.35); }
        .alert-danger  { background-color: rgba(239, 68, 68, 0.12); color: #f87171; border-color: rgba(239, 68, 68, 0.35); }
        .alert-warning { background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border-color: rgba(245, 158, 11, 0.35); }

        /* Tabelas */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #1e293b; border-radius: 8px; overflow: hidden; }
        table th, table td { padding: 14px 18px; text-align: left; }
        table th { background-color: #0f172a; color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 12px; }
        table tr { border-bottom: 1px solid #334155; }
        table tr:hover { background-color: #273549; }
        .actions { display: flex; gap: 8px; }

        /* Footer */
        footer { background-color: #090d16; border-top: 1px solid #1e293b; text-align: center; padding: 25px; color: #64748b; font-size: 14px; margin-top: auto; }
    </style>
</head>
<body>
<header>
    <a href="<?= url('index.php') ?>" class="logo">Autos<span>Julius</span></a>
    <nav>
        <a href="<?= url('index.php') ?>">Carros / Início</a>
        <a href="<?= url('landing.php') ?>">Ver Apresentação</a>
        <?php if ($tipoUsuario === 'admin'): ?>
            <a href="<?= url('login/gerenciar_carros.php') ?>">Gerenciar Veículos</a>
            <a href="<?= url('login/listar_logins.php') ?>">Visualizar Logins</a>
            <a href="<?= url('login/cadastrar.php') ?>">Novo Login / Cadastro</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['id'])): ?>
            <a href="<?= url('login/logout.php') ?>" class="btn-outline">Sair (<?= htmlspecialchars($_SESSION['nome'] ?? '') ?>)</a>
        <?php else: ?>
            <a href="<?= url('login/registro.php') ?>">Criar Conta</a>
            <a href="<?= url('login/login.php') ?>" class="btn-outline">Entrar</a>
        <?php endif; ?>
    </nav>
</header>
<div class="main-container">
