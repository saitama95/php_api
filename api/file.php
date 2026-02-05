<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require "db.php";

if (!isset($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "No file received"
    ]);
    exit;
}

$file = $_FILES['photo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$uploadDir = "uploads/";

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode([
        "status" => false,
        "message" => "Only JPG and PNG allowed"
    ]);
    exit;
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid("img_") . "." . $extension;
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    echo json_encode([
        "status" => false,
        "message" => "File upload failed"
    ]);
    exit;
}

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO photos (path) VALUES (:path)");
$stmt->execute([
    ':path' => $filePath
]);

echo json_encode([
    "status" => true,
    "message" => "File uploaded successfully",
    "data" => [
        "id" => $pdo->lastInsertId(),
        "path" => $filePath
    ]
]);
