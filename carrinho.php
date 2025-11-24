<?php
session_start();

// Conexão com o banco
try {
  $pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na conexão: " . $e->getMessage());
}

// Inicializar carrinho
if (!isset($_SESSION['carrinho'])) {
  $_SESSION['carrinho'] = [];
}

// Remover item do carrinho
if (isset($_GET['remover'])) {
  $id = $_GET['remover'];
  unset($_SESSION['carrinho'][$id]);
  header("Location: carrinho.php");
  exit;
}

// Atualizar quantidade (+ ou -)
if (isset($_POST['acao']) && isset($_POST['produtoId'])) {
  $id = $_POST['produtoId'];
  if ($_POST['acao'] === 'aumentar') {
    $_SESSION['carrinho'][$id]++;
  } elseif ($_POST['acao'] === 'diminuir') {
    $_SESSION['carrinho'][$id]--;
    if ($_SESSION['carrinho'][$id] < 1) {
      unset($_SESSION['carrinho'][$id]); // remove se chegar a zero
    }
  }
  header("Location: carrinho.php");
  exit;
}

// Buscar produtos do carrinho
$ids = array_keys($_SESSION['carrinho']);
$produtos = [];

if (count($ids) > 0) {
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($placeholders)");
  $stmt->execute($ids);
  $produtos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Seu Carrinho</title>
  <link rel="stylesheet" href="css/carrinho.css" />
</head>
<body>
  <header>
    <h1 style="text-align:center;">🛒 Seu Carrinho</h1>
    <p style="text-align:center; margin-bottom:30px;">
      <a href="index.php">← Continuar comprando</a>
    </p>
  </header>

  <main class="carrinho-container">
    <?php if (count($produtos) > 0): ?>
      <div class="carrinho-grid">
        <?php $totalGeral = 0; ?>
        <?php foreach ($produtos as $produto): 
          $id = $produto['id'];
          $qtd = $_SESSION['carrinho'][$id];
          $totalItem = $produto['preco'] * $qtd;
          $totalGeral += $totalItem;
        ?>
          <div class="item-carrinho">
            <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" />
            <h3><?= htmlspecialchars($produto['nome']) ?></h3>
            <p><?= htmlspecialchars($produto['descricao']) ?></p>
            <div class="avaliacao">
              <?= str_repeat("★", $produto['avaliacao']) . str_repeat("☆", 5 - $produto['avaliacao']) ?>
            </div>
            <p>Preço unitário: R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>

            <!-- Controle de quantidade -->
            <form method="POST" action="carrinho.php" class="form-qtd">
              <input type="hidden" name="produtoId" value="<?= $id ?>" />
              <div class="qtd-box">
                <button type="submit" name="acao" value="diminuir">−</button>
                <input type="text" value="<?= $qtd ?>" readonly />
                <button type="submit" name="acao" value="aumentar">+</button>
              </div>
            </form>

            <p>Subtotal: R$ <?= number_format($totalItem, 2, ',', '.') ?></p>
            <a href="?remover=<?= $id ?>" class="btn-remover">Remover</a>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="total-geral">
        <h3 style="text-align:center; margin-top:30px;">
          💰 Total geral: R$ <?= number_format($totalGeral, 2, ',', '.') ?>
        </h3>
        <a href="finalizar.php" class="btn-finalizar">Finalizar Compra</a>
      </div>
    <?php else: ?>
      <p style="text-align:center;">Seu carrinho está vazio.</p>
    <?php endif; ?>
  </main>
</body>
</html>
