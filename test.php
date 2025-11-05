<?php
require_once('php/db.php');
require_once('php/barcode.php');

$allBarcodes = $pdo->query("SELECT barcode FROM media")->fetchAll(PDO::FETCH_COLUMN);

print_r($allBarcodes);

$title = "Härry Pötter and the Göblin's Stöne";
$barcode = generateBarcode($title, $allBarcodes);
echo "Generated barcode: " . $barcode . "\n";
