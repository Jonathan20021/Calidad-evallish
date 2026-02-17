<?php
require_once __DIR__ . '/src/Config/Database.php';
$db = App\Config\Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE evaluations");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
