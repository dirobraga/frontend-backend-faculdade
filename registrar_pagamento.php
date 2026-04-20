<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo = getDB();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: contas_pagar.php'); exit;
}
$id = (int)$_GET['id'];
$stmt = $pdo->prepare('SELECT cp.*, tt.descricao AS tipo, p.nome AS fornecedor_nome FROM tbContasPagar cp LEFT JOIN tbTipoTitulo tt ON tt.tipo_titulo_id=cp.tipo_titulo_id LEFT JOIN tbPessoas p ON p.pessoa_id=cp.fornecedor_id WHERE cp.conta_pagar_id=?');
$stmt->execute([$id]);
$conta = $stmt->fetch();
if (!$conta) { header('Location: contas_pagar.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_pag = $_POST['data_pagamento'] ?? date('Y-m-d');
    $pdo->prepare('UPDATE tbContasPagar SET data_pagamento=?, status=\'pago\', atualizado_por=?, atualizado_em=CURDATE() WHERE conta_pagar_id=?')
        ->execute([$data_pag, currentUser()['id'], $id]);
    setFlash('success', 'Pagamento registrado com sucesso!');
    header('Location: contas_pagar.php'); exit;
}

$pageName     = 'Pagamento';
$pageHeader   = 'Registrar Pagamento';
$pageSubtitle = 'Confirme o pagamento da conta';
$pageAction   = '<a href="contas_pagar.php" class="btn btn--ghost">← Voltar</a>';
include __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:500px;">
  <div class="card__header">
    <span class="card__title">Detalhes da Conta</span>
  </div>
  <div class="card__body">
    <table style="width:100%;font-size:.9rem;margin-bottom:1.5rem;">
      <tr><td style="color:var(--gray-400);padding:.4rem 0;width:40%">Descrição</td><td><?= h($conta['descricao'] ?? $conta['tipo']) ?></td></tr>
      <tr><td style="color:var(--gray-400);padding:.4rem 0;">Fornecedor</td><td><?= h($conta['fornecedor_nome'] ?? '—') ?></td></tr>
      <tr><td style="color:var(--gray-400);padding:.4rem 0;">Tipo</td><td><?= h($conta['tipo']) ?></td></tr>
      <tr><td style="color:var(--gray-400);padding:.4rem 0;">Vencimento</td><td><?= formatDate($conta['data_vencimento']) ?></td></tr>
      <tr><td style="color:var(--gray-400);padding:.4rem 0;">Valor</td><td style="font-weight:700;font-size:1.1rem;"><?= formatMoney($conta['valor']) ?></td></tr>
      <tr><td style="color:var(--gray-400);padding:.4rem 0;">Status</td><td><?= statusBadge($conta['status']) ?></td></tr>
    </table>

    <form method="POST">
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label>Data do Pagamento *</label>
        <input type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn btn--success">✓ Confirmar Pagamento</button>
        <a href="contas_pagar.php" class="btn btn--ghost">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
