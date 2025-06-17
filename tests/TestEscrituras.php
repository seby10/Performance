<?php
require_once '../vendor/autoload.php';
require_once '../src/models/conexion.php';

class TestEscrituras
{
    private $faker;
    private $db;
    private $startTime;
    private $results = [];

    public function __construct()
    {
        $this->faker = Faker\Factory::create('es_ES');
        $this->db = Conexion::getConnection();
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

    public function generateTestData()
    {
        $this->db->exec("SET foreign_key_checks = 0");
        $this->db->exec("TRUNCATE TABLE DETALLE_VENTAS");
        $this->db->exec("TRUNCATE TABLE MAESTRO_VENTAS");
        $this->db->exec("TRUNCATE TABLE PRODUCTOS");
        $this->db->exec("TRUNCATE TABLE CLIENTES");
        $this->db->exec("SET foreign_key_checks = 1");

        $this->startTimer();
        // CLIENTE DE PRUEBA
        $sql = "INSERT INTO CLIENTES VALUES (?, ?, ?, ?, ?)";
        $this->db->prepare($sql)->execute([
            '1234567890',
            'Cliente',
            'De Prueba',
            'Dirección de prueba',
            '0999999999'
        ]);
        $this->endTimer('Generar cliente de prueba');

        // PRODUCTOS
        $this->startTimer();
        $productos = [];
        for ($i = 1; $i <= 15; $i++) {
            $codigo = 'PROD' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $sql = "INSERT INTO PRODUCTOS VALUES (?, ?, ?, ?, ?)";
            $this->db->prepare($sql)->execute([
                $codigo,
                "Producto $i",
                "Fabricante $i",
                $this->faker->numberBetween(10, 1000),
                $this->faker->numberBetween(0, 100)
            ]);
            $productos[] = $codigo;
        }
        $this->endTimer('Generar 15 productos');

        return $productos;
    }

    public function testOrderInsertionPerformance()
    {
        $productos = $this->generateTestData();
        $cedulaCliente = '1234567890';

        // Insertar 15 órdenes con 1 a 15 detalles cada una
        for ($detalles = 1; $detalles <= 15; $detalles++) {
            $this->startTimer();

            $this->db->beginTransaction();
            try {
                // MAESTRO
                $sql = "INSERT INTO MAESTRO_VENTAS (FEC_FAC, CED_CLI_VEN, TOTAL, ESTADO) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    date('Y-m-d'),
                    $cedulaCliente,
                    0,
                    'A'
                ]);
                $facturaId = $this->db->lastInsertId();

                // DETALLLES
                for ($j = 0; $j < $detalles; $j++) {
                    $producto = $productos[$j];
                    $cantidad = $this->faker->numberBetween(1, 10);

                    $sql = "INSERT INTO DETALLE_VENTAS (COD_PRO_VEN, CANTIDAD, NUM_FAC_PER) VALUES (?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$producto, $cantidad, $facturaId]);
                }

                $this->db->commit();
                $this->endTimer("Insertar orden con $detalles detalles");
            } catch (Exception $e) {
                $this->db->rollBack();
                echo "Error en orden con $detalles detalles: " . $e->getMessage() . "\n";
            }
        }
    }

    public function printResults()
    {
        echo "\nResultados de las pruebas de performance (escritura):\n";
        echo str_repeat("-", 70) . "\n";
        echo sprintf("%-30s | %-20s | %-15s\n", "Operación", "Tiempo (ms)", "Memoria (MB)");
        echo str_repeat("-", 70) . "\n";

        foreach ($this->results as $result) {
            echo sprintf(
                "%-30s | %-20s | %-15s\n",
                $result['operation'],
                $result['execution_time_ms'],
                $result['memory_usage_mb']
            );
        }
    }
}
