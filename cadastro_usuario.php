<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = trim($_POST['nome']   ?? '');
    $login  = trim($_POST['login']  ?? '');
    $senha  = $_POST['senha']        ?? '';
    $senha2 = $_POST['senha2']       ?? '';

    if ($nome === '' || $login === '' || $senha === '') {
        $error = 'Preencha todos os campos obrigatórios.';
    } elseif (strlen($login) < 4) {
        $error = 'Login deve ter no mínimo 4 caracteres.';
    } elseif (strlen($senha) < 6) {
        $error = 'Senha deve ter no mínimo 6 caracteres.';
    } elseif ($senha !== $senha2) {
        $error = 'As senhas não coincidem.';
    } else {
        $pdo  = getDB();
        $chk  = $pdo->prepare('SELECT usuario_id FROM tbUsuarios WHERE login = ? LIMIT 1');
        $chk->execute([$login]);
        if ($chk->fetch()) {
            $error = 'Este login já está em uso. Escolha outro.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare('INSERT INTO tbUsuarios (nome, login, senha) VALUES (?, ?, ?)');
            $ins->execute([$nome, $login, $hash]);
            $success = 'Conta criada com sucesso! Você já pode fazer login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro — FinControl</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">₿ FinControl</div>
    <p class="auth-subtitle">Sistema de Contas a Pagar</p>

    <?php if ($error): ?>
      <div class="flash flash--error">
        <span class="flash__icon">✕</span>
        <span><?= h($error) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="flash flash--success">
        <span class="flash__icon">✓</span>
        <span><?= h($success) ?></span>
      </div>
    <?php endif; ?>

    <h2 class="auth-title">Criar Conta</h2>

    <?php if (!$success): ?>
    <form method="POST" action="cadastro_usuario.php">
      <div class="form-group" style="margin-bottom:1rem;">
        <label for="nome">Nome Completo *</label>
        <input type="text" id="nome" name="nome"
               value="<?= h($_POST['nome'] ?? '') ?>" placeholder="Seu nome" required>
      </div>
      <div class="form-group" style="margin-bottom:1rem;">
        <label for="login">Login *</label>
        <input type="text" id="login" name="login"
               value="<?= h($_POST['login'] ?? '') ?>" placeholder="Mínimo 4 caracteres"
               autocomplete="username" required>
      </div>
      <div class="form-group" style="margin-bottom:1rem;">
        <label for="senha">Senha *</label>
        <input type="password" id="senha" name="senha"
               placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
      </div>
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label for="senha2">Confirmar Senha *</label>
        <input type="password" id="senha2" name="senha2"
               placeholder="Repita a senha" autocomplete="new-password" required>
      </div>
      <button type="submit" class="btn btn--gold btn--full">Cadastrar</button>
    </form>
    <?php else: ?>
      <a href="login.php" class="btn btn--primary btn--full" style="justify-content:center;">Ir para Login</a>
    <?php endif; ?>

    <p class="auth-footer">
      Já tem conta? <a href="login.php">Entrar</a>
    </p>
    <p class="auth-footer" style="margin-top:.4rem;">
      <a href="index.php" style="color:var(--gray-400);">← Voltar ao início</a>
    </p>
  </div>
</div>
</body>
</html>
