<?php
header('Content-Type: application/json');
require_once '../controllers/ProductosController.php';

$controller = new ProductosController();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        $controller->getAll();
        break;
    case 'POST':
        $controller->create();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Método no soportado']);
        break;
}