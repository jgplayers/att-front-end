<?php
$host = 'localhost';
$usuario = 'root';
$senha = ''; // padrão do XAMPP é senha vazia
$banco = 'loja virtual teste';



try {
  $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
