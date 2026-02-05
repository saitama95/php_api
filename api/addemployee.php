<?php
// header("Content-Type: application/json");
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require "db.php";

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['name']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Name and email are required"
    ]);
    exit;
}

$name  = trim($data['name']);
$email = trim($data['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email) VALUES (:name, :email)"
    );
    $stmt->execute([
        ':name'  => $name,
        ':email' => $email
    ]);

    echo json_encode([
        "status" => true,
        "message" => "User added successfully",
        "data" => [
            "id" => $pdo->lastInsertId(),
            "name" => $name,
            "email" => $email
        ]
    ]);

} catch (PDOException $e) {

    // Duplicate email
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode([
            "status" => false,
            "message" => "Email already exists"
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Server error"
    ]);
}
