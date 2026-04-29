<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];

if (isset($_GET["orgId"]) && isset($_GET["year"])) {

    // These params don't affect the golf API but are required for the assignment
    $data = [
        "orgId" => $_GET["orgId"],
        "year" => $_GET["year"]
    ];

    $endpoint = "https://live-golf-data.p.rapidapi.com/schedule";
    $isRapidAPI = true;
    $rapidAPIHost = "live-golf-data.p.rapidapi.com";

    // Fetch from API
    $result = get($endpoint, "GOLF_API_KEY", $data, $isRapidAPI, $rapidAPIHost);

    error_log("Response: " . var_export($result, true));

    // Decode JSON if successful
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
    $result = json_decode($result["response"], true);

    // DEBUG: show the key loaded from Render
    echo "<pre>KEY LOADED: " . getenv("GOLF_API_KEY") . "</pre>";

    // DEBUG: show the decoded API response
    echo "<pre>";
    var_export($result);
    echo "</pre>";
}

    } else {
        $result = [];
    }

?>
<div class="container-fluid">
    <h1>Golf Tournament Schedule</h1>
    <p>Fetch golf tournament schedule data from RapidAPI.</p>

    <form>
        <div>
            <label>OrgId</label>
            <input name="orgId" value="2" />

            <label>Year</label>
            <input name="year" value="2024" />

            <input type="submit" value="Fetch Golf Data" />
        </div>
    </form>

    <div class="row">
        <?php if (isset($result["results"])) : ?>
            <?php foreach ($result["results"] as $golf) : ?>
                <pre><?php var_export($golf); ?></pre>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>