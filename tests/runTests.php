<?php
require_once 'PerformanceTest.php';

$test = new PerformanceTest();

// Generar datos base
echo "Generando datos de prueba...\n";
$test->generateTestData();

// Generar órdenes para pruebas
echo "Generando órdenes...\n";
$test->generateOrders(100);
$test->generateOrders(1000);
$test->generateOrders(4000);

// Probar performance del reporte
echo "Probando performance del reporte...\n";
$test->testReportPerformance();

// Mostrar resultados
$test->printResults();