<?php
require __DIR__ . '/../src/Config/Database.php';
require __DIR__ . '/../src/Models/User.php';

use App\Config\Database;

// Explicitly use Calidad-evallish DB name if needed, but Config should have it.
try {
    $db = Database::getInstance()->getConnection();

    // Reset admin password to 'admin123'
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);

    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$newHash]);

    echo "<h1>Database Connected & Admin Password Reset</h1>";
    echo "Password set to: <b>admin123</b><br>";
    echo "Database: " . 'Calidad-evallish';

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo $e->getMessage();
}
