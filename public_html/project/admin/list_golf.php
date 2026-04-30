<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();


// Filters
$filters = [];
$params = [];

// Filter by name
if (!empty($_GET["name"])) {
    $filters[] = "name LIKE :name";
    $params[":name"] = "%" . $_GET["name"] . "%";
}

// Filter by start date
if (!empty($_GET["start_date"])) {
    $filters[] = "start_date >= :start_date";
    $params[":start_date"] = $_GET["start_date"];
}

// Filter by end date
if (!empty($_GET["end_date"])) {
    $filters[] = "end_date <= :end_date";
    $params[":end_date"] = $_GET["end_date"];
}

// Sorting
$allowed_sorts = ["name", "start_date", "end_date", "created"];
$sort = $_GET["sort"] ?? "created";

if (!in_array($sort, $allowed_sorts)) {
    $sort = "created";
}

$order = $_GET["order"] ?? "DESC";
$order = strtoupper($order) === "ASC" ? "ASC" : "DESC";



$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 10;

if ($limit < 1 || $limit > 100) {
    $limit = 10;
}



$query = "SELECT id, tourn_id, name, start_date, end_date, is_api
FROM `IT202-E25-Golf`";

if (count($filters) > 0) {
    $query .= " WHERE " . implode(" AND ", $filters);
}

$query .= " ORDER BY $sort $order";
$query .= " LIMIT :limit";
$params[":limit"] = $limit;



$stmt = $db->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$results = [];
try {
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching tournaments " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
?>

<div class="container-fluid">
    <h3>List Golf Tournaments</h3>

    <?php if (count($results) == 0) : ?>
        <p>No results to show</p>
    <?php else : ?>

        <div class="golf-grid">
            <?php foreach ($results as $record) : ?>
                <div class="golf-card">
                    <h4><?php se($record["name"]); ?></h4>
                    <p><strong>ID:</strong> <?php se($record["tourn_id"]); ?></p>
                    <p><strong>Start:</strong> <?php se($record["start_date"]); ?></p>
                    <p><strong>End:</strong> <?php se($record["end_date"]); ?></p>
                    <p><strong>API Row:</strong> <?php se($record["is_api"]); ?></p>

                    <a href="<?php echo get_url("admin/edit_golf.php"); ?>?id=<?php se($record, "id"); ?>">
                        Edit
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<style>
    .golf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 1.25rem;
        margin-top: 1rem;
    }

    .golf-card {
        background: #f8f8f8;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
</style>

<?php require_once(__DIR__ . "/../../../partials/flash.php");

?>