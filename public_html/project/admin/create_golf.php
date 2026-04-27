<?php
if (!has_role("Admin")) {
    flash("You don't have permission", "warning");
    header("Location: " . get_url("landing.php"));
    exit;
}

require(__DIR__ . "/../../../partials/nav.php");

?>

<?php

// Handle golf fetch or manual create
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $quote = [];

    if ($action === "fetch") {

        // Fetch from API
        $result = fetch_golf_schedule();

        error_log("Data from API: " . var_export($result, true));

        if ($result && isset($result["results"][0])) {
            $g = $result["results"][0];

            // TRANSFORMATION STEP (Milestone requirement)
            $quote = [
                "tournament_id" => $g["tournament_id"],
                "tournament_name" => $g["tournament_name"],
                "course" => $g["course"],
                "location" => $g["location"],
                "start_date" => $g["start_date"],
                "end_date" => $g["end_date"],
                "is_api" => 1
            ];
        }
    } else if ($action === "create") {

        // Clean POST keys to match DB columns
        foreach ($_POST as $k => $v) {
            if (!in_array($k, [
                "tournament_id",
                "tournament_name",
                "course",
                "location",
                "start_date",
                "end_date"
            ])) {
                unset($_POST[$k]);
            }
        }

        $quote = $_POST;
        $quote["is_api"] = 0;

        error_log("Cleaned POST: " . var_export($quote, true));
    }

    // Insert into DB
    $db = getDB();
    $query = "INSERT INTO `IT202-E25-Golf` ";
    $columns = [];
    $params = [];

    foreach ($quote as $k => $v) {
        $columns[] = "`$k`";
        $params[":$k"] = $v;
    }

    $query .= "(" . join(",", $columns) . ")";
    $query .= " VALUES (" . join(",", array_keys($params)) . ")";

    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));

    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        error_log("DB Error: " . var_export($e, true));
        flash("An error occurred", "danger");
    }
}
?>

<div class="container-fluid">
    <h3>Create or Fetch Golf Tournament</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('fetch')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('create')">Create</a>
        </li>
    </ul>

    <div id="fetch" class="tab-target">
        <form method="POST">
            <p>Fetches the next tournament from the API</p>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch" class="btn btn-primary">
        </form>
    </div>

    <div id="create" style="display:none;" class="tab-target">
        <form method="POST">
            <div class="mb-3">
                <label>Tournament ID</label>
                <input type="number" name="tournament_id" required>
            </div>
            <div class="mb-3">
                <label>Tournament Name</label>
                <input type="text" name="tournament_name" required>
            </div>
            <div class="mb-3">
                <label>Course</label>
                <input type="text" name="course" required>
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" name="location" required>
            </div>
            <div class="mb-3">
                <label>Start Date</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="mb-3">
                <label>End Date</label>
                <input type="date" name="end_date" required>
            </div>

            <input type="hidden" name="action" value="create">
            <input type="submit" value="Create" class="btn btn-primary">
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        let targets = document.getElementsByClassName("tab-target");
        for (let t of targets) {
            t.style.display = (t.id === tab) ? "block" : "none";
        }
    }
</script>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>