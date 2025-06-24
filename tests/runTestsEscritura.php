<?php
require_once 'TestEscrituras.php';

$test = new TestEscrituras();

echo "Probando inserción de órdenes con 1 a 15 detalles...\n";
$test->testOrderInsertionPerformance();

$test->printResults();