<?php
// Conexão com o banco
try {
  $pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na conexão: " . $e->getMessage());
}

// Mensagem de retorno
$mensagem = "";
if (isset($_GET['msg'])) {
  switch ($_GET['msg']) {
    case 'produto_del': $mensagem = "🗑️ Produto excluído com sucesso!"; break;
    case 'produto_edit': $mensagem = "✏️ Produto atualizado com sucesso!"; break;
    case 'produto_erro': $mensagem = "⚠️ Erro ao atualizar o produto."; break;
  }
}

// Excluir produto
if (isset($_POST['excluirProduto'])) {
  $id = $_POST['excluirProduto'];
  $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
  $stmt->execute([$id]);
  header("Location: gerenciar_produtos.php?msg=produto_del");
  exit;
}

// Atualizar produto
if (isset($_POST['atualizarProduto'])) {
  $id = $_POST['produtoId'];
  $nome = $_POST['produtoNome'];
  $descricao = $_POST['produtoDescricao'];
  $avaliacao = $_POST['produtoAvaliacao'];
  $preco = $_POST['produtoPreco'];
  $categoria_id = $_POST['produtoCategoria'];

  if ($id && $nome && $descricao && $avaliacao && $preco && $categoria_id) {
    $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, avaliacao = ?, preco = ?, categoria_id = ? WHERE id = ?");
    $stmt->execute([$nome, $descricao, $avaliacao, $preco, $categoria_id, $id]);
    header("Location: gerenciar_produtos.php?msg=produto_edit");
    exit;
  } else {
    header("Location: gerenciar_produtos.php?msg=produto_erro");
    exit;
  }
}

// Filtros
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();

// Buscar produtos
$sql = "SELECT p.*, c.nome AS categoria FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
$params = [];

if ($search) {
  $sql .= " AND p.nome LIKE ?";
  $params[] = "%$search%";
}

if ($categoria) {
  $sql .= " AND p.categoria_id = ?";
  $params[] = $categoria;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Gerenciar Produtos</title>
  <link rel="icon" href="uploads/LogoFAV.png" type="image/png" />
  <link rel="stylesheet" href="css/gerenciar.css" />
</head>
<body>
  <header>
    <h1>Gerenciar Produtos</h1>
    <?php if ($mensagem): ?>
      <p style="text-align:center; font-weight:bold;"><?= $mensagem ?></p>
    <?php endif; ?>
  </header>

  <!-- Filtros -->
  <form method="GET" class="filtro-form">
    <input type="text" name="search" placeholder="Buscar por nome..." value="<?= htmlspecialchars($search) ?>" />
    <select name="categoria">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $categoria == $cat['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['nome']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filtrar</button>
  </form>

  <!-- Cards de produtos -->
  <main class="gerenciar-linha">
    <?php if (count($produtos) > 0): ?>
      <?php foreach ($produtos as $p): ?>
        <div class="card-gerenciar">
          <img src="<?= htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" />
          <h3><?= htmlspecialchars($p['nome']) ?></h3>
          <p><strong>Descrição:</strong> <?= htmlspecialchars($p['descricao']) ?></p>
          <p><strong>Avaliação:</strong> <?= str_repeat("★", $p['avaliacao']) . str_repeat("☆", 5 - $p['avaliacao']) ?></p>
          <p><strong>Preço:</strong> R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
          <p><strong>Categoria:</strong> <?= htmlspecialchars($p['categoria']) ?></p>

          <form method="POST" class="form-editar">
            <input type="hidden" name="produtoId" value="<?= $p['id'] ?>" />
            <input type="text" name="produtoNome" value="<?= htmlspecialchars($p['nome']) ?>" required />
            <input type="text" name="produtoDescricao" value="<?= htmlspecialchars($p['descricao']) ?>" required />
            <select name="produtoAvaliacao" required>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= $p['avaliacao'] == $i ? 'selected' : '' ?>>
                  <?= str_repeat("★", $i) . str_repeat("☆", 5 - $i) ?>
                </option>
              <?php endfor; ?>
            </select>
            <input type="number" name="produtoPreco" value="<?= $p['preco'] ?>" step="0.01" required />
            <select name="produtoCategoria" required>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $p['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" name="atualizarProduto" class="btn-editar">Atualizar</button>
          </form>

          <form method="POST" onsubmit="return confirm('Excluir este produto?')">
            <input type="hidden" name="excluirProduto" value="<?= $p['id'] ?>" />
            <button type="submit" class="btn-remover">Excluir</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center;">Nenhum produto encontrado.</p>
    <?php endif; ?>
  </main>
</body>
</html>
