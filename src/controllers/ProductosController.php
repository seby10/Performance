<?php
require_once '../models/UnitOfWork.php';
require_once '../models/Conexion.php';

class ProductosController {
    private $unitOfWork;

    public function __construct() {
        $this->unitOfWork = new UnitOfWork(Conexion::getConnection());
    }

    public function getAll() {
        try {
            $productos = $this->unitOfWork->getProductosRepository()->getAll();
            echo json_encode(['success' => true, 'data' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->unitOfWork->getProductosRepository()->create($data);
            echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}