<?php
class VentasRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createMaestro($data)
    {
        $stmt = $this->db->prepare("CALL ABRIR_FACTURA(?)");
        $stmt->execute([$data['cedula']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['NUM_FAC'];
    }

    public function createDetalle($data)
    {
        $stmt = $this->db->prepare("CALL INSERTAR_DETALLE(?, ?, ?)");
        $stmt->execute([
            $data['codigo_producto'],
            $data['cantidad'],
            $data['numero_factura']
        ]);
    }

    public function cerrarFactura($numeroFactura)
    {
        $stmt = $this->db->prepare("CALL CERRAR_FACTURA(?)");
        $stmt->execute([$numeroFactura]);
    }

    public function getVentaCompleta($numeroFactura)
    {
        $sql = "SELECT mv.NUM_FAC, mv.FEC_FAC, mv.TOTAL, mv.ESTADO,
                       c.NOM_CLI, c.APE_CLI,
                       dv.CANTIDAD, p.NOM_PRO, p.PRE_UNI_PRO
                FROM MAESTRO_VENTAS mv
                JOIN CLIENTES c ON mv.CED_CLI_VEN = c.CED_CLI
                JOIN DETALLE_VENTAS dv ON mv.NUM_FAC = dv.NUM_FAC_PER
                JOIN PRODUCTOS p ON dv.COD_PRO_VEN = p.COD_PRO
                WHERE mv.NUM_FAC = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroFactura]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportVentas()
    {
        $sql = "SELECT mv.NUM_FAC, mv.FEC_FAC, mv.TOTAL, mv.ESTADO,
                       c.NOM_CLI, c.APE_CLI,
                       dv.CANTIDAD, p.NOM_PRO, p.PRE_UNI_PRO
                FROM MAESTRO_VENTAS mv
                JOIN CLIENTES c ON mv.CED_CLI_VEN = c.CED_CLI
                JOIN DETALLE_VENTAS dv ON mv.NUM_FAC = dv.NUM_FAC_PER
                JOIN PRODUCTOS p ON dv.COD_PRO_VEN = p.COD_PRO";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ventas[] = $row;
        }
        return $ventas ?? [];
    }
}
