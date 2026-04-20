<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo = getDB();

// Deletar conta a pagar
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $pdo->prepare('DELETE FROM tbContasPagar WHERE conta_pagar_id = ?')->execute([(int)$_GET['del']]);
    setFlash('success', 'Conta excluída.');
    header('Location: contas_pagar.php'); exit;
}

// statys da conta
if (isset($_GET['status'], $_GET['id']) && is_numeric($_GET['id'])) {
    $allowed = ['aprovado','cancelado','pendente'];
    if (in_array($_GET['status'], $allowed)) {
        $pdo->prepare('UPDATE tbContasPagar SET status=?, atualizado_por=?, atualizado_em=CURDATE() WHERE conta_pagar_id=?')
            ->execute([$_GET['status'], currentUser()['id'], (int)$_GET['id']]);
        setFlash('success', 'Status atualizado.');
    }
    header('Location: contas_pagar.php'); exit;
}

$status_filter = $_GET['status'] ?? '';
$where = ['1=1'];
$params = [];
if ($status_filter) {
    $where[] = 'cp.status = ?';
    $params[] = $status_filter;
}
$q = implode(' AND ', $where);

$contas = $pdo->prepare("
  SELECT cp.*, tt.descricao AS tipo, p.nome AS fornecedor_nome
  FROM tbContasPagar cp
  LEFT JOIN tbTipoTitulo tt ON tt.tipo_titulo_id = cp.tipo_titulo_id
  LEFT JOIN tbPessoas p     ON p.pessoa_id        = cp.fornecedor_id
  WHERE $q
  ORDER BY cp.data_vencimento ASC, cp.conta_pagar_id DESC
");
$contas->execute($params);
$contas = $contas->fetchAll();

$pageName     = 'Contas';
$pageHeader   = 'Contas a Pagar';
$pageSubtitle = 'Gerencie todas as contas';
$pageAction   = '<a href="contas_pagar_form.php" class="btn btn--gold">+ Nova Conta</a>';
include __DIR__ . '/includes/header.php';
?>

<!-- Filtrros -->
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center;">
  <span style="font-size:.85rem;color:var(--gray-400);font-weight:600;">Filtrar:</span>
  <?php
  $statuses = ['' => 'Todos', 'pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'];
  foreach ($statuses as $val => $label):
    $active = $status_filter === $val;
  ?>
    <a href="contas_pagar.php<?= $val ? '?status='.$val : '' ?>" class="btn btn--sm <?= $active ? 'btn--primary' : 'btn--ghost' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php if ($contas): ?>
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Descrição</th><th>Fornecedor</th><th>Tipo</th>
        <th>Vencimento</th><th>Pagamento</th><th>Valor</th><th>Status</th><th>Ações</th>
      </tr></thead>
      <tbody>
      <?php foreach ($contas as $c): ?>
        <tr>
          <td><?= $c['conta_pagar_id'] ?></td>
          <td><?= h($c['descricao'] ?? '—') ?></td>
          <td><?= h($c['fornecedor_nome'] ?? '—') ?></td>
          <td><?= h($c['tipo']) ?></td>
          <td><?= formatDate($c['data_vencimento']) ?></td>
          <td><?= formatDate($c['data_pagamento']) ?></td>
          <td><?= formatMoney($c['valor']) ?></td>
          <td><?= statusBadge($c['status']) ?></td>
          <td style="white-space:nowrap;">
            <a href="contas_pagar_form.php?id=<?= $c['conta_pagar_id'] ?>" class="btn btn--ghost btn--sm">✏</a>
            <?php if ($c['status'] === 'pendente'): ?>
              <a href="contas_pagar.php?status=aprovado&id=<?= $c['conta_pagar_id'] ?>" class="btn btn--sm btn--primary" data-confirm="Aprovar esta conta?">✓ Aprovar</a>
            <?php endif; ?>
            <?php if (in_array($c['status'], ['pendente','aprovado'])): ?>
              <a href="registrar_pagamento.php?id=<?= $c['conta_pagar_id'] ?>" class="btn btn--sm btn--success">$ Pagar</a>
              <a href="contas_pagar.php?status=cancelado&id=<?= $c['conta_pagar_id'] ?>" class="btn btn--sm btn--danger" data-confirm="Cancelar esta conta?">✕</a>
            <?php endif; ?>
            <a href="contas_pagar.php?del=<?= $c['conta_pagar_id'] ?>" class="btn btn--sm btn--danger" data-confirm="Excluir esta conta?">🗑</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-state__icon">📭</div>
      <div class="empty-state__title">Nenhuma conta encontrada</div>
      <p><a href="contas_pagar_form.php" class="btn btn--gold" style="margin-top:1rem;">+ Nova Conta</a></p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
