<?php
require(__DIR__ . "/../../lib/functions.php");
is_logged_in(true);

$db = getDB();
$user_id = get_user_id();

// Filters
$tourn_search = $_GET["search"] ?? "";
$sort = $_GET["sort"] ?? "name_asc";

// Limit
$limit = intval($_GET["limit"] ?? 10);
if ($limit < 1) $limit = 1;
if ($limit > 100) $limit = 100;

$page = intval($_GET["page"] ?? 1);
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$query = "
SELECT golf.id, golf.name, golf.start_date, golf.end_date
FROM `IT202-E25-Golf` golf
WHERE GOLF.id NOT IN (
SELECT usergolf.golf_id
FROM `IT202-E25-UserGolf` usergolf
WHERE usergolf.user_id = :uid
)";
$params = [":uid" => $user_id];

// Apply filters
if (!empty($tourn_search)) {
    $query .= " AND golf.name LIKE :tname";
    $params[":tname"] = "%" . $tourn_search . "%";
}

// Sorting
switch ($sort) {
    case "name_desc":
        $query .= " ORDER BY golf.name DESC";
        break;
    case "start_asc":
        $query .= " ORDER BY golf.start_date ASC";
        break;
    case "start_desc":
        $query .= " ORDER BY golf.start_date DESC";
        break;
    default:
        $query .= " ORDER BY golf.name ASC";
}

// Limit applied
$query .= " LIMIT :limit OFFSET :offset";
$params[":limit"] = $limit;
$params[":offset"] = $offset;

// Results
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total Stats
$countQuery = "
SELECT COUNT(*) AS total
FROM `IT202-E25-Golf` golf
WHERE golf.id NOT IN (
SELECT usergolf.golf_id
FROM `IT202-E25-UserGolf` usergolf
WHERE usergolf.user_id = :uid
)
";

$countParams = [":uid" => $user_id];

if (!empty($tourn_search)) {
    $countQuery .= " AND golf.name LIKE :tname";
    $countParams[":tname"] = "%" . $tourn_search . "%";
}

$stmt2 = $db->prepare($countQuery);
$stmt2->execute($countParams);
$total = $stmt2->fetchColumn();

require(__DIR__ . "/../../partials/nav.php");
?>

<div class="container mt-4">
    <h3>Unassociated Tournaments</h3>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" placeholder="Search tournaments"
                value="<?php echo se($tourn_search); ?>" class="form-control">
        </div>

        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="name_asc" <?php echo $sort == "name_asc" ? "selected" : ""; ?>>Name A–Z</option>
                <option value="name_desc" <?php echo $sort == "name_desc" ? "selected" : ""; ?>>Name Z–A</option>
                <option value="start_asc" <?php echo $sort == "start_asc" ? "selected" : ""; ?>>Start Date ↑</option>
                <option value="start_desc" <?php echo $sort == "start_desc" ? "selected" : ""; ?>>Start Date ↓</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="number" name="limit" min="1" max="100"
                value="<?php echo $limit; ?>" class="form-control">
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">Apply</button>
        </div>
    </form>

    <!-- STATS -->
    <div class="mb-3">
        <strong>Results Shown:</strong> <?php echo count($results); ?><br>
        <strong>Total Possible:</strong> <?php echo $total; ?>
    </div>

    <hr>

    <!-- RESULTS -->
    <?php if (empty($results)): ?>
        <p>No results available.</p>
    <?php else: ?>
        <?php foreach ($results as $row): ?>
            <div class="card mb-2 p-3">
                <h5><?php echo se($row["name"]); ?></h5>
                <p>
                    <?php echo se($row["name"]); ?> is available for association.<br>
                    <strong>Start:</strong> <?php echo se($row["start_date"]); ?><br>
                    <strong>End:</strong> <?php echo se($row["end_date"]); ?>
                </p>

                <a href="my_golf_tournaments.php?id=<?php echo $row["id"]; ?>"
                    class="btn btn-primary btn-sm">View Tournament</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- PAGINATION -->
    <?php if ($total > $limit): ?>
        <nav>
            <ul class="pagination mt-3">
                <?php for ($i = 1; $i <= ceil($total / $limit); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&search=<?php echo urlencode($tourn_search); ?>&sort=<?php echo $sort; ?>&limit=<?php echo $limit; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>