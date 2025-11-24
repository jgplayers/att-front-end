<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = $_POST['nome'];
  $email = $_POST['email'];
  $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
  try {
    $stmt->execute([$nome, $email, $senha]);
    $_SESSION['usuario'] = $nome;
    header("Location: index.php");
    exit;
  } catch (PDOException $e) {
    $erro = "Erro ao cadastrar: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Cadastro</title>
  <link rel="icon" href="uploads/LogoFAV.png" type="image/png" />
  <link rel="stylesheet" href="css/login_cadastro.css" />
</head>
<body>
  <div class="auth-container">
    <h2>Cadastro de Usuário</h2>

    <?php if (isset($erro)): ?>
      <p class="error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="nome" placeholder="Seu nome" required />
      <input type="email" name="email" placeholder="Seu e-mail" required />
      <input type="password" name="senha" placeholder="Sua senha" required />
      <button type="submit">Cadastrar</button>
    </form>

    <a href="login.php">Já tem conta? Faça login</a>
  </div>
</body>
</html>
