<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=test_db;port=3306','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $rows = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in test_db:\n";
    foreach ($rows as $r) echo "- $r\n";
} catch (PDOException $e) {
    echo 'PDO error: ' . $e->getMessage() . PHP_EOL;
}
