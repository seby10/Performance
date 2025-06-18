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

            // No need for explicit transaction as stored procedures handle it

            // Crear maestro usando stored procedure
            $numeroFactura = $this->unitOfWork->getVentasRepository()->createMaestro([
                'cedula' => $data['cedula']
            ]);

            // Crear detalle usando stored procedure
            foreach ($data['detalle'] as $item) {
                $this->unitOfWork->getVentasRepository()->createDetalle([
                    'codigo_producto' => $item['codigo_producto'],
                    'cantidad' => $item['cantidad'],
                    'numero_factura' => $numeroFactura
                ]);
            }

            // Cerrar la factura
            $this->unitOfWork->getVentasRepository()->cerrarFactura($numeroFactura);

            echo json_encode([
                'success' => true,
                'message' => 'Venta creada exitosamente',
                'numeroFactura' => $numeroFactura
            ]);
        } catch (Exception $e) {
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
