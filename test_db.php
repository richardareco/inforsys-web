<?php
try {
    $conn = new PDO('mysql:host=servidor.inforsyspdv.com;port=3306;dbname=db_DUARTE;charset=utf8mb4', 'rcd', '*2656.rcdAA.-');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexion OK\n";
    $row = $conn->query("SELECT COUNT(*) as total FROM invo1")->fetch(PDO::FETCH_ASSOC);
    echo "invo1 registros: " . $row['total'] . "\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
