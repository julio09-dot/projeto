<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/imagem.php';
require_once __DIR__ . '/database/connect.php';

// Proteção: apenas administradores podem cadastrar veículos
if (!isset($_SESSION['id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
    redirect('login/login.php');
}

// Utilizador autenticado que fica associado ao registo
$usuario_id = $_SESSION['id'] ?? null;

$erros = [];
$mensagem = null;
$dados = ['marca' => '', 'modelo' => '', 'ano' => '', 'preco' => '', 'quilometros' => '', 'combustivel' => '', 'cambio' => '', 'pecas' => '', 'descricao' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $dados['marca']       = trim($_POST['marca'] ?? '');
    $dados['modelo']      = trim($_POST['modelo'] ?? '');
    $dados['ano']         = trim($_POST['ano'] ?? '');
    $dados['preco']       = trim($_POST['preco'] ?? '');
    $dados['quilometros'] = trim($_POST['quilometros'] ?? '');
    $dados['combustivel'] = trim($_POST['combustivel'] ?? '');
    $dados['cambio']      = trim($_POST['cambio'] ?? '');
    $dados['pecas']       = trim($_POST['pecas'] ?? '');
    $dados['descricao']   = trim($_POST['descricao'] ?? '');

    $ano = filter_var($dados['ano'], FILTER_VALIDATE_INT);
    $preco = filter_var($dados['preco'], FILTER_VALIDATE_FLOAT);
    $quilometros = $dados['quilometros'] === '' ? null : filter_var($dados['quilometros'], FILTER_VALIDATE_INT);

    if ($dados['marca'] === '' || mb_strlen($dados['marca']) > 50) {
        $erros[] = 'Informe uma marca válida (máx. 50 caracteres).';
    }
    if ($dados['modelo'] === '' || mb_strlen($dados['modelo']) > 50) {
        $erros[] = 'Informe um modelo válido (máx. 50 caracteres).';
    }
    if ($ano === false || $ano < 1900 || $ano > (int)date('Y') + 1) {
        $erros[] = 'Informe um ano válido entre 1900 e ' . ((int)date('Y') + 1) . '.';
    }
    if ($preco === false || $preco <= 0) {
        $erros[] = 'Informe um preço válido maior que zero.';
    }
    if ($dados['quilometros'] !== '' && ($quilometros === false || $quilometros < 0)) {
        $erros[] = 'Informe uma quilometragem válida (0 ou mais).';
    }

    $combustiveisValidos = ['Flex', 'Gasolina', 'Etanol', 'Diesel', 'Elétrico', 'Híbrido'];
    $cambiosValidos = ['Manual', 'Automático', 'CVT', 'Semi-automático'];

    if (!in_array($dados['combustivel'], $combustiveisValidos, true)) {
        $erros[] = 'Selecione o combustível.';
    }
    if (!in_array($dados['cambio'], $cambiosValidos, true)) {
        $erros[] = 'Selecione o câmbio.';
    }

    if (empty($erros)) {
        [$imagem, $erroImagem] = upload_imagem($_FILES['imagem'] ?? []);
        if ($erroImagem !== null) {
            $erros[] = $erroImagem;
        }
    }

    if (empty($erros)) {
        try {
            $sql = "INSERT INTO veiculos (usuario_id, marca, modelo, ano, preco, quilometros, combustivel, cambio, pecas, descricao, imagem)
                    VALUES (:usuario_id, :marca, :modelo, :ano, :preco, :quilometros, :combustivel, :cambio, :pecas, :descricao, :imagem)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':marca', $dados['marca']);
            $stmt->bindValue(':modelo', $dados['modelo']);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindValue(':preco', $preco);
            $stmt->bindValue(':quilometros', $quilometros, $quilometros === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':combustivel', $dados['combustivel']);
            $stmt->bindValue(':cambio', $dados['cambio']);
            $stmt->bindValue(':pecas', $dados['pecas'] !== '' ? $dados['pecas'] : null);
            $stmt->bindValue(':descricao', $dados['descricao'] !== '' ? $dados['descricao'] : null);
            $stmt->bindValue(':imagem', $imagem);

            $stmt->execute();
            flash_set('success', 'Veículo registado com sucesso! Ele já aparece na página inicial.');
            redirect('cadastrar_carros.php');
        } catch (PDOException $e) {
            upload_remover($imagem);
            $mensagem = ['tipo' => 'danger', 'texto' => 'Erro ao guardar o veículo. Tente novamente.'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="max-width: 800px;">
    <h2 style="margin-bottom: 20px; color: #ffffff;">Adicionar Veículo para Revenda</h2>

    <?= flash_render(true) ?>

    <?php if ($mensagem !== null): ?>
        <div class="alert alert-<?= $mensagem['tipo'] ?>">
            <?= htmlspecialchars($mensagem['texto']) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($erros as $erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endforeach; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 220px;">
                <label for="marca">Marca <span class="required">*</span></label>
                <input type="text" name="marca" id="marca" maxlength="50" required
                       placeholder="Ex: Toyota" value="<?= htmlspecialchars($dados['marca']) ?>">
            </div>

            <div class="form-group" style="flex: 1; min-width: 220px;">
                <label for="modelo">Modelo <span class="required">*</span></label>
                <input type="text" name="modelo" id="modelo" maxlength="50" required
                       placeholder="Ex: Corolla" value="<?= htmlspecialchars($dados['modelo']) ?>">
            </div>
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="ano">Ano <span class="required">*</span></label>
                <input type="number" name="ano" id="ano" min="1900" max="<?= (int)date('Y') + 1 ?>" required
                       placeholder="Ex: 2022" value="<?= htmlspecialchars($dados['ano']) ?>">
            </div>

            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="preco">Preço (R$) <span class="required">*</span></label>
                <input type="number" step="0.01" min="0.01" name="preco" id="preco" required
                       placeholder="Ex: 25000.00" value="<?= htmlspecialchars($dados['preco']) ?>">
            </div>

            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="quilometros">Quilometragem (km)</label>
                <input type="number" min="0" name="quilometros" id="quilometros"
                       placeholder="Ex: 45000" value="<?= htmlspecialchars($dados['quilometros']) ?>">
            </div>
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 180px;">
                <label for="combustivel">Combustível <span class="required">*</span></label>
                <select name="combustivel" id="combustivel" required>
                    <option value="">Selecione...</option>
                    <?php foreach (['Flex', 'Gasolina', 'Etanol', 'Diesel', 'Elétrico', 'Híbrido'] as $op): ?>
                        <option value="<?= $op ?>" <?= $dados['combustivel'] === $op ? 'selected' : '' ?>><?= $op ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="flex: 1; min-width: 180px;">
                <label for="cambio">Câmbio <span class="required">*</span></label>
                <select name="cambio" id="cambio" required>
                    <option value="">Selecione...</option>
                    <?php foreach (['Manual', 'Automático', 'CVT', 'Semi-automático'] as $op): ?>
                        <option value="<?= $op ?>" <?= $dados['cambio'] === $op ? 'selected' : '' ?>><?= $op ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="pecas">Peças e Revisões</label>
            <textarea name="pecas" id="pecas" rows="2"
                      placeholder="Ex: Revisão dos 50.000 km feita; pastilhas trocadas; pneus novos..."><?= htmlspecialchars($dados['pecas']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3"
                      placeholder="Detalhes do veículo, itens extras, estado..."><?= htmlspecialchars($dados['descricao']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="imagem">Foto do Veículo</label>
            <input type="file" name="imagem" id="imagem" accept="image/jpeg,image/png,image/webp"
                   style="padding: 10px;">
            <small style="color: #64748b; display: block; margin-top: 6px; font-size: 12px;">
                JPG, PNG ou WEBP, até 5 MB. Opcional — sem foto o veículo mostra um espaço reservado.
            </small>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn" style="flex: 1;">Guardar Veículo</button>
            <a href="<?= url('login/gerenciar_carros.php') ?>" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
