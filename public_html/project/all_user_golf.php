<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$db = getDB();

// ----------------------------
// REMOVE ALL MATCHING ASSOCIATIONS 
// ----------------------------
if (isset($_GET["remove_all"]) && $_GET["remove_all"] == "1") {
    $username = $_GET["username"] ?? "";
    $tourn_search = $_GET["search"] ?? "";

    $deleteQuery = "
DELETE ug FROM `IT202-E25-UserGolf` ug
JOIN Users u ON ug.user_id = u.id
JOIN `IT202-E25-Golf` golf ON userg.golf_id = golf.id
WHERE 1=1
";

    $deleteParams = [];

    if (!empty($username)) {
        $deleteQuery .= " AND u.username LIKE :username";
        $deleteParams[":username"] = "%" . $username . "%";
    }

    if (!empty($tourn_search)) {
        $deleteQuery .= " AND g.name LIKE :tname";
        $deleteParams[":tname"] = "%" . $tourn_search . "%";
    }

    $stmt = $db->prepare($deleteQuery);
    $stmt->execute($deleteParams);

    flash("All matching associations removed", "success");
    header("Location: all_user_golf.php");
    exit;
}

// ----------------------------
// GET FILTERS
// ----------------------------
$username = $_GET["username"] ?? "";
$tourn_search = $_GET["search"] ?? "";
$sort = $_GET["sort"] ?? "username_asc";
$limit = intval($_GET["limit"] ?? 10);
$page = intval($_GET["page"] ?? 1);

if ($limit < 1) $limit = 1;
if ($limit > 100) $limit = 100;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$params = [];

// ----------------------------
// MAIN QUERY
// ----------------------------
$query = "
SELECT
usergolf.user_id,
usergolf.golf_id,
user.username,
golf.name AS tournament_name,
golf.start_date,
golf.end_date,
(
SELECT COUNT(*)
FROM `IT202-E25-UserGolf` usergolf2
WHERE usergolf2.golf_id = usergolf.golf_id
) AS total_users_for_tournament
FROM `IT202-E25-UserGolf` usergolf
JOIN Users user ON usergolf.user_id = user.id
JOIN `IT202-E25-Golf` golf ON usergolf.golf_id = golf.id
WHERE 1=1
";

// ----------------------------
// FILTERS
// ----------------------------
if (!empty($username)) {
    $query .= " AND user.username LIKE :username";
    $params[":username"] = "%" . $username . "%";
}

if (!empty($tourn_search)) {
    $query .= " AND golf.name LIKE :tname";
    $params[":tname"] = "%" . $tourn_search . "%";
}

// ----------------------------
// SORTING
// ----------------------------
switch ($sort) {
    case "username_desc":
        $query .= " ORDER BY user.username DESC, golf.name ASC";
        break;
    case "tournament_asc":
        $query .= " ORDER BY golf.name ASC, user.username ASC";
        break;
    case "tournament_desc":
        $query .= " ORDER BY golf.name DESC, user.username ASC";
        break;
    case "users_desc":
        $query .= " ORDER BY total_users_for_tournament DESC, golf.name ASC";
        break;
    default:
        $query .= " ORDER BY user.username ASC, golf.name ASC";
        break;
}

// ----------------------------
// LIMIT 
// ----------------------------
$query .= " LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------------------------
// TOTAL FOR STATS
// ----------------------------
$countQuery = "
SELECT COUNT(*) AS total
FROM `IT202-E25-UserGolf` usergolf
JOIN Users user ON usergolf.user_id = user.id
JOIN `IT202-E25-Golf` golf ON usergolf.golf_id = golf.id
WHERE 1=1
";

$countParams = [];

if (!empty($username)) {
    $countQuery .= " AND user.username LIKE :username";
    $countParams[":username"] = "%" . $username . "%";
}

if (!empty($tourn_search)) {
    $countQuery .= " AND golf.name LIKE :tname";
    $countParams[":tname"] = "%" . $tourn_search . "%";
}

$countStmt = $db->prepare($countQuery);
$countStmt->execute($countParams);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)["total"];
$totalPages = ceil($total / $limit);
$currentCount = count($results);
?>

<style>
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #e8f1ff !important;
    }

    .table-striped tbody tr:nth-of-type(even) {
        background-color: #ffffff !important;
    }
</style>

<div class="container mt-4">
    <h2>All Users – Golf Associations</h2>
    <p>This page shows all user–tournament associations.</p>

    <!-- FILTER FORM -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-4 mb-2">
                <input type="text"
                    name="username"
                    placeholder="Filter by username..."
                    value="<?php echo htmlspecialchars($username); ?>"
                    class="form-control">
            </div>
            <div class="col-md-4 mb-2">
                <input type="text"
                    name="search"
                    placeholder="Filter by tournament name..."
                    value="<?php echo htmlspecialchars($tourn_search); ?>"
                    class="form-control">
            </div>
            <div class="col-md-2 mb-2">
                <select name="sort" class="form-control">
                    <option value="username_asc" <?php if ($sort == "username_asc") echo "selected"; ?>>Username (A–Z)</option>
                    <option value="username_desc" <?php if ($sort == "username_desc") echo "selected"; ?>>Username (Z–A)</option>
                    <option value="tournament_asc" <?php if ($sort == "tournament_asc") echo "selected"; ?>>Tournament (A–Z)</option>
                    <option value="tournament_desc" <?php if ($sort == "tournament_desc") echo "selected"; ?>>Tournament (Z–A)</option>
                    <option value="users_desc" <?php if ($sort == "users_desc") echo "selected"; ?>>Most Users per Tournament</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <input type="number"
                    name="limit"
                    min="1"
                    max="100"
                    value="<?php echo $limit; ?>"
                    class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Apply Filters</button>

        <?php if (!empty($username) || !empty($tourn_search)): ?>
            <a href="?sort=<?php echo urlencode($sort); ?>&limit=<?php echo $limit; ?>"
                class="btn btn-secondary ms-2">Clear Filters</a>
        <?php endif; ?>
    </form>

    <!-- STATS -->
    <div class="mb-3">
        <strong>Results Shown:</strong> <?php echo $currentCount; ?><br>
        <strong>Total Matching Associations:</strong> <?php echo $total; ?>
    </div>

    <!-- REMOVE ALL MATCHING ASSOCIATIONS -->
    <?php if ($total > 0 && (!empty($username) || !empty($tourn_search))): ?>
        <div class="mb-3">
            <a href="all_user_golf.php?remove_all=1&username=<?php echo urlencode($username); ?>&search=<?php echo urlencode($tourn_search); ?>"
                class="btn btn-danger"
                onclick="return confirm('Remove ALL matching associations?');">
                Remove All Associations for Matching User(s)
            </a>
        </div>
    <?php endif; ?>

    <!-- RESULTS -->
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-primary">
            <tr>
                <th>Summary</th>
                <th>Username</th>
                <th>Tournament</th>
                <th>Total Users for Tournament</th>
                <th style="width: 220px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No results available.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td>
                            <?php
                            echo htmlspecialchars($row["username"]) .
                                " is associated with " .
                                htmlspecialchars($row["tournament_name"]);
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo get_url("profile.php?id=" . $row["user_id"]); ?>">
                                <?php echo htmlspecialchars($row["username"]); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row["tournament_name"]); ?>
                            <br>
                            <small>
                                Start: <?php echo htmlspecialchars($row["start_date"]); ?>,
                                End: <?php echo htmlspecialchars($row["end_date"] ?? "NULL"); ?>
                            </small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row["total_users_for_tournament"]); ?>
                        </td>
                        <td>
                            <a href="<?php echo get_url("admin/golf_event.php?id=" . $row["golf_id"]); ?>"
                                class="btn btn-info btn-sm">🔍 View</a>

                            <a href="unassigned.php?user_id=<?php echo $row["user_id"]; ?>&golf_id=<?php echo $row["golf_id"]; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Remove this association?');">
                                 Remove Relationship
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- PAGES -->
    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination mt-3">
                <li class="page-item disabled"><span class="page-link">Pages:</span></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link"
                            href="?username=<?php echo urlencode($username); ?>&search=<?php echo urlencode($tourn_search); ?>&sort=<?php echo $sort; ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>