<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $path = trim($_GET['path'] ?? '');

    if ($path === '') {
        echo json_encode(['success' => false, 'error' => 'No file path provided']);
        exit;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext !== 'md') {
        echo json_encode(['success' => false, 'error' => 'File must have a .md extension']);
        exit;
    }

    if (!is_file($path)) {
        echo json_encode(['success' => false, 'error' => 'File not found: ' . $path]);
        exit;
    }

    if (!is_readable($path)) {
        echo json_encode(['success' => false, 'error' => 'File is not readable']);
        exit;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to read file']);
        exit;
    }

    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
    }

    echo json_encode([
        'success' => true,
        'content' => $content,
        'filename' => basename($path)
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
