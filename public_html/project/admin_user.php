<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

if (!has_role("Admin")) {
    flash("You do not have permission to access this page", "danger");
    die(header("Location: " . get_url("home.php")));
}

$db = getDB();

// Handle association 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selected_users = $_POST["users"] ?? [];
    $selected_entities = $_POST["entities"] ?? [];

    foreach ($selected_users as $uid) {
        foreach ($selected_entities as $gid) {

            // Check if association exists
            $check = $db->prepare("SELECT 1 FROM `IT202-E25-UserGolf` WHERE user_id = :uid AND golf_id = :gid");
            $check->execute([":uid" => $uid, ":gid" => $gid]);

            if ($check->fetch()) {
                // Exists → remove it
                $del = $db->prepare("DELETE FROM `IT202-E25-UserGolf` WHERE user_id = :uid AND golf_id = :gid");
                $del->execute([":uid" => $uid, ":gid" => $gid]);
            } else {
                // Does not exist → add it
                $ins = $db->prepare("INSERT INTO `IT202-E25-UserGolf` (user_id, golf_id) VALUES (:uid, :gid)");
                $ins->execute([":uid" => $uid, ":gid" => $gid]);
            }
        }
    }

    flash("Associations updated successfully!", "success");
}

// Handle search
$user_search = trim($_GET["user_search"] ?? "");
$entity_search = trim($_GET["entity_search"] ?? "");

$users = [];
$entities = [];

// User search 
if ($user_search !== "") {
    $stmt = $db->prepare("SELECT id, username FROM Users WHERE username LIKE :uname LIMIT 25");
    $stmt->execute([":uname" => "%$user_search%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Entity search 
if ($entity_search !== "") {
    $stmt = $db->prepare("SELECT id, name FROM `IT202-E25-Golf` WHERE name LIKE :gname LIMIT 25");
    $stmt->execute([":gname" => "%$entity_search%"]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <h2>Admin Association Page</h2>

    <form method="GET" class="mb-4">
        <div>
            <label>Username (partial match):</label>
            <input type="text" name="user_search" value="<?php echo se($user_search); ?>">
        </div>

        <div>
            <label>Entity name (partial match):</label>
            <input type="text" name="entity_search" value="<?php echo se($entity_search); ?>">
        </div>

        <button type="submit">Search</button>
    </form>

    <form method="POST">
        <div style="display:flex; gap:40px;">

            <!-- USERS COLUMN -->
            <div style="flex:1;">
                <h4>Users</h4>
                <?php if (empty($users)): ?>
                    <p>No results available.</p>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <div>
                            <input type="checkbox" name="users[]" value="<?php echo $u['id']; ?>">
                            <?php echo se($u["username"]); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ENTITIES  -->
            <div style="flex:1;">
                <h4>Entities</h4>
                <?php if (empty($entities)): ?>
                    <p>No results available.</p>
                <?php else: ?>
                    <?php foreach ($entities as $golf): ?>
                        <div>
                            <input type="checkbox" name="entities[]" value="<?php echo $golf['id']; ?>">
                            <?php echo se($golf["name"]); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <button type="submit" class="mt-3">Apply Associations</button>
    </form>
</div>