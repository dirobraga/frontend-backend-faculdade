<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($login === '' || $senha === '') {
        $error = 'Preencha login e senha.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT usuario_id, nome, senha FROM tbUsuarios WHERE login = ? LIMIT 1');
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id']   = $user['usuario_id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Login ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — FinControl</title>
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

    <h2 class="auth-title">Entrar</h2>

    <form method="POST" action="login.php">
      <div class="form-group" style="margin-bottom:1rem;">
        <label for="login">Login</label>
        <input type="text" id="login" name="login"
               value="<?= h($_POST['login'] ?? '') ?>"
               placeholder="Seu login" autocomplete="username" required>
      </div>
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha"
               placeholder="Sua senha" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn--gold btn--full">Entrar no Sistema</button>
    </form>

    <p class="auth-footer">
      Não tem conta? <a href="cadastro_usuario.php">Criar cadastro</a>
    </p>
    <p class="auth-footer" style="margin-top:.4rem;">
      <a href="index.php" style="color:var(--gray-400);">← Voltar ao início</a>
    </p>
  </div>
</div>
</body>
</html>
