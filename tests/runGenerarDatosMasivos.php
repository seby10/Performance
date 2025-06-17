<?php
require_once 'TestLecturas.php';
require_once 'EscrituraMasivaScript.php';

$escrituraTest = new EscrituraMasivaScript();

// Generar datos masivos (1,000,000 de detalles)
echo "Generando datos masivos para pruebas (1,000,000 detalles)...\n";
$escrituraTest->generateMassiveTestData(1000000);