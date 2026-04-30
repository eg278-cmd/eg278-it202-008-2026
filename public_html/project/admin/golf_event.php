<?php
require(__DIR__ . "/../../../lib/functions.php");
require(__DIR__ . "/../../../partials/nav.php");



// 1. Retrieve the ID from the URL
$id = se($_GET, "id", -1, false);

// 2. Validate the ID
if ($id < 1) {
    flash("Invalid tournament ID", "danger");
    header("Location: " . get_url("admin/list_golf.php"));
    exit;
}

$db = getDB();

// 3. Load the record
$query = "SELECT id, tourn_id, name, start_date, end_date, is_api
          FROM `IT202-E25-Golf`
          WHERE id = :id";

$stmt = $db->prepare($query);
$stmt->execute([":id" => $id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Handle missing record
if (!$record) {
    flash("Tournament not found", "warning");
    header("Location: " . get_url("admin/list_golf.php"));
    exit;
}
?>

<?php require(__DIR__ . "/../../../partials/nav.php"); ?>

<div class="container">
    <h2>Golf Tournament Details</h2>

    <div class="details-card">
        <h3><?php se($record["name"]); ?></h3>

        <p><strong>Tournament ID:</strong> <?php se($record["tourn_id"]); ?></p>
        <p><strong>Start Date:</strong> <?php se($record["start_date"]); ?></p>
        <p><strong>End Date:</strong> <?php se($record["end_date"]); ?></p>
        <p><strong>API Row:</strong> <?php se($record["is_api"]); ?></p>

        <a class="btn" href="<?php echo get_url("admin/list_golf.php"); ?>">Back to List</a>
    </div>
</div>

<style>
.container {
    max-width: 700px;
    margin: 2rem auto;
}

.details-card {
    background: #fafafa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #ddd;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

.details-card h3 {
    margin-bottom: 1rem;
}

.btn {
    display: inline-block;
    padding: 10px 16px;
    background: #007bff;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.btn:hover {
    background: #0056b3;
}
</style>

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
