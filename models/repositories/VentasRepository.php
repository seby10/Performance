<?php
class VentasRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createMaestro($data) {
        $stmt = $this->db->prepare("INSERT INTO MAESTRO_VENTAS (FEC_FAC, CED_CLI_VEN, TOTAL, ESTADO) 
                                   VALUES (:fecha, :cedula, :total, :estado)");
        $stmt->execute([
            'fecha' => $data['fecha'],
            'cedula' => $data['cedula'],
            'total' => $data['total'],
            'estado' => 'A'
        ]);
        return $this->db->lastInsertId();
    }

    public function createDetalle($data) {
        $stmt = $this->db->prepare("INSERT INTO DETALLE_VENTAS (COD_PRO_VEN, CANTIDAD, NUM_FAC_PER) 
                                   VALUES (:codigo_producto, :cantidad, :numero_factura)");
        return $stmt->execute([
            'codigo_producto' => $data['codigo_producto'],
            'cantidad' => $data['cantidad'],
            'numero_factura' => $data['numero_factura']
        ]);
    }

    public function getVentaCompleta($numeroFactura) {
        $maestro = $this->db->prepare("SELECT * FROM MAESTRO_VENTAS WHERE NUM_FAC = :numero");
        $maestro->execute(['numero' => $numeroFactura]);
        $resultMaestro = $maestro->fetch(PDO::FETCH_ASSOC);

        $detalle = $this->db->prepare("SELECT d.*, p.NOM_PRO, p.PRE_UNI_PRO 
                                      FROM DETALLE_VENTAS d 
                                      JOIN PRODUCTOS p ON d.COD_PRO_VEN = p.COD_PRO 
                                      WHERE d.NUM_FAC_PER = :numero");
        $detalle->execute(['numero' => $numeroFactura]);
        $resultDetalle = $detalle->fetchAll(PDO::FETCH_ASSOC);

        return [
            'maestro' => $resultMaestro,
            'detalle' => $resultDetalle
        ];
    }
}