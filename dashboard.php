<?php
require_once __DIR__ . '/config/db.php';
$pageName = 'Dashboard';
requireLogin();

$pdo = getDB();
$uid = currentUser()['id'];

// Status
$stats = $pdo->query("
  SELECT
    COUNT(*) AS total,
    SUM(valor) AS total_valor,
    SUM(CASE WHEN status='pendente' THEN 1 ELSE 0 END) AS pendentes,
    SUM(CASE WHEN status='vencido'  OR (status='pendente' AND data_vencimento < CURDATE()) THEN 1 ELSE 0 END) AS vencidas,
    SUM(CASE WHEN status='pago'     THEN 1 ELSE 0 END) AS pagas,
    SUM(CASE WHEN status='pago'     THEN valor ELSE 0 END) AS total_pago,
    SUM(CASE WHEN status IN ('pendente','aprovado') THEN valor ELSE 0 END) AS total_pendente
  FROM tbContasPagar
")->fetch();

// Próximas a vencer (7 dias)
$proximas = $pdo->query("
  SELECT cp.*, tt.descricao AS tipo, p.nome AS fornecedor_nome
  FROM tbContasPagar cp
  LEFT JOIN tbTipoTitulo tt ON tt.tipo_titulo_id = cp.tipo_titulo_id
  LEFT JOIN tbPessoas p     ON p.pessoa_id        = cp.fornecedor_id
  WHERE cp.status IN ('pendente','aprovado')
    AND cp.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
  ORDER BY cp.data_vencimento
  LIMIT 10
")->fetchAll();

// Últimas contas
$ultimas = $pdo->query("
  SELECT cp.*, tt.descricao AS tipo, p.nome AS fornecedor_nome
  FROM tbContasPagar cp
  LEFT JOIN tbTipoTitulo tt ON tt.tipo_titulo_id = cp.tipo_titulo_id
  LEFT JOIN tbPessoas p     ON p.pessoa_id        = cp.fornecedor_id
  ORDER BY cp.conta_pagar_id DESC
  LIMIT 8
")->fetchAll();

$pageHeader   = 'Dashboard';
$pageSubtitle = 'Visão geral das contas a pagar';
$pageAction   = '<a href="contas_pagar_form.php" class="btn btn--gold">+ Nova Conta</a>';
include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-card__label">Total de Contas</div>
    <div class="stat-card__value"><?= (int)$stats['total'] ?></div>
    <div class="stat-card__sub">registros</div>
  </div>
  <div class="stat-card stat-card--danger">
    <div class="stat-card__label">Pendentes / Vencidas</div>
    <div class="stat-card__value"><?= (int)$stats['pendentes'] ?></div>
    <div class="stat-card__sub"><?= (int)$stats['vencidas'] ?> vencidas</div>
  </div>
  <div class="stat-card stat-card--success">
    <div class="stat-card__label">Total Pago</div>
    <div class="stat-card__value"><?= formatMoney($stats['total_pago'] ?? 0) ?></div>
    <div class="stat-card__sub"><?= (int)$stats['pagas'] ?> contas pagas</div>
  </div>
  <div class="stat-card stat-card--info">
    <div class="stat-card__label">A Pagar</div>
    <div class="stat-card__value"><?= formatMoney($stats['total_pendente'] ?? 0) ?></div>
    <div class="stat-card__sub">saldo devedor</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;flex-wrap:wrap;">

  <!-- Proximas a vencer -->
  <div class="card">
    <div class="card__header">
      <span class="card__title">⚠ Próximas a Vencer (7 dias)</span>
      <a href="contas_pagar.php?filtro=vencimento" class="btn btn--ghost btn--sm">Ver todas</a>
    </div>
    <?php if ($proximas): ?>
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Descrição</th><th>Fornecedor</th><th>Vencimento</th><th>Valor</th>
        </tr></thead>
        <tbody>
        <?php foreach ($proximas as $c): ?>
          <tr>
            <td><?= h($c['descricao'] ?? $c['tipo']) ?></td>
            <td><?= h($c['fornecedor_nome'] ?? '—') ?></td>
            <td><?= formatDate($c['data_vencimento']) ?></td>
            <td><?= formatMoney($c['valor']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state" style="padding:2rem;">
        <div class="empty-state__icon">🎉</div>
        <div class="empty-state__title">Nenhuma conta vence nos próximos 7 dias</div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Últimas contas -->
  <div class="card">
    <div class="card__header">
      <span class="card__title">📋 Últimas Contas Registradas</span>
      <a href="contas_pagar.php" class="btn btn--ghost btn--sm">Ver todas</a>
    </div>
    <?php if ($ultimas): ?>
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Descrição</th><th>Valor</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php foreach ($ultimas as $c): ?>
          <tr>
            <td><?= h($c['descricao'] ?? $c['tipo']) ?></td>
            <td><?= formatMoney($c['valor']) ?></td>
            <td><?= statusBadge($c['status']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state" style="padding:2rem;">
        <div class="empty-state__icon">📭</div>
        <div class="empty-state__title">Nenhuma conta registrada</div>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
