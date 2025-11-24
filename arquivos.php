<?php
$arquivos = scandir(__DIR__);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Arquivos do Projeto</title>
  <link rel="icon" href="uploads/LogoFAV.png" type="image/png" />
  <link rel="stylesheet" href="css/arquivos.css" />
</head>
<body>
  <h1>Arquivos do Projeto</h1>
  <ul>
    <?php foreach ($arquivos as $arquivo): ?>
      <?php if ($arquivo !== '.' && $arquivo !== '..'): ?>
        <li><a href="<?= htmlspecialchars($arquivo) ?>" target="_blank"><?= htmlspecialchars($arquivo) ?></a></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</body>
</html>
