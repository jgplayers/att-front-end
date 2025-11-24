<?php
session_start();

// Conexão com o banco
try {
    $pdo = new PDO("mysql:host=localhost;dbname=Loja Virtual Teste", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = $_POST['senha'] ?? '';

    if ($usuario && $senha) {
        // Criptografar a senha
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir no banco
        $stmt = $pdo->prepare("INSERT INTO admin (usuario, senha) VALUES (?, ?)");
        try {
            $stmt->execute([$usuario, $hash]);
            $mensagem = "✅ Administrador cadastrado com sucesso!";
        } catch (PDOException $e) {
            $mensagem = "❌ Erro ao cadastrar: " . $e->getMessage();
        }
    } else {
        $mensagem = "⚠️ Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Admin</title>
  <link rel="stylesheet" href="css/cadastro_admin.css">
</head>
<body>
  <div class="cadastro-container">
    <h1>🛠️ Cadastro de Administrador</h1>

    <?php if (!empty($mensagem)): ?>
      <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="POST" class="cadastro-form">
      <label for="usuario">Usuário:</label>
      <input type="text" id="usuario" name="usuario" required>

      <label for="senha">Senha:</label>
      <input type="password" id="senha" name="senha" required>

      <button type="submit">Cadastrar</button>
    </form>
  </div>
</body>
</html>
