<?php
require(__DIR__ . "/../../../lib/functions.php");
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    header("Location: " . get_url("landing.php"));
    exit;
}

/*** DELETE LOGIC ***/
$delete_id = se($_GET, "delete_id", -1, false);

if ($delete_id > 0) {

      // Permission check
      if (!has_role("Admin")) {
        flash("You do not have permission to delete tournaments", "danger");
        header("Location: " . get_url("admin/list_golf.php"));
        exit;
      }

      // Placeholder delete logic
      flash("Tournament deleted", "success");

      // Redirect back to the previous page 
      $redirect = $_SERVER["HTTP_REFERER"] ?? get_url("admin/list_golf.php");
      header("Location: " . $redirect);
      exit;
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
$allowed_sorts = ["name", "start_date", "end_date", "tourn_id", "id"];
$sort = $_GET["sort"] ?? "start_date";

if (!in_array($sort, $allowed_sorts)) {
    $sort = "start_date";
}

// ORDER validation 
$allowed_orders = ["ASC", "DESC"];
$order = strtoupper($_GET["order"] ?? "DESC");

if (!in_array($order, $allowed_orders)) {
    $order = "DESC";
}

// Limit
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
$query .= " LIMIT $limit";


$stmt = $db->prepare($query);

foreach ($params as $key => $value) {
    if ($key === ":limit") {
        $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
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

                    <a href="<?php echo get_url("admin/edit_golf.php?id=" . $record["id"]); ?>">
                        Edit
                    </a>
                    <br>
                     <a href="<?php echo get_url("admin/list_golf.php?delete_id=" . $record["id"]); ?>"
                        onclick="return confirm('Are you sure you want to delete this tournament?');">
                        Delete
                    </a>
                    <br>
                   

                    <a href="<?php echo get_url("admin/golf_event.php?id=" . $record["id"]); ?>">
                        View
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

// Examples
<hr>
<h3>Example Entities</h3>
<p>Examples showing additional details and edit/delete links.</p>

<?php
$example_stmt = $db->prepare("SELECT * FROM `IT202-E25-Golf` ORDER BY id DESC LIMIT 7");
$example_stmt->execute();
$examples = $example_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="example-gallery">
<?php foreach ($examples as $row): ?>
    <div class="example-card">
        <h4><?php se($row["name"]); ?></h4>

        <p><strong>Tournament ID:</strong> <?php se($row["tourn_id"]); ?></p>
        <p><strong>Start Date:</strong> <?php se($row["start_date"]);  ?></p>
        <p><strong>End Date:</strong> <?php se($row["end_date"]); ?></p>
        <p><strong>API Row:</strong> <?php se($row["is_api"]); ?></p>

        <?php if (isset($row["created"])): ?>
            <p><strong>Created:</strong> <?php se($row["created"]); ?></p>
        <?php endif; ?>

        <?php if (isset($row["modified"])): ?>
            <p><strong>Modified:</strong> <?php se($row["modified"]); ?></p>
        <?php endif; ?>

        <a class="btn-edit" href="<?php echo get_url("admin/edit_golf.php?id=" . $row["id"]); ?>">Edit</a>
        <a class="btn-delete" 
        href="<?php echo get_url("admin/list_golf.php?delete_id=" . $row["id"]); ?>"
        onclick="return confirm('Are you sure you want to delete this tournament?');">
        Delete>
       </a>
       
    </div>
<?php endforeach;
?>
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

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
