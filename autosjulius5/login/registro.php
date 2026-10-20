<?php
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/flash.php";
require_once __DIR__ . "/../database/connect.php";

// Já autenticado? Não precisa criar outra conta
if (isset($_SESSION["id"])) {
    redirect(($_SESSION["tipo"] ?? "") === "admin" ? "login/listar_logins.php" : "index.php");
}

$erros = [];
$dados = ["nome" => "", "email" => "", "telefone" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $dados["nome"]     = trim($_POST["nome"] ?? "");
    $dados["email"]    = trim($_POST["email"] ?? "");
    $dados["telefone"] = trim($_POST["telefone"] ?? "");
    $senha             = $_POST["senha"] ?? "";
    $senhaConfirmacao  = $_POST["senha_confirmacao"] ?? "";

    if ($dados["nome"] === "" || mb_strlen($dados["nome"]) > 100) {
        $erros[] = "Informe um nome válido (máx. 100 caracteres).";
    }
    if (!filter_var($dados["email"], FILTER_VALIDATE_EMAIL) || mb_strlen($dados["email"]) > 150) {
        $erros[] = "Informe um e-mail válido.";
    }
    if ($dados["telefone"] !== "" && !preg_match('/^[\d\s()+-]{8,20}$/', $dados["telefone"])) {
        $erros[] = "Informe um telefone válido ou deixe em branco.";
    }
    if (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }
    if ($senha !== $senhaConfirmacao) {
        $erros[] = "A confirmação de senha não confere.";
    }

    if (empty($erros)) {
        try {
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $check->execute(["email" => $dados["email"]]);

            if ($check->fetch()) {
                $erros[] = "Este e-mail já está registado. Faça login ou use outro e-mail.";
            }
        } catch (PDOException $e) {
            $erros[] = "Não foi possível verificar o e-mail.";
        }
    }

    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, telefone, senha, tipo)
                 VALUES (:nome, :email, :telefone, :senha, 'vendedor')"
            );
            $stmt->execute([
                "nome"     => $dados["nome"],
                "email"    => $dados["email"],
                "telefone" => $dados["telefone"] ?: null,
                "senha"    => password_hash($senha, PASSWORD_DEFAULT),
            ]);

            // Login automático após o cadastro
            session_regenerate_id(true);
            $_SESSION["id"]    = (int) $pdo->lastInsertId();
            $_SESSION["nome"]  = $dados["nome"];
            $_SESSION["email"] = $dados["email"];
            $_SESSION["tipo"]  = "vendedor";

            flash_set("success", "Conta criada com sucesso! Bem-vindo(a), " . $dados["nome"] . ".");
            redirect("index.php");
        } catch (PDOException $e) {
            $erros[] = "Não foi possível criar a conta. Tente novamente.";
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="form-card" style="max-width: 440px;">
    <span style="display: block; text-align: center; color: #ef4444; font-weight: 700; letter-spacing: 2px; font-size: 13px; margin-bottom: 8px;">
        AUTOSJULIUS
    </span>

    <h1 style="color: #ffffff; text-align: center; margin-bottom: 6px;">Criar Conta</h1>
    <p style="color: #94a3b8; text-align: center; margin-bottom: 22px; font-size: 14px;">
        Cadastre-se para acessar a plataforma AutosJulius
    </p>

    <?php foreach ($erros as $erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="registro.php">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nome">Nome Completo <span class="required">*</span></label>
            <input type="text" id="nome" name="nome" maxlength="100" required
                   placeholder="Ex: Julius Silva" value="<?= htmlspecialchars($dados["nome"]) ?>">
        </div>

        <div class="form-group">
            <label for="email">E-mail <span class="required">*</span></label>
            <input type="email" id="email" name="email" maxlength="150" required
                   autocomplete="username"
                   placeholder="Ex: julius@email.com" value="<?= htmlspecialchars($dados["email"]) ?>">
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" maxlength="20"
                   placeholder="Ex: (11) 99999-0000" value="<?= htmlspecialchars($dados["telefone"]) ?>">
        </div>

        <div class="form-group">
            <label for="senha">Senha <span class="required">*</span></label>
            <input type="password" id="senha" name="senha" minlength="6" required
                   autocomplete="new-password" placeholder="Mínimo de 6 caracteres">
        </div>

        <div class="form-group">
            <label for="senha_confirmacao">Confirmar Senha <span class="required">*</span></label>
            <input type="password" id="senha_confirmacao" name="senha_confirmacao" minlength="6" required
                   autocomplete="new-password" placeholder="Digite a senha de novo">
        </div>

        <button class="btn" type="submit" style="width: 100%;">
            Criar Minha Conta
        </button>
    </form>

    <p style="text-align: center; margin-top: 20px; color: #94a3b8; font-size: 14px;">
        Já possui conta?
        <a href="login.php" style="color: #ef4444;">Entrar</a>
    </p>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
