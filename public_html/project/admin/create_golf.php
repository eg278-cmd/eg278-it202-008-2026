<?php
require(__DIR__ . "/../../../partials/nav.php");

// Admin check (NO redirect because nav.php already printed HTML)
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit; // stop the page cleanly
}
?>

<?php

if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $quote = [];

    if ($action === "fetch") {

        $result = fetch_golf_schedule();

        // echo "<pre>";
        // var_export($result);
        // echo "</pre>";
        // exit;

        error_log("Data from API: " . var_export($result, true));

        if ($result && isset($result["schedule"][0])) {
            $g = $result["schedule"][0];

            // Extract timestamps
            $start_ts = $g["date"]["start"]["\$date"]["\$numberLong"];
            $end_ts   = $g["date"]["end"]["\$date"]["\$numberLong"];

            // Transform into DB-ready fields
            $quote = [
                "tourn_id"   => $g["tournId"],
                "name"       => $g["name"],
                "start_date" => date("Y-m-d", $start_ts / 1000),
                "end_date"   => date("Y-m-d", $end_ts / 1000),
                "is_api"     => 1
            ];
        } else {
            flash("API returned no results", "warning");
        }

    } else if ($action === "create") {

        foreach ($_POST as $k => $v) {
            if (!in_array($k, ["tourn_id", "name", "start_date", "end_date"])) {
                unset($_POST[$k]);
            }
        }

        $quote = $_POST;
        $quote["is_api"] = 0;
    }

    // Insert into DB
    if (!empty($quote)) {
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
                <input type="number" name="tourn_id" required>
            </div>
            <div class="mb-3">
                <label>Tournament Name</label>
                <input type="text" name="name" required>
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
