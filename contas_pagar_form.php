<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo = getDB();

// Editar
$conta = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM tbContasPagar WHERE conta_pagar_id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $conta = $stmt->fetch();
}

// Salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id             = (int)($_POST['conta_pagar_id'] ?? 0);
    $valor          = str_replace(['.', ','], ['', '.'], $_POST['valor'] ?? '0');
    $data_venc      = $_POST['data_vencimento'] ?? '';
    $data_pag       = $_POST['data_pagamento']  ?: null;
    $tipo_id        = (int)($_POST['tipo_titulo_id'] ?? 1);
    $fornecedor_id  = ($_POST['fornecedor_id'] !== '') ? (int)$_POST['fornecedor_id'] : null;
    $descricao      = trim($_POST['descricao'] ?? '');
    $status         = $_POST['status'] ?? 'pendente';
    $user_id        = currentUser()['id'];

    if ($valor <= 0 || $data_venc === '') {
        setFlash('error', 'Valor e data de vencimento são obrigatórios.');
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }

    if ($id > 0) {
        $pdo->prepare('UPDATE tbContasPagar SET valor=?,data_vencimento=?,data_pagamento=?,tipo_titulo_id=?,fornecedor_id=?,descricao=?,status=?,atualizado_por=?,atualizado_em=CURDATE() WHERE conta_pagar_id=?')
            ->execute([$valor, $data_venc, $data_pag, $tipo_id, $fornecedor_id, $descricao, $status, $user_id, $id]);
        setFlash('success', 'Conta atualizada com sucesso.');
    } else {
        $pdo->prepare('INSERT INTO tbContasPagar (valor,data_vencimento,data_pagamento,tipo_titulo_id,fornecedor_id,descricao,status,atualizado_por,atualizado_em) VALUES (?,?,?,?,?,?,?,?,CURDATE())')
            ->execute([$valor, $data_venc, $data_pag, $tipo_id, $fornecedor_id, $descricao, $status, $user_id]);
        setFlash('success', 'Conta cadastrada com sucesso.');
    }
    header('Location: contas_pagar.php'); exit;
}

$tipos       = $pdo->query('SELECT * FROM tbTipoTitulo ORDER BY descricao')->fetchAll();
$fornecedores = $pdo->query("SELECT pessoa_id, nome FROM tbPessoas ORDER BY nome")->fetchAll();

$isEdit       = $conta !== null;
$pageName     = $isEdit ? 'Editar Conta' : 'Nova Conta';
$pageHeader   = $isEdit ? 'Editar Conta' : 'Nova Conta a Pagar';
$pageSubtitle = $isEdit ? 'Atualize os dados da conta' : 'Registre uma nova obrigação financeira';
$pageAction   = '<a href="contas_pagar.php" class="btn btn--ghost">← Voltar</a>';
include __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:800px;">
  <div class="card__body">
    <form method="POST" action="contas_pagar_form.php">
      <?php if ($isEdit): ?>
        <input type="hidden" name="conta_pagar_id" value="<?= $conta['conta_pagar_id'] ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group full">
          <label>Descrição</label>
          <textarea name="descricao" placeholder="Descreva a conta, referência, nota fiscal..."><?= h($conta['descricao'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label>Valor (R$) *</label>
          <input type="text" name="valor" required
                 value="<?= $isEdit ? number_format((float)$conta['valor'], 2, ',', '.') : '' ?>"
                 placeholder="0,00">
        </div>

        <div class="form-group">
          <label>Tipo de Título *</label>
          <select name="tipo_titulo_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['tipo_titulo_id'] ?>" <?= (($conta['tipo_titulo_id'] ?? '') == $t['tipo_titulo_id']) ? 'selected' : '' ?>>
                <?= h($t['descricao']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Data de Vencimento *</label>
          <input type="date" name="data_vencimento" required
                 value="<?= h($conta['data_vencimento'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Data de Pagamento</label>
          <input type="date" name="data_pagamento"
                 value="<?= h($conta['data_pagamento'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Fornecedor</label>
          <select name="fornecedor_id">
            <option value="">— Nenhum —</option>
            <?php foreach ($fornecedores as $f): ?>
              <option value="<?= $f['pessoa_id'] ?>" <?= (($conta['fornecedor_id'] ?? '') == $f['pessoa_id']) ? 'selected' : '' ?>>
                <?= h($f['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <?php foreach (['pendente','aprovado','pago','cancelado','vencido'] as $s): ?>
              <option value="<?= $s ?>" <?= (($conta['status'] ?? 'pendente') === $s) ? 'selected' : '' ?>>
                <?= ucfirst($s) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display:flex;gap:.75rem;margin-top:1.75rem;">
        <button type="submit" class="btn btn--gold">💾 Salvar Conta</button>
        <a href="contas_pagar.php" class="btn btn--ghost">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
