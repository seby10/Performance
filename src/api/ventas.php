<?php
header('Content-Type: application/json');
require_once '../controllers/VentasController.php';

$controller = new VentasController();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['numero'])) {
            $controller->getVenta($_GET['numero']);
        } else {
            $controller->getReportVentas($_GET['limite'] ?? 10000);
        }
        break;
    case 'POST':
        $controller->create();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Método no soportado']);
        break;
}
