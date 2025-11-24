<?php
session_start();

// Conexão com o banco
try {
    $pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Se já estiver logado, redireciona para o painel
if (isset($_SESSION['admin'])) {
    header("Location: painel.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha   = $_POST['senha'] ?? '';

    if ($usuario && $senha) {
        // Buscar admin no banco
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($senha, $admin['senha'])) {
            // Login válido → cria sessão e redireciona
            $_SESSION['admin'] = $admin['usuario'];
            header("Location: painel.php");
            exit;
        } else {
            $erro = "Usuário ou senha inválidos!";
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <div class="login-container">
    <h1>⚙️ Login Administrativo</h1>

    <?php if (!empty($erro)): ?>
      <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <label for="usuario">Usuário:</label>
      <input type="text" id="usuario" name="usuario" required>

      <label for="senha">Senha:</label>
      <input type="password" id="senha" name="senha" required>

      <button type="submit">Entrar</button>
    </form>

    <!-- 🔗 Botão para cadastro de admin -->
    <div class="cadastro-link">
      <p>Não tem conta de administrador?</p>
      <a href="cadastro_admin.php" class="btn-cadastro">Cadastrar Admin</a>
    </div>
  </div>
</body>
</html>
