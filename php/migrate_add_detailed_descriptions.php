<?php
/**
 * Migration: add detailed_description_en and detailed_description_ar to products table
 * Usage (CLI): php migrate_add_detailed_descriptions.php
 * Usage (browser): open http://localhost/rawee/php/migrate_add_detailed_descriptions.php
 */

require_once __DIR__ . '/db_connect.php';

$table = 'products';
$columnsToAdd = [
    'detailed_description_en' => 'TEXT NULL',
    'detailed_description_ar' => 'TEXT NULL',
];

$existingColumns = [];
$res = $mysqli->query("SHOW COLUMNS FROM `$table`");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }
    $res->free();
} else {
    http_response_code(500);
    echo "Error: could not read columns for table `$table`: " . $mysqli->error;
    exit(1);
}

$queries = [];
foreach ($columnsToAdd as $col => $definition) {
    if (!in_array($col, $existingColumns)) {
        $queries[] = "ALTER TABLE `$table` ADD COLUMN `$col` $definition";
    }
}

if (empty($queries)) {
    echo "No changes needed. Columns already exist.\n";
    exit(0);
}

$allOk = true;
foreach ($queries as $q) {
    if (!$mysqli->query($q)) {
        echo "Failed to run query: $q\nError: " . $mysqli->error . "\n";
        $allOk = false;
    } else {
        echo "Executed: $q\n";
    }
}

if ($allOk) {
    echo "Migration completed successfully.\n";
    exit(0);
} else {
    http_response_code(500);
    exit(1);
}

?>
