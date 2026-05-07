<?php
require(__DIR__ . "/../../../lib/functions.php");

// 1. Validate ID BEFORE nav.php
$id = se($_GET, "id", -1, false);

if ($id < 1) {
    flash("Invalid tournament ID", "danger");
    header("Location: " . get_url("admin/list_golf.php"));
    exit;
}

// 2. Load record BEFORE nav.php
$db = getDB();
$query = "SELECT tourn_id, name, start_date, end_date
          FROM `IT202-E25-Golf`
          WHERE id = :id";

$stmt = $db->prepare($query);

try {
    $stmt->execute([":id" => $id]);
    $golf = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching record: " . var_export($e, true));
    flash("Error fetching record", "danger");
    header("Location: " . get_url("admin/list_golf.php"));
    exit;
}

if (!$golf) {
    flash("Tournament not found", "warning");
    header("Location: " . get_url("admin/list_golf.php"));
    exit;
}

// 3. NOW load nav.php
require(__DIR__ . "/../../../partials/nav.php");

// 4. Handle POST update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Only allow valid fields
    $allowed = ["tourn_id", "name", "start_date", "end_date"];
    $row = [];

    foreach ($allowed as $field) {
        if (isset($_POST[$field])) {
            $row[$field] = trim($_POST[$field]);
        }
    }

    $row["id"] = $id;

    try {
        $result = update("IT202-E25-Golf", $row);

        if ($result["rowCount"] > 0) {
            flash("Tournament updated successfully", "success");
        } else {
            flash("No changes detected", "warning");
        }

        header("Location: " . get_url("admin/edit_golf.php?id=" . $id));
        exit;

    } catch (PDOException $e) {
        error_log("Error updating tournament: " . var_export($e, true));
        flash("Error updating tournament", "danger");
    }
}
?>

<div class="container-fluid">
    <h3>Edit Golf Tournament</h3>

    <form method="POST" onsubmit="return validateEditForm();">
        <div class="mb-3">
            <label for="tourn_id">Tournament ID</label>
            <input type="text" name="tourn_id" id="tourn_id" required
                   value="<?php se($golf, "tourn_id"); ?>">
        </div>

        <div class="mb-3">
            <label for="name">Tournament Name</label>
            <input type="text" name="name" id="name" required
                   value="<?php se($golf, "name"); ?>">
        </div>

        <div class="mb-3">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" required
                   value="<?php se($golf, "start_date"); ?>">
        </div>

        <div class="mb-3">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" required
                   value="<?php se($golf, "end_date"); ?>">
        </div>

        <input type="submit" value="Update" class="btn btn-primary">
    </form>
</div>

<script>
    function validateEditForm() {
        let name = document.getElementById("name").value.trim();

        if (name.length < 4) {
            alert("Name must be at least 4 characters");
            return false;
        }

        return true;
    }
    </script>
<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
