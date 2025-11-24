<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $senha = $_POST['senha'];

  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
  $stmt->execute([$email]);
  $usuario = $stmt->fetch();

  if ($usuario && password_verify($senha, $usuario['senha'])) {
    $_SESSION['usuario'] = $usuario['nome'];
    header("Location: index.php");
    exit;
  } else {
    $erro = "E-mail ou senha inválidos.";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="icon" href="uploads/LogoFAV.png" type="image/png" />
  <link rel="stylesheet" href="css/login_cadastro.css" />
</head>
<body>
  <div class="auth-container">
    <h2>Login</h2>
    <?php if (isset($erro)): ?>
      <p class="error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Seu e-mail" required />
      <input type="password" name="senha" placeholder="Sua senha" required />
      <button type="submit">Entrar</button>
    </form>

    <a href="cadastro.php">Não tem conta? Cadastre-se</a>
  </div>
</body>
</html>
