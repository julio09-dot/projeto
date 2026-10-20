<?php require_once __DIR__ . '/includes/helpers.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutosJulius - A Sua Melhor Escolha Sobre Rodas</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0f172a; color: #f8fafc; line-height: 1.6; display: flex; flex-direction: column; min-height: 100vh; }
        
        header { background-color: #1e293b; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ef4444; }
        .logo { font-size: 26px; font-weight: 800; color: #ffffff; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; }
        .logo span { color: #ef4444; }
        
        /* Hero Section com Poucas Palavras */
        .hero { text-align: center; padding: 80px 20px; max-width: 800px; margin: 0 auto; }
        .hero h1 { font-size: 42px; font-weight: 800; margin-bottom: 15px; color: #ffffff; }
        .hero h1 span { color: #ef4444; }
        .hero p { font-size: 20px; color: #94a3b8; margin-bottom: 30px; font-weight: 400; }
        
        /* Destaques Rápidos */
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; max-width: 900px; margin: 0 auto 60px auto; padding: 0 20px; }
        .feature-card { background: #1e293b; padding: 25px; border-radius: 10px; border: 1px solid #334155; text-align: center; }
        .feature-card h3 { color: #ef4444; margin-bottom: 10px; font-size: 18px; }
        .feature-card p { color: #cbd5e1; font-size: 14px; }
        
        /* Call to Action antes do Footer */
        .cta-section { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); text-align: center; padding: 60px 20px; border-top: 1px solid #334155; margin-top: auto; }
        .cta-section h2 { font-size: 28px; margin-bottom: 15px; color: #ffffff; }
        .cta-section p { color: #94a3b8; margin-bottom: 25px; }
        
        .btn-login { display: inline-block; padding: 16px 36px; background-color: #ef4444; color: white; text-decoration: none; font-size: 18px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); transition: all 0.2s ease; }
        .btn-login:hover { background-color: #dc2626; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6); }
        
        footer { background-color: #090d16; border-top: 1px solid #1e293b; text-align: center; padding: 25px; color: #64748b; font-size: 14px; }
    </style>
</head>
<body>

<header>
    <a href="<?= url('landing.php') ?>" class="logo">Autos<span>Julius</span></a>
    <a href="<?= url('login/login.php') ?>" style="color: #cbd5e1; text-decoration: none; font-weight: 600;">Acessar Conta</a>
    <a href="<?= url('index.php') ?>" style="color: #cbd5e1; text-decoration: none; font-weight: 600;">Início</a>
</header>

<main>
    <section class="hero">
        <h1>Venda e Revenda de Carros na <span>AutosJulius</span></h1>
        <p>Sem enrolação. Carros revisados, procedência garantida e as melhores condições de pagamento do mercado.</p>
    </section>

    <section class="features">
        <div class="feature-card">
            <h3>🚗 Estudo de Troca</h3>
            <p>Avaliamos seu seminovo com valor justo de mercado.</p>
        </div>
        <div class="feature-card">
            <h3>📋 Checkup Completo</h3>
            <p>Veículos 100% periciados e prontos para rodar.</p>
        </div>
        <div class="feature-card">
            <h3>⚡ Financiamento Rápido</h3>
            <p>Aprovação imediata com as melhores taxas do setor.</p>
        </div>
    </section>

    <section class="cta-section">
        <h2>Pronto para negociar ou gerenciar?</h2>
        <p>Acesse sua conta para ver ofertas exclusivas ou gerenciar seu painel de revenda.</p>
        <a href="<?= url('login/login.php') ?>" class="btn-login">Entrar na Minha Conta</a>
        <p style="margin-top: 18px; color: #94a3b8;">
            Ainda não tem conta?
            <a href="<?= url('login/registro.php') ?>" style="color: #ef4444; font-weight: 700;">Cadastre-se grátis</a>
        </p>
    </section>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> AutosJulius - Todos os direitos reservados. Compra, Venda e Revenda de Veículos.
</footer>

</body>
</html>
