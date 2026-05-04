<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$user_id = get_user_id();

// If ?all=1 is present → remove ALL
$remove_all = isset($_GET["all"]);

// If ?golf_id=### is present → remove ONE
$golf_id = $_GET["golf_id"] ?? null;

$db = getDB();

if ($remove_all) {
// Remove ALL associations for this user
$stmt = $db->prepare("DELETE FROM `IT202-E25-UserGolf` WHERE user_id = :uid");
$stmt->execute([":uid" => $user_id]);

flash("All associations removed", "success");
}
elseif ($golf_id) {
// Remove ONE association
$stmt = $db->prepare("DELETE FROM `IT202-E25-UserGolf`
WHERE user_id = :uid AND golf_id = :gid");
$stmt->execute([
":uid" => $user_id,
":gid" => $golf_id
]);

flash("Association removed", "success");
}

redirect(get_url("project/my_golf_tournaments.php"));
