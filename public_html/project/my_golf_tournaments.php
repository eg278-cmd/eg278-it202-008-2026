<?php
require_once(__DIR__ . "/../partials/nav.php");
is_logged_in(true);

$user_id = get_user_id();
$db = getDB();

$query = "SELECT golf.id, golf.name, golf.start_date, golf.end_date, golf.tourn_id
FROM `IT202-E25-UserGolf` usergolf
JOIN `IT202-E25-Golf` golf ON usergolf.golf_id = golf.id
WHERE usergolf.user_id = :uid
AND usergolf.is_active = 1";

$stmt = $db->prepare($query);
$stmt->execute([":uid" => $user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class='container mt-4'>
    <h2>My Associated Golf Tournaments</h2>
    <p class="text-muted">These tournaments are associated with your account.</p>

    <?php if (empty($results)): ?>
        <div class="alert alert-info">You have no associated tournaments yet.</div>
    <?php endif; ?>

    <?php foreach ($results as $r): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php se($r, "name"); ?></h5>
                <p class="card-text">
                    <strong>Start:</strong> <?php se($r, "start_date"); ?><br>
                    <strong>End:</strong> <?php se($r, "end_date"); ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
</div>