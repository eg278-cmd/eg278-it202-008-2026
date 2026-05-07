<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$db = getDB();

// If admin-style delete is used, use the provided user_id
// Otherwise default to the logged-in user
$user_id = $_GET["user_id"] ?? get_user_id();

$golf_id = $_GET["golf_id"] ?? null;

if (!$golf_id) {
    flash("Invalid tournament", "danger");
    redirect(get_url("my_golf_tournaments.php"));
    exit;
}

$stmt = $db->prepare("
DELETE FROM `IT202-E25-UserGolf`
WHERE user_id = :uid AND golf_id = :gid
");
$stmt->execute([
    ":uid" => $user_id,
    ":gid" => $golf_id
]);

flash("Association removed", "success");

// If admin-style delete was used, return to the all-users page
if (isset($_GET["user_id"])) {
    redirect("all_user_golf.php");
    exit;
}

// Otherwise return to the user's page
redirect("my_golf_tournaments.php");
exit;
