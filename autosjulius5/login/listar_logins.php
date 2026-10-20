<?php
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/flash.php";
require_once __DIR__ . "/../database/connect.php";

if (!isset($_SESSION["id"]) || ($_SESSION["tipo"] ?? "") !== "admin") {
    redirect("login/login.php");
}

try {
    $stmt = $pdo->query(
        "SELECT id, nome, email, telefone
         FROM usuarios
         ORDER BY id DESC"
    );

    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Não foi possível carregar os usuários.";
    $usuarios = [];
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="form-card" style="max-width: 1000px; margin: 30px auto;">
    <h1 style="color: #ffffff;">Usuários cadastrados</h1>

    <?= flash_render() ?>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <p style="margin-top: 20px;">
        <a href="<?= url('login/cadastrar.php') ?>" class="btn">
            + Cadastrar usuário
        </a>
    </p>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5">
                            Nenhum usuário cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= (int)$usuario["id"] ?></td>
                            <td><?= htmlspecialchars($usuario["nome"]) ?></td>
                            <td><?= htmlspecialchars($usuario["email"]) ?></td>
                            <td><?= htmlspecialchars($usuario["telefone"] ?? "—") ?></td>

                            <td style="white-space: nowrap;">
                                <div class="actions">
                                    <a href="<?= url('login/cadastrar.php') ?>?id=<?= (int)$usuario["id"] ?>" class="btn btn-sm">
                                        Editar
                                    </a>

                                    <?php if ((int)$usuario["id"] !== (int)$_SESSION["id"]): ?>
                                        <form action="<?= url('login/deletar_login.php') ?>" method="POST" style="display: inline;"
                                              onsubmit="return confirm('Deseja realmente excluir este usuário?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$usuario["id"] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                Excluir
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
