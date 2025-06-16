<?php
require_once '../models/UnitOfWork.php';
require_once '../models/Conexion.php';

class VentasController
{
    private $unitOfWork;

    public function __construct()
    {
        $this->unitOfWork = new UnitOfWork(Conexion::getConnection());
    }

    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $this->unitOfWork->beginTransaction();

            // Crear maestro
            $numeroFactura = $this->unitOfWork->getVentasRepository()->createMaestro([
                'fecha' => date('Y-m-d'),
                'cedula' => $data['cedula'],
                'total' => $data['total']
            ]);

            // Crear detalle y actualizar stock
            foreach ($data['detalle'] as $item) {
                $this->unitOfWork->getVentasRepository()->createDetalle([
                    'codigo_producto' => $item['codigo_producto'],
                    'cantidad' => $item['cantidad'],
                    'numero_factura' => $numeroFactura
                ]);

                $this->unitOfWork->getProductosRepository()->updateStock(
                    $item['codigo_producto'],
                    $item['cantidad']
                );
            }

            $this->unitOfWork->commit();
            echo json_encode(['success' => true, 'message' => 'Venta creada exitosamente']);
        } catch (Exception $e) {
            $this->unitOfWork->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getVenta($numeroFactura)
    {
        try {
            $venta = $this->unitOfWork->getVentasRepository()->getVentaCompleta($numeroFactura);
            echo json_encode(['success' => true, 'data' => $venta]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
