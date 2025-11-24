<?php
// Conexão com o banco
try {
  $pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na conexão: " . $e->getMessage());
}

// Mensagem de retorno via GET
$mensagem = "";
if (isset($_GET['msg'])) {
  switch ($_GET['msg']) {
    case 'categoria_ok': $mensagem = "✅ Categoria cadastrada!"; break;
    case 'categoria_edit': $mensagem = "✏️ Categoria atualizada!"; break;
    case 'categoria_erro': $mensagem = "⚠️ Não é possível excluir: existem produtos vinculados."; break;
    case 'categoria_del': $mensagem = "🗑️ Categoria excluída!"; break;
    case 'produto_ok': $mensagem = "✅ Produto cadastrado com sucesso!"; break;
    case 'produto_erro': $mensagem = "❌ Erro ao salvar a imagem."; break;
  }
}

// Cadastrar categoria
if (isset($_POST['novaCategoria'])) {
  $nome = $_POST['novaCategoria'];
  if ($nome) {
    $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
    $stmt->execute([$nome]);
    header("Location: cadastro_produto.php?msg=categoria_ok");
    exit;
  }
}

// Atualizar categoria
if (isset($_POST['editarCategoria']) && isset($_POST['categoriaId'])) {
  $novoNome = $_POST['editarCategoria'];
  $id = $_POST['categoriaId'];
  if ($novoNome && $id) {
    $stmt = $pdo->prepare("UPDATE categorias SET nome = ? WHERE id = ?");
    $stmt->execute([$novoNome, $id]);
    header("Location: cadastro_produto.php?msg=categoria_edit");
    exit;
  }
}

// Excluir categoria com verificação
if (isset($_POST['excluirCategoria'])) {
  $id = $_POST['excluirCategoria'];
  $verifica = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
  $verifica->execute([$id]);
  $total = $verifica->fetchColumn();

  if ($total > 0) {
    header("Location: cadastro_produto.php?msg=categoria_erro");
    exit;
  } else {
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: cadastro_produto.php?msg=categoria_del");
    exit;
  }
}

// Cadastrar produto com imagem, descrição e avaliação
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["produtoNome"])) {
  $nome = $_POST["produtoNome"] ?? '';
  $descricao = $_POST["produtoDescricao"] ?? '';
  $avaliacao = $_POST["produtoAvaliacao"] ?? 0;
  $preco = $_POST["produtoPreco"] ?? '';
  $categoria_id = $_POST["produtoCategoria"] ?? '';
  $imagem = $_FILES["produtoImagem"] ?? null;

  if ($nome && $descricao && $preco && $categoria_id && $imagem && $imagem["error"] === UPLOAD_ERR_OK) {
    $destino = "uploads/";
    if (!is_dir($destino)) {
      mkdir($destino, 0755, true);
    }

    $extensao = pathinfo($imagem["name"], PATHINFO_EXTENSION);
    $nomeImagem = uniqid("img_") . "." . $extensao;
    $caminhoFinal = $destino . $nomeImagem;

    if (move_uploaded_file($imagem["tmp_name"], $caminhoFinal)) {
      $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, avaliacao, preco, imagem, categoria_id) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$nome, $descricao, $avaliacao, $preco, $caminhoFinal, $categoria_id]);
      header("Location: cadastro_produto.php?msg=produto_ok");
      exit;
    } else {
      header("Location: cadastro_produto.php?msg=produto_erro");
      exit;
    }
  }
}

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Cadastro de Produto e Categoria</title>
  <link rel="icon" href="uploads/LogoFAV.png" type="image/png" />
  <link rel="stylesheet" href="css/cadastro.css" />
</head>
<body>
  <h1>Cadastro de Produto e Categoria</h1>

  <?php if ($mensagem): ?>
    <p style="text-align:center; font-weight:bold;"><?= $mensagem ?></p>
  <?php endif; ?>

  <!-- Cadastro de Categoria -->
  <section class="form-section">
    <h2>Cadastrar Nova Categoria</h2>
    <form method="POST">
      <input type="text" name="novaCategoria" placeholder="Nome da nova categoria" required />
      <button type="submit">Cadastrar Categoria</button>
    </form>
  </section>

  <!-- Atualizar ou Excluir Categoria -->
  <section class="form-section">
    <h2>Gerenciar Categorias</h2>
    <?php foreach ($categorias as $cat): ?>
      <form method="POST" style="display:flex; gap:10px; margin-bottom:10px;">
        <input type="hidden" name="categoriaId" value="<?= $cat['id'] ?>" />
        <input type="text" name="editarCategoria" value="<?= htmlspecialchars($cat['nome']) ?>" />
        <button type="submit">Atualizar</button>
        <button type="submit" name="excluirCategoria" value="<?= $cat['id'] ?>" onclick="return confirm('Excluir esta categoria?')">Excluir</button>
      </form>
    <?php endforeach; ?>
  </section>

  <!-- Cadastro de Produto -->
  <section class="form-section">
    <h2>Cadastrar Produto</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="produtoNome" placeholder="Nome do produto" required />

      <textarea name="produtoDescricao" placeholder="Descrição do produto" rows="4" required></textarea>

      <label for="produtoAvaliacao">Avaliação (1 a 5 estrelas):</label>
      <select name="produtoAvaliacao" id="produtoAvaliacao" required>
        <option value="">Selecione</option>
        <option value="1">★☆☆☆☆</option>
        <option value="2">★★☆☆☆</option>
        <option value="3">★★★☆☆</option>
        <option value="4">★★★★☆</option>
        <option value="5">★★★★★</option>
      </select>

      <input type="number" name="produtoPreco" placeholder="Preço" step="0.01" required />

      <label for="produtoImagem">Imagem do produto:</label><br>
      <input type="file" name="produtoImagem" id="produtoImagem" accept="image/*" onchange="mostrarPrevia()" required /><br><br>
      <img id="preview" src="#" alt="Prévia da imagem" style="display:none; max-width:200px; border:1px solid #ccc; border-radius:6px; margin-bottom:15px;" />

      <select name="produtoCategoria" required>
        <option value="">Selecione uma categoria</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Cadastrar Produto</button>
    </form>
  </section>

  <script>
    function mostrarPrevia() {
      const input = document.getElementById('produtoImagem');
      const preview = document.getElementById('preview');

      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
      } else {
        preview.src = '#';
        preview.style.display = 'none';
      }
    }
  </script>
</body>
</html>
