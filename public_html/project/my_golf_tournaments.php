<?php
require_once(__DIR__ . "/../../partials/nav.php");
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

<div class="container mt-4">
    <h2>My Associated Golf Tournaments</h2>
    <p>These tournaments are associated with your account.</p>

    <?php
    // Stats
    $total = count($results);

    $earliest = null;
    $latest = null;

    if ($total > 0) {
        $earliest = min(array_column($results, "start_date"));
        $latest = max(array_column($results, "end_date"));
    }
    ?>
    <div class="card p-3 mb-3">
        <h5>Stats</h5>
        <p><strong>Total Associated Tournaments:</strong> <?php echo $total; ?></p>

        <?php if ($total > 0) : ?>
            <p><strong>Earliest Start Date:</strong> <?php echo htmlspecialchars($earliest); ?></p>
             <p><strong>Latest Start Date:</strong> <?php echo htmlspecialchars($latest); ?></p>
        <?php endif;
        ?>
     </div>

     <a href="<?php echo get_url("project/unassigned.php?all=1"); ?>"
        class="btn btn-warning mb-3"
        onclick="return confirm('Remove all associations?');">
         Remove all
        </a>
        
    <?php if (empty($results)) : ?>
        <p>No results available.</p>
    <?php else : ?>
        <?php foreach ($results as $row) : ?>
            <div class="card mb-2 p-3">
                <h4><?php echo htmlspecialchars($row["name"]); ?></h4>
                <p>Start: <?php echo htmlspecialchars($row["start_date"]); ?></p>
                <p>End: <?php echo htmlspecialchars($row["end_date"]); ?></p>
                <a href="<?php echo get_url("admin/golf_event.php?id=" . $row['id']); ?>"
                class="btn btn-primary btn-sm">
                View Details</a>
                <a href="<?php echo get_url("project/unassigned.php?golf_id" . $row['id']); ?>"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Remove this association?');">
                Remove
                </a>
               
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>