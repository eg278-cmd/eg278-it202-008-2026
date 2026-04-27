<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
?>

<?php
$id = se($_GET, "id", -1, false);
//TODO handle golf fields
if (isset($_POST["symbol"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["tourn_id", "name", "start_date", "end_date"])) {
            unset($_POST[$k]);
        }
        $quote = $_POST;
        error_log("Cleaned up POST: " . var_export($quote, true));
    }
    // Ideally only the table name should need to change for most queries
    //update data
    $row = $_POST;
    $row["id"] = $id; // add id to the stock array for the update
    try {
        $quote = uppercaseSymbolCurrency([$quote])[0];
        $r = update("IT202-E25-Golf", $quote);
        if ($r["rowCount"]) {
            flash("Updated " . $r["rowCount"] . " record(s)", "success");
        } else {
            flash("Error updating record (this can occur if no properties changed)", "warning");
        }
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
    catch(Exception $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred: " . $e->getMessage(), "danger");
    }
}

$golf = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT tourn_id, name, start_date, end_date 
     FROM `IT202-E25-Golf`
      WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $golf = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_golf.php")));
}

?>
<div class="container-fluid">
    <h3>Edit Golf Tournaments</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="tourn_id">Tournament ID</label>
            <input type="text" name="tourn_id" id="tourn_id" placeholder="Tournament ID" required value="<?php se($golf, "tourn_id"); ?>">
        </div>
        <div class="mb-3">
            <label for="name">Tournament Name</label>
            <input type="text" name="name" id="name" placeholder="Tournament Name" required value="<?php se($golf, "name"); ?>">
        </div>
        <div class="mb-3">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" placeholder="Start Date" required value="<?php se($golf, "start_date"); ?>">
        </div>
        <div class="mb-3">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" placeholder="End Date" required value="<?php se($golf, "end_date"); ?>">
        </div>
    
        <input type="submit" value="Update" class="btn btn-primary">
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>