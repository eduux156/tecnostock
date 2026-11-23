<?php
$host = 'localhost';
$dbname = 'tecnostock_db';
$username = 'root'; 
$password = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
  //  echo "¡Conexión exitosa a la base de datos!"; 
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>