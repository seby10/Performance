<?php
require_once '../vendor/autoload.php';
require_once '../src/models/conexion.php';

class PerformanceTest {
    private $faker;
    private $db;
    private $startTime;
    private $results = [];

    public function __construct() {
        $this->faker = Faker\Factory::create('es_ES');
        $this->db = Conexion::getConnection();
    }

    private function startTimer() {
        $this->startTime = microtime(true);
    }

    private function endTimer($operation) {
        $endTime = microtime(true);
        $executionTime = ($endTime - $this->startTime) * 1000;
        $memoryUsage = memory_get_usage(true) / 1024 / 1024;
        
        $this->results[] = [
            'operation' => $operation,
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 2)
        ];
    }

    public function generateTestData() {
        $this->startTimer();
        for ($i = 0; $i < 100; $i++) {
            $sql = "INSERT INTO CLIENTES VALUES (?, ?, ?, ?, ?)";
            $this->db->prepare($sql)->execute([
                $this->faker->unique()->numerify('##########'),
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->faker->address(),
                $this->faker->phoneNumber()
            ]);
        }
        $this->endTimer('Generar 100 clientes');

        $this->startTimer();
        for ($i = 0; $i < 1000; $i++) {
            $sql = "INSERT INTO PRODUCTOS VALUES (?, ?, ?, ?, ?)";
            $this->db->prepare($sql)->execute([
                $this->faker->unique()->bothify('PROD#####'),
                $this->faker->words(3, true),
                $this->faker->company(),
                $this->faker->numberBetween(10, 1000),
                $this->faker->numberBetween(0, 100)
            ]);
        }
        $this->endTimer('Generar 1000 productos');
    }

    public function generateOrders($numberOfOrders) {
        $this->startTimer();
        $clientes = $this->db->query("SELECT CED_CLI FROM CLIENTES")->fetchAll(PDO::FETCH_COLUMN);
        $productos = $this->db->query("SELECT COD_PRO FROM PRODUCTOS")->fetchAll(PDO::FETCH_COLUMN);

        for ($i = 0; $i < $numberOfOrders; $i++) {
            $this->db->beginTransaction();
            try {
                // Insertar maestro
                $sql = "INSERT INTO MAESTRO_VENTAS (FEC_FAC, CED_CLI_VEN, TOTAL, ESTADO) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $this->faker->date(),
                    $clientes[array_rand($clientes)],
                    0,
                    'A'
                ]);
                $facturaId = $this->db->lastInsertId();

                // Insertar detalles
                $numDetalles = min(($i % 15) + 1, count($productos));
                
                for ($j = 0; $j < $numDetalles; $j++) {
                    $producto = $productos[array_rand($productos)];
                    $cantidad = $this->faker->numberBetween(1, 5);
                    
                    $sql = "INSERT INTO DETALLE_VENTAS VALUES (?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$producto, $cantidad, $facturaId]);
                }

                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                echo "Error en orden $i: " . $e->getMessage() . "\n";
            }
        }
        $this->endTimer("Generar $numberOfOrders órdenes");
    }

    public function testReportPerformance() {
        $orderCounts = [100, 1000, 4000, 10000, 20000, 60000, 100000];

        foreach ($orderCounts as $count) {
            $this->startTimer();
            $sql = "SELECT m.NUM_FAC, m.FEC_FAC, c.NOM_CLI, 
                           d.COD_PRO_VEN, p.NOM_PRO, d.CANTIDAD
                    FROM MAESTRO_VENTAS m
                    JOIN CLIENTES c ON m.CED_CLI_VEN = c.CED_CLI
                    JOIN DETALLE_VENTAS d ON m.NUM_FAC = d.NUM_FAC_PER
                    JOIN PRODUCTOS p ON d.COD_PRO_VEN = p.COD_PRO
                    LIMIT $count";
            
            $result = $this->db->query($sql);
            $this->endTimer("Reporte con $count órdenes");
        }
    }

    public function printResults() {
        echo "\nResultados de las pruebas de performance:\n";
        echo str_repeat("-", 70) . "\n";
        echo sprintf("%-30s | %-20s | %-15s\n", "Operación", "Tiempo (ms)", "Memoria (MB)");
        echo str_repeat("-", 70) . "\n";
        
        foreach ($this->results as $result) {
            echo sprintf("%-30s | %-20s | %-15s\n",
                $result['operation'],
                $result['execution_time_ms'],
                $result['memory_usage_mb']
            );
        }
    }
}