<?php
require_once 'TestEscrituras.php';

$test = new TestEscrituras();

// Probar inserción de órdenes con diferentes cantidades de detalles
echo "Probando inserción de órdenes con 1 a 15 detalles...\n";
$test->testOrderInsertionPerformance();

// Mostrar resultados
$test->printResults();