<?php
header('Content-Type: application/json');
require_once '../controllers/ClientesController.php';

$controller = new ClientesController();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['cedula'])) {
            $controller->getByCedula();
        } else {
            $controller->getAll();
        }
        break;
    case 'POST':
        $controller->create();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Método no soportado']);
        break;
}
