<?php
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../database/connect.php";

// Já autenticado? Não precisa ver o login de novo
if (isset($_SESSION["id"])) {
    redirect(($_SESSION["tipo"] ?? "") === "admin" ? "login/listar_logins.php" : "index.php");
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        $erro = "Preencha o e-mail e a senha.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT id, nome, email, senha, tipo
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1"
            );

            $stmt->execute(["email" => $email]);
            $usuario = $stmt->fetch();

            if (
                $usuario &&
                !empty($usuario["senha"]) &&
                password_verify($senha, $usuario["senha"])
            ) {
                session_regenerate_id(true);

                $_SESSION["id"] = $usuario["id"];
                $_SESSION["nome"] = $usuario["nome"];
                $_SESSION["email"] = $usuario["email"];
                $_SESSION["tipo"] = $usuario["tipo"];

                if ($usuario["tipo"] === "admin") {
                    redirect("login/listar_logins.php");
                } else {
                    redirect("index.php");
                }
            }

            $erro = "E-mail ou senha inválidos.";
        } catch (PDOException $e) {
            $erro = "Não foi possível realizar o login.";
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="form-card" style="max-width: 420px;">
    <span style="display: block; text-align: center; color: #ef4444; font-weight: 700; letter-spacing: 2px; font-size: 13px; margin-bottom: 8px;">
        AUTOSJULIUS
    </span>

    <h1 style="color: #ffffff; text-align: center; margin-bottom: 20px;">Login</h1>

    <?php if ($erro !== ""): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                required>
        </div>

        <div class="form-group">
            <label for="senha">Senha</label>
            <input
                type="password"
                id="senha"
                name="senha"
                autocomplete="current-password"
                required>
        </div>

        <button class="btn" type="submit" style="width: 100%;">
            Entrar
        </button>
    </form>

    <p style="text-align: center; margin-top: 20px; color: #94a3b8; font-size: 14px;">
        Não possui conta?
        <a href="registro.php" style="color: #ef4444;">Cadastre-se</a>
    </p>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
