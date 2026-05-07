<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$db = getDB();

$user_id = get_user_id();
$golf_id = $_GET["golf_id"] ?? null;

if (!$golf_id) {
    flash("Invalid tournament.");
     header("Location: list_golf.php");
     exit;
}

// Check if already assigned
$check = $db->prepare("
SELECT 1 FROM `IT202-E25-UserGolf`
WHERE user_id = :uid AND golf_id = :gid
");
$check->execute([
    ":uid" => $user_id,
    ":gid" => $golf_id
]);

if ($check->fetch()) {
    flash("You already assigned this tournament.");
    header("Location: my_golf_tournaments.php");
    exit;
}

// Insert new association
$stmt = $db->prepare("
INSERT INTO `IT202-E25-UserGolf` (user_id, golf_id)
VALUES (:uid, :gid)
");

try {
    $stmt->execute([
        ":uid" => $user_id,
        ":gid" => $golf_id
    ]);
    flash("Tournament assigned successfully!");
} catch (Exception $e) {
    flash("Error assigning tournament.");
}

    header("Location: my_golf_tournaments.php");
    exit;
