<?php
require_once __DIR__ . '/includes/helpers.php';
session_start();
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/imagem.php';
require_once __DIR__ . '/database/connect.php';

try {
    $stmt = $pdo->query(
        "SELECT *
         FROM veiculos
         ORDER BY data_cadastro DESC"
    );
    $veiculos = $stmt->fetchAll();
} catch (PDOException $e) {
    $veiculos = [];
    $erro = 'Não foi possível carregar o estoque de veículos.';
}

require_once __DIR__ . '/includes/header.php';
?>

<?= flash_render() ?>

<?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="font-size: 28px; color: #ffffff; margin: 0 0 6px 0; font-weight: 700;">Estoque de Veículos</h2>
        <p style="color: #94a3b8; margin: 0; font-size: 15px;">Gerencie e visualize as ofertas ativas na revenda AutosJulius.</p>
    </div>
    <?php if (($_SESSION['tipo'] ?? '') === 'admin'): ?>
        <a href="<?= url('login/gerenciar_carros.php') ?>" class="btn">+ Gerenciar Veículos</a>
    <?php endif; ?>
</div>

<?php if (empty($veiculos) && !isset($erro)): ?>
    <div class="alert alert-warning">Nenhum veículo cadastrado no momento.</div>
<?php elseif (!empty($veiculos)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
        <?php foreach ($veiculos as $v): ?>
            <div style="background: #1e293b; border-radius: 12px; overflow: hidden; border: 1px solid #334155; display: flex; flex-direction: column; padding: 24px;">
                <div style="flex: 1;">
                    <?php if (!empty($v['imagem'])): ?>
                        <img src="<?= htmlspecialchars(upload_url($v['imagem'])) ?>"
                             alt="<?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>"
                             loading="lazy"
                             style="width: 100%; height: 190px; object-fit: cover; border-radius: 8px; margin-bottom: 14px; border: 1px solid #334155;">
                    <?php else: ?>
                        <div style="width: 100%; height: 190px; border-radius: 8px; margin-bottom: 14px; border: 1px dashed #475569; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 14px; font-weight: 600;">
                            Sem foto
                        </div>
                    <?php endif; ?>

                    <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 700; color: #f8fafc;">
                        <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>
                    </h3>

                    <p style="color: #94a3b8; font-size: 14px; margin: 0 0 6px 0;">
                        <strong style="color: #cbd5e1;">Ano:</strong> <?= (int)$v['ano'] ?>
                    </p>
                    <p style="color: #94a3b8; font-size: 14px; margin: 0 0 6px 0;">
                        <strong style="color: #cbd5e1;">KM:</strong>
                        <?= isset($v['quilometros']) && $v['quilometros'] !== null ? number_format((int)$v['quilometros'], 0, ',', '.') . ' km' : 'Não informado' ?>
                    </p>
                    <?php if (!empty($v['combustivel'])): ?>
                        <p style="color: #94a3b8; font-size: 14px; margin: 0 0 6px 0;">
                            <strong style="color: #cbd5e1;">Combustível:</strong> <?= htmlspecialchars($v['combustivel']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($v['cambio'])): ?>
                        <p style="color: #94a3b8; font-size: 14px; margin: 0 0 6px 0;">
                            <strong style="color: #cbd5e1;">Câmbio:</strong> <?= htmlspecialchars($v['cambio']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div style="border-top: 1px solid #334155; padding-top: 16px; margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 22px; font-weight: 800; color: #22c55e;">
                        R$ <?= number_format((float)$v['preco'], 2, ',', '.') ?>
                    </span>
                    <button type="button" class="btn btn-secondary js-detalhes"
                            data-veiculo='<?= htmlspecialchars(json_encode([
                                'marca'       => $v['marca'],
                                'modelo'      => $v['modelo'],
                                'ano'         => (int)$v['ano'],
                                'preco'       => number_format((float)$v['preco'], 2, ',', '.'),
                                'quilometros' => isset($v['quilometros']) && $v['quilometros'] !== null ? number_format((int)$v['quilometros'], 0, ',', '.') . ' km' : 'Não informado',
                                'combustivel' => !empty($v['combustivel']) ? $v['combustivel'] : 'Não informado',
                                'cambio'      => !empty($v['cambio']) ? $v['cambio'] : 'Não informado',
                                'pecas'       => !empty($v['pecas']) ? $v['pecas'] : 'Nenhuma informação registrada.',
                                'descricao'   => !empty($v['descricao']) ? $v['descricao'] : 'Sem descrição.',
                                'imagem'      => !empty($v['imagem']) ? upload_url($v['imagem']) : '',
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
                        Ver Detalhes
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal de Detalhes -->
<div id="modal-detalhes" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; max-width: 550px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="m-titulo" style="color: #ffffff; margin: 0; font-size: 22px;"></h3>
            <button type="button" onclick="fecharModal()" style="background: none; border: none; color: #94a3b8; font-size: 26px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <img id="m-imagem" src="" alt=""
             style="display: none; width: 100%; max-height: 280px; object-fit: cover; border-radius: 8px; margin-bottom: 18px; border: 1px solid #334155;">

        <table style="width: 100%; border-collapse: collapse; background: transparent;">
            <tbody>
                <tr><td style="color: #94a3b8; font-weight: 600; width: 40%;">Ano</td><td id="m-ano" style="color: #f8fafc;"></td></tr>
                <tr><td style="color: #94a3b8; font-weight: 600;">Quilômetros</td><td id="m-km" style="color: #f8fafc;"></td></tr>
                <tr><td style="color: #94a3b8; font-weight: 600;">Combustível</td><td id="m-combustivel" style="color: #f8fafc;"></td></tr>
                <tr><td style="color: #94a3b8; font-weight: 600;">Câmbio</td><td id="m-cambio" style="color: #f8fafc;"></td></tr>
                <tr><td style="color: #94a3b8; font-weight: 600;">Preço</td><td id="m-preco" style="color: #22c55e; font-weight: 800;"></td></tr>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <h4 style="color: #cbd5e1; font-size: 15px; margin-bottom: 6px;">Peças e Revisões</h4>
            <p id="m-pecas" style="color: #94a3b8; font-size: 14px; margin: 0;"></p>
        </div>

        <div style="margin-top: 16px;">
            <h4 style="color: #cbd5e1; font-size: 15px; margin-bottom: 6px;">Descrição</h4>
            <p id="m-descricao" style="color: #94a3b8; font-size: 14px; margin: 0;"></p>
        </div>

        <button type="button" class="btn" onclick="fecharModal()" style="width: 100%; margin-top: 25px;">Fechar</button>
    </div>
</div>

<script>
function abrirDetalhes(d) {
    document.getElementById('m-titulo').textContent = d.marca + ' ' + d.modelo;

    var img = document.getElementById('m-imagem');
    if (d.imagem) {
        img.src = d.imagem;
        img.alt = d.marca + ' ' + d.modelo;
        img.style.display = 'block';
    } else {
        img.src = '';
        img.alt = '';
        img.style.display = 'none';
    }

    document.getElementById('m-ano').textContent = d.ano;
    document.getElementById('m-km').textContent = d.quilometros;
    document.getElementById('m-combustivel').textContent = d.combustivel;
    document.getElementById('m-cambio').textContent = d.cambio;
    document.getElementById('m-preco').textContent = 'R$ ' + d.preco;
    document.getElementById('m-pecas').textContent = d.pecas;
    document.getElementById('m-descricao').textContent = d.descricao;
    document.getElementById('modal-detalhes').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modal-detalhes').style.display = 'none';
}

document.querySelectorAll('.js-detalhes').forEach(function (btn) {
    btn.addEventListener('click', function () {
        abrirDetalhes(JSON.parse(this.getAttribute('data-veiculo')));
    });
});

document.getElementById('modal-detalhes').addEventListener('click', function (e) {
    if (e.target === this) fecharModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') fecharModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
