<?php
require_once '../vendor/autoload.php';
require_once '../src/models/conexion.php';

class EscrituraMasivaScript {
    private $faker;
    private $db;
    private $startTime;
    private $results = [];
    private $batchSize = 1000; // Para inserciones por lotes

    public function __construct() {
        $this->faker = Faker\Factory::create('es_ES');
        $this->db = Conexion::getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

    public function generateMassiveTestData($totalOrders) {
        // 1. Limpiar tablas existentes
        $this->cleanTables();
        
        // 2. Generar clientes (100 clientes)
        $this->generateClients(100);
        
        // 3. Generar productos (1000 productos)
        $this->generateProducts(1000);
        
        // 4. Generar órdenes masivas
        $this->generateMassiveOrders($totalOrders);
    }

    private function cleanTables() {
        $this->startTimer();
        $this->db->exec("SET foreign_key_checks = 0");
        $this->db->exec("TRUNCATE TABLE DETALLE_VENTAS");
        $this->db->exec("TRUNCATE TABLE MAESTRO_VENTAS");
        $this->db->exec("TRUNCATE TABLE PRODUCTOS");
        $this->db->exec("TRUNCATE TABLE CLIENTES");
        $this->db->exec("SET foreign_key_checks = 1");
        $this->endTimer('Limpiar tablas existentes');
    }

    private function generateClients($count) {
        $this->startTimer();
        $sql = "INSERT INTO CLIENTES (CED_CLI, NOM_CLI, APE_CLI, DIR_CLI, TEL_CLI) VALUES ";
        $values = [];
        $params = [];
        
        for ($i = 0; $i < $count; $i++) {
            $values[] = "(?, ?, ?, ?, ?)";
            array_push($params, 
                $this->faker->unique()->numerify('##########'),
                $this->faker->firstName(),
                $this->faker->lastName(),
                $this->faker->address(),
                $this->faker->phoneNumber()
            );
        }
        
        $sql .= implode(',', $values);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $this->endTimer("Generar $count clientes");
    }

    private function generateProducts($count) {
        $this->startTimer();
        $sql = "INSERT INTO PRODUCTOS (COD_PRO, NOM_PRO, MAR_PRO, PRE_UNI_PRO, EXISTENCIA) VALUES ";
        $values = [];
        $params = [];
        
        for ($i = 0; $i < $count; $i++) {
            $values[] = "(?, ?, ?, ?, ?)";
            array_push($params,
                $this->faker->unique()->bothify('PROD#####'),
                $this->faker->words(3, true),
                $this->faker->company(),
                $this->faker->numberBetween(10, 1000),
                $this->faker->numberBetween(0, 100)
            );
        }
        
        $sql .= implode(',', $values);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $this->endTimer("Generar $count productos");
    }

    private function generateMassiveOrders($totalDetails) {
        // Asumimos un promedio de 10 detalles por orden
        $totalOrders = ceil($totalDetails / 10);
        
        $this->startTimer();
        
        // Obtener IDs de clientes y productos
        $clientes = $this->db->query("SELECT CED_CLI FROM CLIENTES")->fetchAll(PDO::FETCH_COLUMN);
        $productos = $this->db->query("SELECT COD_PRO FROM PRODUCTOS")->fetchAll(PDO::FETCH_COLUMN);
        
        // Insertar órdenes por lotes
        for ($batch = 0; $batch < ceil($totalOrders / $this->batchSize); $batch++) {
            $this->db->beginTransaction();
            
            try {
                // Insertar maestros
                $masterSql = "INSERT INTO MAESTRO_VENTAS (FEC_FAC, CED_CLI_VEN, TOTAL, ESTADO) VALUES ";
                $masterValues = [];
                $masterParams = [];
                $orderIds = [];
                
                $batchOrders = min($this->batchSize, $totalOrders - ($batch * $this->batchSize));
                
                for ($i = 0; $i < $batchOrders; $i++) {
                    $masterValues[] = "(?, ?, ?, ?)";
                    array_push($masterParams,
                        $this->faker->dateTimeBetween('-1 year')->format('Y-m-d'),
                        $clientes[array_rand($clientes)],
                        0,
                        'A'
                    );
                }
                
                $masterSql .= implode(',', $masterValues);
                $stmt = $this->db->prepare($masterSql);
                $stmt->execute($masterParams);
                
                // Obtener IDs generados (depende del driver de base de datos)
                $firstId = $this->db->lastInsertId();
                for ($i = 0; $i < $batchOrders; $i++) {
                    $orderIds[] = $firstId + $i;
                }
                
                // Insertar detalles
                $detailSql = "INSERT INTO DETALLE_VENTAS (COD_PRO_VEN, CANTIDAD, NUM_FAC_PER) VALUES ";
                $detailValues = [];
                $detailParams = [];
                $detailsCount = 0;
                
                foreach ($orderIds as $orderId) {
                    // Entre 1 y 20 detalles por orden (promedio 10)
                    $numDetails = rand(1, 20);
                    
                    for ($j = 0; $j < $numDetails; $j++) {
                        $detailValues[] = "(?, ?, ?)";
                        array_push($detailParams,
                            $productos[array_rand($productos)],
                            rand(1, 5),
                            $orderId
                        );
                        $detailsCount++;
                        
                        // Si alcanzamos el total requerido, salir
                        if (($batch * $this->batchSize * 10 + $detailsCount) >= $totalDetails) {
                            break 3; // Salir de todos los bucles
                        }
                    }
                }
                
                $detailSql .= implode(',', $detailValues);
                $stmt = $this->db->prepare($detailSql);
                $stmt->execute($detailParams);
                
                $this->db->commit();
                
                // Mostrar progreso
                $processed = min(($batch + 1) * $this->batchSize, $totalOrders);
                echo "Procesadas $processed de $totalOrders órdenes (" . round(($processed/$totalOrders)*100, 2) . "%)\n";
                
            } catch (Exception $e) {
                $this->db->rollBack();
                echo "Error en lote $batch: " . $e->getMessage() . "\n";
            }
        }
        
        $this->endTimer("Generar $totalOrders órdenes con ~$totalDetails detalles");
    }
}