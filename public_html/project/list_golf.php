<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

$db = getDB();

// ----------------------------
// GET FILTERS
// ----------------------------
$search = $_GET["search"] ?? "";
$sort = $_GET["sort"] ?? "name_asc";
$limit = intval($_GET["limit"] ?? 10);
$page = intval($_GET["page"] ?? 1);

if ($limit < 1) $limit = 1;
if ($limit > 100) $limit = 100;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

$params = [];
$query = "
SELECT golf.id, golf.name, golf.start_date, golf.end_date
FROM `IT202-E25-Golf` golf
WHERE 1=1
";

if (!empty($search)) {
    $query .= " AND golf.name LIKE :search";
    $params[":search"] = "%$search%";
}

// ----------------------------
// SORTING
// ----------------------------
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

// LIMIT + OFFSET
$query .= " LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total for pages
$countQuery = "
SELECT COUNT(*) as total
FROM `IT202-E25-Golf` golf
WHERE 1=1
";
if (!empty($search)) {
    $countQuery .= " AND golf.name LIKE :search";
}
$countStmt = $db->prepare($countQuery);
if (!empty($search)) {
    $countStmt->bindValue(":search", "%$search%");
}
$countStmt->execute();
$total = $countStmt->fetch(PDO::FETCH_ASSOC)["total"];
$totalPages = ceil($total / $limit);
?>

<div class="container mt-4">
    <h2>List of Tournaments</h2>

    <!-- FILTER FORM -->
    <form method="GET" class="mb-3">
        <input type="text" name="search" placeholder="Search by name..."
            value="<?php echo htmlspecialchars($search); ?>"
            class="form-control mb-2">

        <select name="sort" class="form-control mb-2">
            <option value="name_asc" <?php if ($sort == "name_asc") echo "selected"; ?>>Name (A–Z)</option>
            <option value="name_desc" <?php if ($sort == "name_desc") echo "selected"; ?>>Name (Z–A)</option>
            <option value="start_asc" <?php if ($sort == "start_asc") echo "selected"; ?>>Start Date (ASC)</option>
            <option value="start_desc" <?php if ($sort == "start_desc") echo "selected"; ?>>Start Date (DESC)</option>
        </select>

        <input type="number" name="limit" min="1" max="100"
            value="<?php echo $limit; ?>"
            class="form-control mb-2">

        <button type="submit" class="btn btn-primary">Apply</button>
    </form>

    <!-- RESULTS -->
    <?php if (empty($results)) : ?>
        <p>No results available.</p>
    <?php else : ?>
        <?php foreach ($results as $row) : ?>
            <div class="card mb-2 p-3">
                <h4><?php echo htmlspecialchars($row["name"]); ?></h4>
                <p>Start: <?php echo htmlspecialchars($row["start_date"]); ?></p>
                <p>End: <?php echo htmlspecialchars($row["end_date"]); ?></p>

                <a href="<?php echo get_url("admin/golf_event.php?id=" . $row["id"]); ?>"
                    class="btn btn-info btn-sm">View</a>

                <a href="<?php echo get_url("assign.php?golf_id=" . $row["id"]); ?>"
                    class="btn btn-success btn-sm">Assign to Me</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- PAGINATION -->
    <nav>
        <ul class="pagination mt-3">
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link"
                        href="?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>