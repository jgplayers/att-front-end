<?php
session_start();
require_once __DIR__ . '/conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
  echo "Produto não encontrado.";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
  echo "Produto não encontrado.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($produto['nome']) ?> | Click & Compre</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/detalhes.css" />
  <link rel="icon" href="uploads/LogoFAV.png" type="image/png" />
</head>
<body>
  <header class="topo">
  <div class="logo">
  <a href="index.php" class="logo-link">
    <h1>🛍️ Click & Compre</h1>
  </a>
</div>

    <div class="topo-botoes">
      <a href="index.php" class="btn-topo">🏠 Home</a>
      <a href="carrinho.php" class="btn-topo">🛒 Carrinho</a>
      <?php if (isset($_SESSION['usuario'])): ?>
        <span class="boas-vindas">👋 Olá, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
        <a href="logout.php" class="btn-topo">Sair</a>
      <?php else: ?>
        <a href="login.php" class="btn-topo">🔐 Entrar</a>
        <a href="cadastro.php" class="btn-topo">Cadastrar</a>
      <?php endif; ?>
    </div>
  </header>

  <main class="detalhes-container">
    <div class="detalhes-box">
      <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="detalhes-imagem" />
      <div class="detalhes-info">
        <h2><?= htmlspecialchars($produto['nome']) ?></h2>
        <p class="detalhes-descricao"><?= htmlspecialchars($produto['descricao']) ?></p>
        <p class="detalhes-avaliacao">
          <?= str_repeat("★", $produto['avaliacao']) . str_repeat("☆", 5 - $produto['avaliacao']) ?>
        </p>
        <p class="detalhes-preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>

        <?php if (isset($_SESSION['usuario'])): ?>
          <form method="GET" action="index.php" class="form-comprar">
            <input type="hidden" name="add_carrinho" value="<?= $produto['id'] ?>" />
            <div class="qtd-box">
              <button type="button" onclick="alterarQtd('qtd_<?= $produto['id'] ?>', -1)">−</button>
              <input type="number" id="qtd_<?= $produto['id'] ?>" name="quantidade" value="1" min="1" />
              <button type="button" onclick="alterarQtd('qtd_<?= $produto['id'] ?>', 1)">+</button>
            </div>
            <button type="submit" class="btn-comprar">Adicionar ao carrinho</button>
          </form>
        <?php else: ?>
          <button class="btn-comprar" onclick="alert('Faça login para comprar')">Comprar</button>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script>
    function alterarQtd(id, delta) {
      const input = document.getElementById(id);
      let valor = parseInt(input.value) || 1;
      valor += delta;
      if (valor < 1) valor = 1;
      input.value = valor;
    }
  </script>
</body>
</html>
