<?php
require_once '../vendor/autoload.php';
require_once '../src/models/conexion.php';

class TestLecturas
{
    private $faker;
    private $db;
    private $startTime;
    private $results = [];
    private $batchSize = 1000; // Para inserciones por lotes

    public function __construct()
    {
        $this->faker = Faker\Factory::create('es_ES');
        $this->db = Conexion::getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function startTimer()
    {
        $this->startTime = microtime(true);
    }

    private function endTimer($operation)
    {
        $endTime = microtime(true);
        $executionTime = ($endTime - $this->startTime) * 1000;
        $memoryUsage = memory_get_usage(true) / 1024 / 1024;

        $this->results[] = [
            'operation' => $operation,
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 2)
        ];
    }

    public function testReportPerformance()
    {
        $orderCounts = [100, 1000, 4000, 10000, 20000, 60000, 100000, 1000000];

        foreach ($orderCounts as $count) {
            // Consulta 1: Reporte maestro-detalle básico (optimizado para memoria)
            $this->startTimer();
            $sql = "SELECT m.NUM_FAC, m.FEC_FAC, c.NOM_CLI, 
                       d.COD_PRO_VEN, p.NOM_PRO, d.CANTIDAD
                FROM MAESTRO_VENTAS m
                JOIN CLIENTES c ON m.CED_CLI_VEN = c.CED_CLI
                JOIN DETALLE_VENTAS d ON m.NUM_FAC = d.NUM_FAC_PER
                JOIN PRODUCTOS p ON d.COD_PRO_VEN = p.COD_PRO
                LIMIT $count";

            // Usar fetch() en lugar de fetchAll() para procesar filas una por una
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Procesar cada fila individualmente sin almacenar todo en memoria
                // Puedes comentar esto si solo quieres medir el tiempo de ejecución
                // $dummy = $row['NUM_FAC']; // Solo para asegurar que se procesa
            }
            $this->endTimer("Reporte básico ($count detalles)");
        }
    }

    public function printResults()
    {
        echo "\nResultados de las pruebas de performance:\n";
        echo str_repeat("-", 100) . "\n";
        echo sprintf("%-40s | %-20s | %-15s\n", "Operación", "Tiempo (ms)", "Memoria (MB)");
        echo str_repeat("-", 100) . "\n";

        foreach ($this->results as $result) {
            echo sprintf(
                "%-40s | %-20s | %-15s\n",
                $result['operation'],
                $result['execution_time_ms'],
                $result['memory_usage_mb']
            );
        }
    }
}
