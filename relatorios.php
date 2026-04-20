<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo = getDB();

// Filters
$mes  = $_GET['mes']  ?? date('Y-m');
$tipo = $_GET['tipo'] ?? '';

$where  = ['1=1'];
$params = [];
if ($mes) {
    $where[] = 'DATE_FORMAT(cp.data_vencimento, \'%Y-%m\') = ?';
    $params[] = $mes;
}
if ($tipo) {
    $where[] = 'cp.status = ?';
    $params[] = $tipo;
}
$q = implode(' AND ', $where);

// Summary by status
$summary = $pdo->prepare("
  SELECT status, COUNT(*) AS qtd, SUM(valor) AS total
  FROM tbContasPagar cp
  WHERE $q
  GROUP BY status
");
$summary->execute($params);
$summary = $summary->fetchAll();

// By supplier
$porFornecedor = $pdo->prepare("
  SELECT COALESCE(p.nome, 'Sem fornecedor') AS fornecedor, COUNT(*) AS qtd, SUM(cp.valor) AS total, SUM(CASE WHEN cp.status='pago' THEN cp.valor ELSE 0 END) AS pago
  FROM tbContasPagar cp
  LEFT JOIN tbPessoas p ON p.pessoa_id = cp.fornecedor_id
  WHERE $q
  GROUP BY cp.fornecedor_id, p.nome
  ORDER BY total DESC
  LIMIT 10
");
$porFornecedor->execute($params);
$porFornecedor = $porFornecedor->fetchAll();

// By type
$porTipo = $pdo->prepare("
  SELECT tt.descricao AS tipo, COUNT(*) AS qtd, SUM(cp.valor) AS total
  FROM tbContasPagar cp
  JOIN tbTipoTitulo tt ON tt.tipo_titulo_id = cp.tipo_titulo_id
  WHERE $q
  GROUP BY cp.tipo_titulo_id, tt.descricao
  ORDER BY total DESC
");
$porTipo->execute($params);
$porTipo = $porTipo->fetchAll();

// Totals
$totals = $pdo->prepare("SELECT COUNT(*) AS qtd, SUM(valor) AS total, SUM(CASE WHEN status='pago' THEN valor ELSE 0 END) AS pago, SUM(CASE WHEN status IN ('pendente','aprovado') THEN valor ELSE 0 END) AS pendente FROM tbContasPagar cp WHERE $q");
$totals->execute($params);
$totals = $totals->fetch();

$pageName     = 'Relatórios';
$pageHeader   = 'Relatórios Financeiros';
$pageSubtitle = 'Análise e resumo das contas';
include __DIR__ . '/includes/header.php';
?>

<!-- Filters -->
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card__body">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
      <div class="form-group" style="min-width:180px;">
        <label>Mês de Referência</label>
        <input type="month" name="mes" value="<?= h($mes) ?>">
      </div>
      <div class="form-group" style="min-width:160px;">
        <label>Status</label>
        <select name="tipo">
          <option value="">Todos</option>
          <?php foreach (['pendente','aprovado','pago','vencido','cancelado'] as $s): ?>
            <option value="<?= $s ?>" <?= $tipo===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn--primary">Filtrar</button>
      <a href="relatorios.php" class="btn btn--ghost">Limpar</a>
    </form>
  </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom:1.5rem;">
  <div class="stat-card">
    <div class="stat-card__label">Total de Contas</div>
    <div class="stat-card__value"><?= (int)$totals['qtd'] ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__label">Valor Total</div>
    <div class="stat-card__value"><?= formatMoney($totals['total'] ?? 0) ?></div>
  </div>
  <div class="stat-card stat-card--success">
    <div class="stat-card__label">Total Pago</div>
    <div class="stat-card__value"><?= formatMoney($totals['pago'] ?? 0) ?></div>
  </div>
  <div class="stat-card stat-card--danger">
    <div class="stat-card__label">Total Pendente</div>
    <div class="stat-card__value"><?= formatMoney($totals['pendente'] ?? 0) ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;flex-wrap:wrap;">

  <!-- Por Status -->
  <div class="card">
    <div class="card__header"><span class="card__title">Por Status</span></div>
    <?php if ($summary): ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Status</th><th>Quantidade</th><th>Valor Total</th></tr></thead>
        <tbody>
        <?php foreach ($summary as $r): ?>
          <tr>
            <td><?= statusBadge($r['status']) ?></td>
            <td><?= (int)$r['qtd'] ?></td>
            <td><?= formatMoney($r['total']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state"><div class="empty-state__icon">📊</div><div class="empty-state__title">Sem dados</div></div>
    <?php endif; ?>
  </div>

  <!-- Por Tipo de Título -->
  <div class="card">
    <div class="card__header"><span class="card__title">Por Tipo de Título</span></div>
    <?php if ($porTipo): ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Tipo</th><th>Qtd</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($porTipo as $r): ?>
          <tr>
            <td><?= h($r['tipo']) ?></td>
            <td><?= (int)$r['qtd'] ?></td>
            <td><?= formatMoney($r['total']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state"><div class="empty-state__icon">📋</div><div class="empty-state__title">Sem dados</div></div>
    <?php endif; ?>
  </div>

</div>

<!-- Por Fornecedor -->
<div class="card" style="margin-top:1.5rem;">
  <div class="card__header"><span class="card__title">Top 10 Fornecedores</span></div>
  <?php if ($porFornecedor): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Fornecedor</th><th>Qtd</th><th>Total</th><th>Total Pago</th><th>Saldo</th></tr></thead>
      <tbody>
      <?php foreach ($porFornecedor as $r): ?>
        <tr>
          <td><?= h($r['fornecedor']) ?></td>
          <td><?= (int)$r['qtd'] ?></td>
          <td><?= formatMoney($r['total']) ?></td>
          <td><?= formatMoney($r['pago']) ?></td>
          <td><?= formatMoney($r['total'] - $r['pago']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-state"><div class="empty-state__icon">🏢</div><div class="empty-state__title">Sem dados</div></div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
