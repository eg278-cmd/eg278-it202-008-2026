<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    // Validate task 
     if (empty($task)) {
        echo "Task can not be empty. <br>"; 
        $is_valid = false;
    }
    // Validate due date (must be valid MYSQL date format YYYY-MM-DD)
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $due)) {
        echo "Due date must be in YYYY-MM-DD format, <br>";
        $is_valid = false;
    }
    // Validate assigned (must be 'self')
    if (empty($assigned)) {
        echo "Assigned value was empty - defaulting to 'self'.<br>";
        $assigned = "self";
    }
    if ($assigned !== "self" && strlen(trim($assigned)) === 0) {
        echo "Assigned value was invalid - defaulting to 'self'.<br>";
        $assigned = 'self';
    } 
    // End validations

    
    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo
        */
        $query = "INSERT INTO M4_Todos (task, due, assigned) 
                  VALUES (:task, :due, :assigned)";
        $params = [
            ":task"     => $task,
            ":due"      => $due,
            ":assigned"  => $assigned
        ]; // Apply the proper PDO placeholder to variable mapping here
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);
            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }
        } catch (PDOException $e) {
           // Check if it's a unique constraint error
           if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
            echo "A todo with this task and due date already exists. Choose a different combination";
           } else{
            echo "There was an error inserting the record check the logs (terminal). Try again.";
           }
           error_log("Insert Error: " . var_export($e, true)); // shows in the terminal
        }
    } else {
        error_log("Creation input wasn't valid");
    }
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Create ToDo </h2>
        <form method="GET">
            <div>
                <label for="task">Task</label>
                <input type="text" id="task" name="task">
            <div>

            <div> 
                <label for="due">Due Date</label>
                <input type="date" id="due" name="due" placeholder="YYYY-MM-DD">
            </div>

            <div>
                <label for="assigned">Assigned To</label>
                <input type="text" id="assigned" name="assigned" value="self">
            </div>

             <div>
                <input type="submit" value="Create Todo">
            </div>
        </form>
    </section>
</body>
</body>

</html>