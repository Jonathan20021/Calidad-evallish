<?php
require __DIR__ . '/../src/Config/Database.php';
require __DIR__ . '/../src/Models/User.php';

use App\Config\Database;
use App\Models\User;

echo "<h1>Debug Login</h1>";

try {
    $db = Database::getInstance()->getConnection();
    echo "DB Connection: OK<br>";
} catch (Exception $e) {
    echo "DB Connection: FAILED - " . $e->getMessage();
    exit;
}

$userModel = new User();
$user = $userModel->findByUsername('admin');

if ($user) {
    echo "User 'admin' found.<br>";
    echo "Hash: " . $user['password_hash'] . "<br>";

    $check = password_verify('admin123', $user['password_hash']);
    echo "Password 'admin123' verify: " . ($check ? '<span style="color:green">VALID</span>' : '<span style="color:red">INVALID</span>') . "<br>";

    if (!$check) {
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "New Hash for 'admin123': " . $newHash . "<br>";
    }
} else {
    echo "User 'admin' NOT found.<br>";
}
