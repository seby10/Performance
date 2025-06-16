<?php
require_once 'TestLecturas.php';
require_once 'EscrituraMasivaScript.php';

// $escrituraTest = new EscrituraMasivaScript();
$test = new TestLecturas();

// Generar datos masivos (1,000,000 de detalles)
// echo "Generando datos masivos para pruebas (1,000,000 detalles)...\n";
// $escrituraTest->generateMassiveTestData(1000000);

// Probar performance del reporte con diferentes tamaños
echo "\nProbando performance de reportes...\n";
$test->testReportPerformance();

// Mostrar resultados
$test->printResults();