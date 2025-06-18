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

    public function getReportVentas($limit)
    {
        if ($limit > 100000) {
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 300);
        }

        $ventas = [];
        $sql = "SELECT SQL_NO_CACHE mv.NUM_FAC, mv.FEC_FAC, mv.TOTAL, mv.ESTADO,
                   c.NOM_CLI, c.APE_CLI,
                   dv.CANTIDAD, p.NOM_PRO, p.PRE_UNI_PRO
            FROM MAESTRO_VENTAS mv
            STRAIGHT_JOIN CLIENTES c ON mv.CED_CLI_VEN = c.CED_CLI
            STRAIGHT_JOIN DETALLE_VENTAS dv ON mv.NUM_FAC = dv.NUM_FAC_PER
            STRAIGHT_JOIN PRODUCTOS p ON dv.COD_PRO_VEN = p.COD_PRO
            LIMIT ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ventas[] = $row;

                if (count($ventas) % 10000 === 0) {
                    gc_collect_cycles();
                }
            }

            return $ventas;
        } catch (PDOException $e) {
            error_log("Error en getReportVentas: " . $e->getMessage());
            return [];
        } finally {
            $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
    }
}
