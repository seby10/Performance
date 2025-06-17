<?php
require_once 'TestLecturas.php';
require_once 'EscrituraMasivaScript.php';

$test = new TestLecturas();

// Probar performance del reporte con diferentes tamaños
echo "\nProbando performance de reportes...\n";
$test->testReportPerformance();

// Mostrar resultados
$test->printResults();