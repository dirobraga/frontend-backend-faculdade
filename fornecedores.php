<?php
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo = getDB();

// Deletar
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];
    // Checar se tem conta, caso tenha flash
    $chk = $pdo->prepare('SELECT COUNT(*) FROM tbContasPagar WHERE fornecedor_id = ?');
    $chk->execute([$id]);
    if ($chk->fetchColumn() > 0) {
        setFlash('error', 'Não é possível excluir: fornecedor possui contas a pagar vinculadas.');
    } else {
        $pdo->prepare('DELETE FROM tbPessoas WHERE pessoa_id = ?')->execute([$id]);
        setFlash('success', 'Fornecedor excluído com sucesso.');
    }
    header('Location: fornecedores.php'); exit;
}

// Salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int)($_POST['pessoa_id'] ?? 0);
    $nome      = trim($_POST['nome']      ?? '');
    $cpf       = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $nascimento= $_POST['nascimento']     ?? '';
    $telefone  = trim($_POST['telefone']  ?? '');
    $tipo_id   = (int)($_POST['pessoa_tipo_id'] ?? 1);

    if ($nome === '' || $cpf === '' || $nascimento === '') {
        setFlash('error', 'Nome, CPF e data de nascimento são obrigatórios.');
    } else {
        $user_id = currentUser()['id'];
        if ($id > 0) {
            $pdo->prepare('UPDATE tbPessoas SET nome=?, cpf=?, nascimento=?, telefone=?, pessoa_tipo_id=?, atualizado_por=?, atualizado_em=CURDATE() WHERE pessoa_id=?')
                ->execute([$nome, $cpf, $nascimento, $telefone, $tipo_id, $user_id, $id]);
            setFlash('success', 'Fornecedor atualizado com sucesso.');
        } else {
            $pdo->prepare('INSERT INTO tbPessoas (nome,cpf,nascimento,telefone,pessoa_tipo_id,atualizado_por,atualizado_em) VALUES (?,?,?,?,?,?,CURDATE())')
                ->execute([$nome, $cpf, $nascimento, $telefone, $tipo_id, $user_id]);
            setFlash('success', 'Fornecedor cadastrado com sucesso.');
        }
    }
    header('Location: fornecedores.php'); exit;
}

// Editar
$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = $pdo->prepare('SELECT * FROM tbPessoas WHERE pessoa_id = ?');
    $editing->execute([(int)$_GET['edit']]);
    $editing = $editing->fetch();
}

// Lista
$busca = trim($_GET['q'] ?? '');
if ($busca !== '') {
    $stmt = $pdo->prepare('SELECT p.*, pt.descricao AS tipo FROM tbPessoas p JOIN tbPessoaTipo pt ON pt.pessoa_tipo_id=p.pessoa_tipo_id WHERE p.nome LIKE ? OR p.cpf LIKE ? ORDER BY p.nome');
    $stmt->execute(["%$busca%", "%$busca%"]);
} else {
    $stmt = $pdo->query('SELECT p.*, pt.descricao AS tipo FROM tbPessoas p JOIN tbPessoaTipo pt ON pt.pessoa_tipo_id=p.pessoa_tipo_id ORDER BY p.nome');
}
$pessoas = $stmt->fetchAll();
$tipos   = $pdo->query('SELECT * FROM tbPessoaTipo ORDER BY descricao')->fetchAll();

$pageName     = 'Fornecedores';
$pageHeader   = 'Fornecedores';
$pageSubtitle = 'Cadastre e gerencie fornecedores';
$pageAction   = '<button onclick="document.getElementById(\'modal-form\').style.display=\'flex\'" class="btn btn--gold">+ Novo Fornecedor</button>';
include __DIR__ . '/includes/header.php';
?>

<!-- formulário adicinar fornecedor -->
<div id="modal-form" style="display:<?= $editing ? 'flex' : 'none' ?>;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;">
  <div class="card" style="width:100%;max-width:540px;max-height:90vh;overflow-y:auto;">
    <div class="card__header">
      <span class="card__title"><?= $editing ? 'Editar' : 'Novo' ?> Fornecedor</span>
      <button onclick="document.getElementById('modal-form').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400);">✕</button>
    </div>
    <div class="card__body">
      <form method="POST" action="fornecedores.php">
        <?php if ($editing): ?>
          <input type="hidden" name="pessoa_id" value="<?= $editing['pessoa_id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
          <div class="form-group full">
            <label>Nome *</label>
            <input type="text" name="nome" value="<?= h($editing['nome'] ?? '') ?>" required placeholder="Nome completo ou razão social">
          </div>
          <div class="form-group">
            <label>CPF / CNPJ *</label>
            <input type="text" name="cpf" value="<?= h($editing['cpf'] ?? '') ?>" required placeholder="000.000.000-00">
          </div>
          <div class="form-group">
            <label>Data de criação *</label>
            <input type="date" name="nascimento" value="<?= h($editing['nascimento'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label>Telefone / WhatsApp</label>
            <input type="tel" name="telefone" value="<?= h($editing['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
          </div>
          <div class="form-group">
            <label>Tipo de Pessoa</label>
            <select name="pessoa_tipo_id">
              <?php foreach ($tipos as $t): ?>
                <option value="<?= $t['pessoa_tipo_id'] ?>" <?= (($editing['pessoa_tipo_id'] ?? 1) == $t['pessoa_tipo_id']) ? 'selected' : '' ?>>
                  <?= h($t['descricao']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:1.5rem;">
          <button type="submit" class="btn btn--gold">Salvar</button>
          <button type="button" onclick="document.getElementById('modal-form').style.display='none'" class="btn btn--ghost">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Busca -->
<form method="GET" style="margin-bottom:1.25rem;display:flex;gap:.75rem;">
  <input type="text" name="q" value="<?= h($busca) ?>" placeholder="Buscar por nome ou CPF..." style="max-width:320px;">
  <button class="btn btn--primary">Buscar</button>
  <?php if ($busca): ?><a href="fornecedores.php" class="btn btn--ghost">Limpar</a><?php endif; ?>
</form>

<!-- Tabela -->
<div class="card">
  <?php if ($pessoas): ?>
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Nome</th><th>CPF/CNPJ</th><th>Telefone</th><th>Tipo</th><th>Ações</th>
      </tr></thead>
      <tbody>
      <?php foreach ($pessoas as $p): ?>
        <tr>
          <td><?= $p['pessoa_id'] ?></td>
          <td><?= h($p['nome']) ?></td>
          <td><?= h($p['cpf']) ?></td>
          <td><?= h($p['telefone'] ?: '—') ?></td>
          <td><?= h($p['tipo']) ?></td>
          <td>
            <a href="fornecedores.php?edit=<?= $p['pessoa_id'] ?>" class="btn btn--ghost btn--sm">✏ Editar</a>
            <a href="fornecedores.php?del=<?= $p['pessoa_id'] ?>" class="btn btn--danger btn--sm" data-confirm="Excluir <?= h($p['nome']) ?>?">✕</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-state__icon">🏢</div>
      <div class="empty-state__title">Nenhum fornecedor encontrado</div>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
