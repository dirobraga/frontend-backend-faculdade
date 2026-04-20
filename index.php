<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>FinControl — Sistema de Contas a Pagar</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="landing">

  <!-- Nav -->
  <nav class="landing__nav">
    <div class="landing__brand">
      <span>₿</span> FinControl
    </div>
    <a href="login.php" class="landing__btn-secondary" style="padding:.5rem 1.25rem;border-radius:8px;">
      Entrar
    </a>
  </nav>

  <!-- Hero -->
  <section class="landing__hero">
    <div class="landing__eyebrow">
      <span>✦</span> Controle Financeiro Empresarial <span>✦</span>
    </div>
    <h1 class="landing__title">
      Gerencie <br><em>Contas a Pagar</em><br> da sua empresa<br>com precisão e agilidade
    </h1>
    <p class="landing__subtitle">
      Registre fornecedores, controle vencimentos, aprove despesas e acompanhe
      pagamentos em um único sistema integrado.
    </p>
    <div class="landing__cta">
      <a href="login.php" class="landing__btn-primary">Acessar o Sistema</a>
      <a href="cadastro_usuario.php" class="landing__btn-secondary">Criar Conta</a>
    </div>
  </section>

  <!--  -->
  <div class="landing__features" style="grid-template-columns: repeat(2, 1fr); max-width: 600px;">
    <div class="landing__feat">
      <div class="landing__feat-icon">🏢</div>
      <h3>Fornecedores</h3>
      <p>Cadastre e gerencie todos os seus fornecedores e parceiros.</p>
    </div>
    <div class="landing__feat">
      <div class="landing__feat-icon">📋</div>
      <h3>Contas a Pagar</h3>
      <p>Acompanhe vencimentos, status e histórico de pagamentos.</p>
    </div>
    <div class="landing__feat">
      <div class="landing__feat-icon">✅</div>
      <h3>Aprovações</h3>
      <p>Fluxo de aprovação de despesas com registro completo.</p>
    </div>
    <div class="landing__feat">
      <div class="landing__feat-icon">📊</div>
      <h3>Relatórios</h3>
      <p>Visualize totais, inadimplências e resumos financeiros.</p>
    </div>
  </div>

  <footer class="landing__footer">
    <p>© <?= date('Y') ?> FinControl — Sistema de Controle Financeiro</p>
  </footer>
</div>
</body>
</html>
