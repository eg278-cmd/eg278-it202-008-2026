<?php
// require functions.php to pull in flash()
require(__DIR__ . "/../../lib/functions.php");
reset_session();
flash("You have been logged out","success");
header("Location: login.php"); // redirect back to login