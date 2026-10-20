<?php
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../database/connect.php";

// Proteção: apenas administradores podem criar/editar logins
if (!isset($_SESSION["id"]) || ($_SESSION["tipo"] ?? "") !== "admin") {
    redirect("login/login.php");
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$erros = [];
$mensagem = null;
$dados = ["nome" => "", "email" => "", "telefone" => ""];

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email, telefone FROM usuarios WHERE id = :id");
        $stmt->execute(["id" => $id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            redirect("login/listar_logins.php");
        }

        $dados = $usuario;
    } catch (PDOException $e) {
        $erros[] = "Não foi possível carregar o usuário.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $idPost    = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $dados["nome"]      = trim($_POST["nome"] ?? "");
    $dados["email"]     = trim($_POST["email"] ?? "");
    $dados["telefone"]  = trim($_POST["telefone"] ?? "");
    $senha              = $_POST["senha"] ?? "";

    if ($dados["nome"] === "" || mb_strlen($dados["nome"]) > 100) {
        $erros[] = "Informe um nome válido (máx. 100 caracteres).";
    }
    if (!filter_var($dados["email"], FILTER_VALIDATE_EMAIL) || mb_strlen($dados["email"]) > 150) {
        $erros[] = "Informe um e-mail válido.";
    }
    if ($dados["telefone"] !== "" && !preg_match('/^[\d\s()+-]{8,20}$/', $dados["telefone"])) {
        $erros[] = "Informe um telefone válido ou deixe em branco.";
    }
    if (!$idPost && strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }
    if ($idPost && $senha !== "" && strlen($senha) < 6) {
        $erros[] = "A nova senha deve ter pelo menos 6 caracteres.";
    }

    if (empty($erros)) {
        try {
            // Verificar e-mail duplicado
            $sql = "SELECT id FROM usuarios WHERE email = :email";
            $params = ["email" => $dados["email"]];
            if ($idPost) {
                $sql .= " AND id <> :id";
                $params["id"] = $idPost;
            }
            $check = $pdo->prepare($sql);
            $check->execute($params);

            if ($check->fetch()) {
                $erros[] = "Este e-mail já está registado.";
            }
        } catch (PDOException $e) {
            $erros[] = "Não foi possível verificar o e-mail.";
        }
    }

    if (empty($erros)) {
        try {
            if ($idPost) {
                if ($senha !== "") {
                    $stmt = $pdo->prepare(
                        "UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone, senha = :senha
                         WHERE id = :id"
                    );
                    $stmt->execute([
                        "nome" => $dados["nome"],
                        "email" => $dados["email"],
                        "telefone" => $dados["telefone"] ?: null,
                        "senha" => password_hash($senha, PASSWORD_DEFAULT),
                        "id" => $idPost,
                    ]);
                } else {
                    $stmt = $pdo->prepare(
                        "UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone
                         WHERE id = :id"
                    );
                    $stmt->execute([
                        "nome" => $dados["nome"],
                        "email" => $dados["email"],
                        "telefone" => $dados["telefone"] ?: null,
                        "id" => $idPost,
                    ]);
                }

                $mensagem = ["tipo" => "success", "texto" => "Usuário atualizado com sucesso!"];
                $id = $idPost;
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (nome, email, telefone, senha, tipo)
                     VALUES (:nome, :email, :telefone, :senha, 'vendedor')"
                );
                $stmt->execute([
                    "nome" => $dados["nome"],
                    "email" => $dados["email"],
                    "telefone" => $dados["telefone"] ?: null,
                    "senha" => password_hash($senha, PASSWORD_DEFAULT),
                ]);

                $mensagem = ["tipo" => "success", "texto" => "Usuário cadastrado com sucesso!"];
                $dados = ["nome" => "", "email" => "", "telefone" => ""];
                $id = null;
            }
        } catch (PDOException $e) {
            $erros[] = "Não foi possível guardar o usuário.";
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="form-card">
    <h2 style="margin-bottom: 10px; color: #ffffff; text-align: center;">
        <?= $id ? "Alterar Dados do Login" : "Cadastrar Novo Login / Vendedor" ?>
    </h2>
    <p style="color: #94a3b8; text-align: center; margin-bottom: 25px; font-size: 14px;">
        <?= $id ? "Altere as informações do usuário abaixo" : "Preencha os dados abaixo para criar uma conta de acesso" ?>
    </p>

    <?php if ($mensagem !== null): ?>
        <div class="alert alert-<?= $mensagem["tipo"] ?>">
            <?= htmlspecialchars($mensagem["texto"]) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($erros as $erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endforeach; ?>

    <form action="<?= url('login/cadastrar.php') ?><?= $id ? "?id=" . (int)$id : "" ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ? (int)$id : "" ?>">

        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" maxlength="100" required
                   value="<?= htmlspecialchars($dados["nome"]) ?>" placeholder="Ex: Julius Silva">
        </div>

        <div class="form-group">
            <label>E-mail / Usuário</label>
            <input type="email" name="email" maxlength="150" required
                   value="<?= htmlspecialchars($dados["email"]) ?>" placeholder="Ex: julius@autosjulius.com.br">
        </div>

        <div class="form-group">
            <label>Telefone</label>
            <input type="tel" name="telefone" maxlength="20"
                   value="<?= htmlspecialchars($dados["telefone"] ?? "") ?>" placeholder="Ex: (11) 99999-0000">
        </div>

        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" minlength="6"
                   placeholder="<?= $id ? "Nova senha (deixe em branco para manter a atual)" : "Mínimo de 6 caracteres" ?>">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn" style="flex: 1;">
                <?= $id ? "Salvar Alterações" : "Concluir Cadastro" ?>
            </button>
            <a href="<?= url('login/listar_logins.php') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
