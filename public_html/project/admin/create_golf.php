<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    // die(header("Location: " . get_url("landing.php")));
}
?>

<?php

//TODO handle stock fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $tourn_id = $_POST["tourn_id"] ?? "";
    $golf = [];
   
        if ($action === "fetch") {
            // Fetch API Golf Tournaments
            $result = fetch_golf_schedule(1, 2024);

            error_log("Data from Golf API" . var_export($result, true));
            if ($result) {

                $row = $decoded["schedule"][0];

                error_log("GOLF DATA: " . var_export($row, true));
                
                $golf = [
                    "tourn_id" => $row["tourn_id"] ?? "",
                    "name" => $row["name"] ?? "",
                    "start_date" => $row["date"]["start"][$date]["$numberLong"] ?? "",
                    "end_date" => $row["date"]["end"]["$date"]["$numberLong"] ?? "",
                    "is_api" => 1
                ];
            }
        }
    
         else if ($action === "create") {
            foreach ($_POST as $k => $v) {
                // remove keys that aren't part of your data
                // this is both for security and for our dynamic DB logic to work correctly
                // the keys must match the column names of your table
                if (!in_array($k, ["tourn_id", "name", "start_date", "end_date"])) {
                    unset($_POST[$k]);
                }
            }
            $golf = $_POST;
            $golf["is_api"] = 0;
            error_log("Cleaned up POST: " . var_export($golf, true));
        }
     else {
        flash("You must provide a tournament ID", "warning");
    }
    //insert data - Below should only really need the table name changes
    // the query building should work for all regular inserts
    $db = getDB();
    $query = "INSERT INTO `IT202-E25-Golf` ";
    $columns = [];
    $params = [];
    //per record
    foreach ($golf as $k => $v) {
        array_push($columns, "`$k`");
        $params[":$k"] = $v;
    }
    $columns[] = "`tourn_id`";
    $params[":tourn_id"] = $tourn_id;
    $columns[] = "`name`";
    $params[":name"] = $golf["tournament_name"] ?? "";
    $columns[] = "`start_date`";
    $params[":start_date"] = $golf["start_date"] ?? $golf["startDate"] ?? "";
    $columns[] = "`end_date`";
    $params[":end_date"] = $golf["end_date"] ?? $golf["endDate"] ?? "";
    $query .= "(" . join(",", $columns) . ")";
    $query .= "VALUES (" . join(",", array_keys($params)) . ")";
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        error_log("PDO ERROR INFO: " . var_export($e->errorInfo, true));
        flash("An error occurred", "danger");
    }
}


//TODO handle manual create stock
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
            <div>
                <label for="tourn_id">Tournament ID</label>
                <input type="text" name="tourn_id" id="tourn_id" placeholder="Tournament ID">
            </div>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch API Tournament" class="btn btn-primary">
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <div class="mb-3">
                <label for="tourn_id">Tournament ID</label>
                <input type="text" name="tourn_id" id="tourn_id" placeholder="Tournament ID" required>
            </div>
            <div class="mb-3">
                <label for="name">Tournament Name</label>
                <input type="text" name="name" id="name" placeholder="Tournament Name" required>
            </div>
            <div class="mb-3">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" placeholder="Start Date" required>
            </div>
            <div class="mb-3">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" placeholder="End Date" required>
            </div>

            <input type="hidden" name="action" value="create">
            <input type="submit" value="Create Tournament" class="btn btn-primary">
        </form>
    </div>
</div>
<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "block" : "none";
            }
        }
    }

</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>