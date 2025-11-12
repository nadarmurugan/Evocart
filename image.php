<?php
require_once 'config.php'; // Include DB connection

if (isset($_GET['id'])) {
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT image_data, image_type FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $row = $stmt->fetch();

        if ($row) {
            // Crucial: Tell the browser it's an image, not an HTML page
            header("Content-Type: " . $row['image_type']); 
            echo $row['image_data'];
            exit;
        }
    } catch (\PDOException $e) {
        // Handle error (log it, or serve a placeholder image)
    }
}
// Serve a broken image or placeholder if not found
http_response_code(404);
?>