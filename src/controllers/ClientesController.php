<?php
require_once '../models/UnitOfWork.php';
require_once '../models/Conexion.php';

class ClientesController {
    private $unitOfWork;

    public function __construct() {
        $this->unitOfWork = new UnitOfWork(Conexion::getConnection());
    }

    public function getAll() {
        try {
            $clientes = $this->unitOfWork->getClientesRepository()->getAll();
            echo json_encode(['success' => true, 'data' => $clientes]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($this->unitOfWork->getClientesRepository()->exists($data['cedula'])) {
                throw new Exception("Cliente ya existe");
            }

            $result = $this->unitOfWork->getClientesRepository()->create($data);
            echo json_encode(['success' => true, 'message' => 'Cliente creado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}