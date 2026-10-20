<?php
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/flash.php";
require_once __DIR__ . "/../includes/imagem.php";
require_once __DIR__ . "/../database/connect.php";

// Proteção da página: Apenas usuários logados e do tipo "admin" podem acessar
if (!isset($_SESSION["id"]) || ($_SESSION["tipo"] ?? "") !== "admin") {
    redirect("login/login.php");
}

// Processar a exclusão do veículo via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $carro_id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if ($carro_id) {
        try {
            $stmtDel = $pdo->prepare("DELETE FROM veiculos WHERE id = :id");
            $stmtDel->execute(["id" => $carro_id]);

            if ($stmtDel->rowCount() > 0) {
                flash_set("success", "Veículo excluído com sucesso! Ele saiu da página inicial.");
            } else {
                flash_set("danger", "Veículo não encontrado.");
            }
        } catch (PDOException $e) {
            flash_set("danger", "Não foi possível excluir o veículo.");
        }

        redirect("login/gerenciar_carros.php");
    }
}

// Buscar a lista atualizada de veículos
try {
    $stmt = $pdo->query(
        "SELECT id, marca, modelo, ano, preco, combustivel, cambio, quilometros, imagem
         FROM veiculos
         ORDER BY data_cadastro DESC"
    );
    $veiculos = $stmt->fetchAll();
} catch (PDOException $e) {
    $veiculos = [];
    flash_set("danger", "Não foi possível carregar a lista de veículos.");
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="form-card" style="max-width: 1000px; margin: 30px auto;">
    <h1 style="color: #ffffff; margin-bottom: 20px;">Painel de Veículos (Admin)</h1>

    <?= flash_render(true) ?>

    <!-- ADICIONAR -->
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 18px 20px; margin-bottom: 25px;">
        <div>
            <strong style="color: #ffffff; font-size: 16px;">1. Adicionar carro</strong>
            <div style="color: #94a3b8; font-size: 13px;">Cadastra um novo veículo no estoque.</div>
        </div>
        <a href="<?= url('cadastrar_carros.php') ?>" class="btn">+ Adicionar Novo Carro</a>
    </div>

    <!-- ATUALIZAR / DELETAR -->
    <strong style="color: #ffffff; font-size: 16px; display: block; margin-bottom: 4px;">
        2. Atualizar e 3. Deletar carros
    </strong>
    <div style="color: #94a3b8; font-size: 13px; margin-bottom: 10px;">
        Use os botões em cada linha para editar os dados ou excluir o veículo.
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Marca / Modelo</th>
                    <th>Ano</th>
                    <th>Preço</th>
                    <th>Combustível</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($veiculos)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8;">
                            Nenhum veículo cadastrado no sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($veiculos as $v): ?>
                        <tr>
                            <td><?= (int)$v['id'] ?></td>
                            <td>
                                <?php if (!empty($v['imagem'])): ?>
                                    <img src="<?= htmlspecialchars(upload_url($v['imagem'])) ?>"
                                         alt="<?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>"
                                         style="width: 72px; height: 52px; object-fit: cover; border-radius: 6px; border: 1px solid #334155;">
                                <?php else: ?>
                                    <span style="color: #64748b;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></strong></td>
                            <td><?= (int)$v['ano'] ?></td>
                            <td style="color: #22c55e; font-weight: 700;">
                                R$ <?= number_format((float)$v['preco'], 2, ',', '.') ?>
                            </td>
                            <td><?= htmlspecialchars($v['combustivel'] ?? '—') ?></td>
                            <td style="text-align: center;">
                                <div class="actions" style="justify-content: center;">
                                    <!-- ATUALIZAR -->
                                    <a href="<?= url('editar_carro.php') ?>?id=<?= (int)$v['id'] ?>" class="btn btn-warning btn-sm">
                                        Editar
                                    </a>
                                    <!-- DELETAR -->
                                    <form action="<?= url('login/gerenciar_carros.php') ?>" method="POST" style="display: inline;"
                                          onsubmit="return confirm('Deseja realmente excluir este veículo? Esta ação não pode ser desfeita.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Excluir
                                        </button>
                                    </form>
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
