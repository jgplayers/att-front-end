<?php
session_start();

// Se não estiver logado como admin, redireciona para login
if (!isset($_SESSION['admin'])) {
    header("Location: login_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel de Controle</title>
  <link rel="stylesheet" href="css/painel.css">
</head>
<body>
  <div class="painel">
    <div class="painel-header">
      <h1>⚙️ Painel de Controle</h1>
      <!-- 🔘 Botão de Logout -->
      <a href="logout_admin.php" class="btn-logout">Sair</a>
    </div>

    <div class="links-grid">
      <a href="arquivos.php" class="link-card">📂 Arquivos</a>
      <a href="cadastro.php" class="link-card">📝 Cadastro</a>
      <a href="cadastro_produto.php" class="link-card">📦 Cadastro Produto</a>
      <a href="gerenciar_produtos.php" class="link-card">🛠️ Gerenciar Produtos</a>
      <a href="index.php" class="link-card">🏠 Home</a>
      <a href="js" class="link-card">📜 Scripts JS</a>
      <a href="login.php" class="link-card">🔐 Login</a>
    </div>
  </div>
</body>
</html>
