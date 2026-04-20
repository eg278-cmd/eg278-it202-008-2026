<?php
require(__DIR__ . '/../lib/db.php');

$db = getDB();

$query = "DESCRIBE `IT202-M25-Golf`";
$stmt = $db->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($rows);
echo "</pre>";