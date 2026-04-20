<?php
require_once __DIR__ . '/../includes/auth.php';
$user = currentUser();
$pageName = $pageName ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageName) ? h($pageName) . ' — ' : '' ?>FinControl</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $rootPath ?? '' ?>assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="<?= $rootPath ?? '' ?>dashboard.php" class="navbar__brand">
    <span class="navbar__logo">₿</span>
    <span>FinControl</span>
  </a>
  <ul class="navbar__links">
    <li><a href="<?= $rootPath ?? '' ?>dashboard.php"    <?= $pageName==='Dashboard'   ?'class="active"':'' ?>>Dashboard</a></li>
    <li><a href="<?= $rootPath ?? '' ?>contas_pagar.php" <?= $pageName==='Contas'      ?'class="active"':'' ?>>Contas a Pagar</a></li>
    <li><a href="<?= $rootPath ?? '' ?>fornecedores.php" <?= $pageName==='Fornecedores'?'class="active"':'' ?>>Fornecedores</a></li>
    <li><a href="<?= $rootPath ?? '' ?>relatorios.php"   <?= $pageName==='Relatórios'  ?'class="active"':'' ?>>Relatórios</a></li>
  </ul>
  <div class="navbar__user">
    <span class="navbar__avatar"><?= mb_substr($user['nome'], 0, 1) ?></span>
    <span class="navbar__name"><?= h($user['nome']) ?></span>
    <a href="<?= $rootPath ?? '' ?>logout.php" class="navbar__logout" title="Sair">⏻</a>
  </div>
</nav>
<main class="main">
<?php if (isset($pageHeader)): ?>
  <div class="page-header">
    <h1 class="page-title"><?= h($pageHeader) ?></h1>
    <?php if (isset($pageSubtitle)): ?>
      <p class="page-subtitle"><?= h($pageSubtitle) ?></p>
    <?php endif; ?>
    <?php if (isset($pageAction)): ?>
      <div class="page-actions"><?= $pageAction ?></div>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php renderFlash(); ?>
