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

// Adicionar ao carrinho
if (isset($_GET['add_carrinho']) && isset($_GET['quantidade'])) {
    $idProduto = intval($_GET['add_carrinho']);
    $quantidade = max(1, intval($_GET['quantidade']));
    $_SESSION['carrinho'][$idProduto] = $quantidade;
    header("Location: index.php");
    exit;
}

// Filtros
$search = $_GET['search'] ?? '';
$categoriaFiltro = $_GET['categoria'] ?? '';

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Click & Compre</title>
  <link rel="stylesheet" href="css/style.css" />
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

      <?php
      $totalItens = 0;
      if (!empty($_SESSION['carrinho'])) {
          foreach ($_SESSION['carrinho'] as $qtd) {
              $totalItens += (int)$qtd;
          }
      }
      ?>

      <a href="carrinho.php" class="btn-topo carrinho-btn">
        🛒 Carrinho
        <?php if ($totalItens > 0): ?>
          <span class="carrinho-contador"><?= $totalItens ?></span>
        <?php endif; ?>
      </a>

      <?php if (isset($_SESSION['usuario'])): ?>
        <span class="boas-vindas">👋 Olá, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
        <a href="logout.php" class="btn-topo">Sair</a>
      <?php else: ?>
        <a href="login.php" class="btn-topo">🔐 Entrar</a>
        <a href="cadastro.php" class="btn-topo">Cadastrar</a>
      <?php endif; ?>

      <!-- Botão Admin visível para todos -->
      <a href="login_admin.php" class="btn-topo btn-admin">⚙️ Admin</a>
    </div>
  </header>

  <div class="mensagem-boas-vindas">
    <h2>🎉 Bem-vindo à Click & Compre!</h2>
    <p>Explore nossas categorias e encontre os melhores produtos com os melhores preços. Boas compras!</p>
  </div>

  <section class="filtros">
    <form method="GET" class="filtro-form">
      <div class="filtro-linha">
        <div class="dropdown-categorias">
          <button type="button" class="btn-categorias" onclick="toggleCategorias()">📂 Categorias</button>
          <ul id="lista-categorias" class="lista-categorias">
            <li><a href="?categoria=">Todas as categorias</a></li>
            <?php foreach ($categorias as $cat): ?>
              <li><a href="?categoria=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <input type="text" name="search" placeholder="Buscar produtos..." value="<?= htmlspecialchars($search) ?>" />
        <button type="submit">Buscar</button>
      </div>
    </form>
  </section>

  <main>
    <?php foreach ($categorias as $cat): ?>
      <?php
        if ($categoriaFiltro && $categoriaFiltro != $cat['id']) continue;

        $sql = "SELECT * FROM produtos WHERE categoria_id = ?";
        $params = [$cat['id']];

        if ($search) {
          $sql .= " AND nome LIKE ?";
          $params[] = "%$search%";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $produtos = $stmt->fetchAll();
      ?>

      <?php if (count($produtos) > 0): ?>
        <section class="bloco-categoria">
          <h2 class="titulo-categoria"><?= htmlspecialchars($cat['nome']) ?></h2>
          <div class="produtos-grid">
            <?php foreach ($produtos as $produto): ?>
              <div class="card-produto">
                <a href="detalhes.php?id=<?= $produto['id'] ?>" class="card-produto-link">
                  <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" />
                  <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                </a>
                <p class="descricao"><?= htmlspecialchars($produto['descricao']) ?></p>
                <div class="avaliacao">
                  <?= str_repeat("★", $produto['avaliacao']) . str_repeat("☆", 5 - $produto['avaliacao']) ?>
                </div>
                <p class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>

                <?php if (isset($_SESSION['usuario'])): ?>
                  <form method="GET" action="index.php" class="form-comprar">
                    <input type="hidden" name="add_carrinho" value="<?= $produto['id'] ?>" />
                    <div class="qtd-box">
                      <button type="button" onclick="alterarQtd('qtd_<?= $produto['id'] ?>', -1)">−</button>
                      <input type="number" id="qtd_<?= $produto['id'] ?>" name="quantidade" value="1" min="1" />
                      <button type="button" onclick="alterarQtd('qtd_<?= $produto['id'] ?>', 1)">+</button>
                    </div>
                    <button type="submit" class="btn-comprar">Comprar</button>
                  </form>
                <?php else: ?>
                  <button class="btn-comprar" onclick="alertaLogin()">Comprar</button>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    <?php endforeach; ?>
  </main>

  <div id="modal-login" class="modal-overlay" onclick="fecharModal(event)">
    <div class="modal-box">
      <span class="modal-close" onclick="fecharModal()">×</span>
      <h3>⚠️ Você precisa estar logado</h3>
      <p>Para comprar produtos, faça login ou crie uma conta.</p>
      <div class="modal-actions">
        <a href="login.php" class="btn-modal">Fazer login</a>
        <a href="cadastro.php" class="btn-modal">Cadastrar</a>
      </div>
    </div>
  </div>

  <script>
    function toggleCategorias() {
      const lista = document.getElementById("lista-categorias");
      lista.style.display = lista.style.display === "block" ? "none" : "block";
    }

    document.addEventListener("click", function (e) {
      const btn = document.querySelector(".btn-categorias");
      const lista = document.getElementById("lista-categorias");
      if (!btn.contains(e.target) && !lista.contains(e.target)) {
        lista.style.display = "none";
      }
    });

    function alertaLogin() {
      document.getElementById("modal-login").style.display = "flex";
    }

    function fecharModal(event) {
      const modal = document.getElementById("modal-login");
      if (!event || event.target === modal || event.target.classList.contains("modal-close")) {
        modal.style.display = "none";
      }
    }

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
