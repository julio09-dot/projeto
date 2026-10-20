<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/imagem.php';
require_once __DIR__ . '/database/connect.php';

// Proteção da página: apenas administradores podem editar veículos
if (!isset($_SESSION['id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
    redirect('login/login.php');
}

$erros = [];
$vazio = ['marca' => '', 'modelo' => '', 'ano' => '', 'preco' => '', 'quilometros' => '', 'combustivel' => '', 'cambio' => '', 'pecas' => '', 'descricao' => ''];
$dados = $vazio;

$carro_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$carro_id) {
    redirect('login/gerenciar_carros.php');
}

// Carrega os dados atuais do veículo
try {
    $stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id = :id");
    $stmt->execute(['id' => $carro_id]);
    $veiculo = $stmt->fetch();
} catch (PDOException $e) {
    $veiculo = false;
}

if (!$veiculo) {
    redirect('login/gerenciar_carros.php');
}

$dados = [
    'marca'       => $veiculo['marca'],
    'modelo'      => $veiculo['modelo'],
    'ano'         => (string)$veiculo['ano'],
    'preco'       => (string)$veiculo['preco'],
    'quilometros' => $veiculo['quilometros'] !== null ? (string)$veiculo['quilometros'] : '',
    'combustivel' => $veiculo['combustivel'] ?? '',
    'cambio'      => $veiculo['cambio'] ?? '',
    'pecas'       => $veiculo['pecas'] ?? '',
    'descricao'   => $veiculo['descricao'] ?? '',
];

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

    $imagemAtual = $veiculo['imagem'] ?? null;
    $novaImagem  = $imagemAtual;
    $imagemEnviada = false;

    if (empty($erros)) {
        [$imagemNova, $erroImagem] = upload_imagem($_FILES['imagem'] ?? []);

        if ($erroImagem !== null) {
            $erros[] = $erroImagem;
        } elseif ($imagemNova !== null) {
            // Nova foto substitui a antiga
            $novaImagem = $imagemNova;
            $imagemEnviada = true;
        } elseif (!empty($_POST['remover_imagem'])) {
            $novaImagem = null;
        }
    }

    if (empty($erros)) {
        try {
            $sql = "UPDATE veiculos SET
                        marca = :marca,
                        modelo = :modelo,
                        ano = :ano,
                        preco = :preco,
                        quilometros = :quilometros,
                        combustivel = :combustivel,
                        cambio = :cambio,
                        pecas = :pecas,
                        descricao = :descricao,
                        imagem = :imagem
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $carro_id, PDO::PARAM_INT);
            $stmt->bindValue(':marca', $dados['marca']);
            $stmt->bindValue(':modelo', $dados['modelo']);
            $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindValue(':preco', $preco);
            $stmt->bindValue(':quilometros', $quilometros, $quilometros === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':combustivel', $dados['combustivel']);
            $stmt->bindValue(':cambio', $dados['cambio']);
            $stmt->bindValue(':pecas', $dados['pecas'] !== '' ? $dados['pecas'] : null);
            $stmt->bindValue(':descricao', $dados['descricao'] !== '' ? $dados['descricao'] : null);
            $stmt->bindValue(':imagem', $novaImagem);

            $stmt->execute();

            // Só apaga a foto antiga depois do banco confirmar
            if ($novaImagem !== $imagemAtual) {
                upload_remover($imagemAtual);
            }

            flash_set('success', 'Veículo atualizado com sucesso! As novas informações já aparecem na página inicial.');
            redirect('editar_carro.php?id=' . $carro_id);
        } catch (PDOException $e) {
            // Se a nova foto ficou órfã por falhar o UPDATE, remove
            if ($imagemEnviada) {
                upload_remover($novaImagem);
            }
            $erros[] = 'Erro ao atualizar o veículo. Tente novamente.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="max-width: 800px;">
    <h2 style="margin-bottom: 20px; color: #ffffff;">
        Editar Veículo #<?= (int)$veiculo['id'] ?> — <?= htmlspecialchars($veiculo['marca'] . ' ' . $veiculo['modelo']) ?>
    </h2>

    <?= flash_render(true) ?>

    <?php foreach ($erros as $erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endforeach; ?>

    <form action="<?= url('editar_carro.php') ?>?id=<?= (int)$carro_id ?>" method="POST" enctype="multipart/form-data">
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
                      placeholder="Ex: Revisão dos 50.000 km feita; pastilhas trocadas..."><?= htmlspecialchars($dados['pecas']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3"
                      placeholder="Detalhes do veículo, itens extras, estado..."><?= htmlspecialchars($dados['descricao']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Foto do Veículo</label>

            <?php if (!empty($veiculo['imagem'])): ?>
                <div style="margin-bottom: 12px;">
                    <img src="<?= htmlspecialchars(upload_url($veiculo['imagem'])) ?>"
                         alt="Foto atual de <?= htmlspecialchars($veiculo['marca'] . ' ' . $veiculo['modelo']) ?>"
                         style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 8px; border: 1px solid #334155;">
                    <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; color: #f87171; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="remover_imagem" value="1" style="width: auto;">
                        Remover foto atual
                    </label>
                </div>
            <?php else: ?>
                <p style="color: #64748b; font-size: 13px; margin-bottom: 10px;">Este veículo ainda não tem foto.</p>
            <?php endif; ?>

            <input type="file" name="imagem" id="imagem" accept="image/jpeg,image/png,image/webp"
                   style="padding: 10px;">
            <small style="color: #64748b; display: block; margin-top: 6px; font-size: 12px;">
                <?= !empty($veiculo['imagem']) ? 'Envie uma nova foto para substituir a atual.' : 'JPG, PNG ou WEBP, até 5 MB.' ?>
            </small>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn" style="flex: 1;">Salvar Alterações</button>
            <a href="<?= url('login/gerenciar_carros.php') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
