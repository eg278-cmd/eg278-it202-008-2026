<?php
require_once(__DIR__ . "/../../partials/nav.php");

// PROCESS LOGIN BEFORE ANY HTML
if (isset($_POST["email"], $_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    $hasError = false;

    // Email/username validation
    if (empty($email)) {
        flash("Email or username is required.", "danger");
        $hasError = true;
    }

    if (str_contains($email, "@")) {
        $email = sanitize_email($email);
        if (!is_valid_email($email)) {
            flash("Invalid email address.", "danger");
            $hasError = true;
        }
    } else {
        $email = strtolower(trim($email));
        if (!is_valid_username($email)) {
            flash("Username must be lowercase, alphanumerical, and can only contain _ or -", "danger");
            $hasError = true;
        }
    }

    // Password validation
    if (empty($password)) {
        flash("Password is required.", "danger");
        $hasError = true;
    }

    if (!is_valid_password($password)) {
        flash("Password must be at least 8 characters long.", "danger");
        $hasError = true;
    }

    // If no validation errors, attempt login
    if (!$hasError) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, password, username FROM Users
WHERE email = :email OR username = :email");

        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $ambigify = false;

                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);

                    if (password_verify($password, $hash)) {
                        // Login success
                        $_SESSION["user"] = $user;

                        // Load roles
                        try {
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles
JOIN UserRoles ON Roles.id = UserRoles.role_id
WHERE UserRoles.user_id = :user_id
AND Roles.is_active = 1
AND UserRoles.is_active = 1");
                            $stmt->execute([":user_id" => get_user_id()]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }

                        $_SESSION["user"]["roles"] = $roles ?? [];

                        // REDIRECT BEFORE ANY HTML OUTPUT
                        header("Location: landing.php");
                        exit;
                    } else {
                        $ambigify = true;
                    }
                } else {
                    $ambigify = true;
                }

                if ($ambigify) {
                    flash("Invalid login attempt. Please check your email and password.", "danger");
                }
            }
        } catch (Exception $e) {
            flash("There was an error logging in. Please try again later.", "danger");
            error_log("Login Error: " . var_export($e, true));
        }
    }
}
?>

<!-- HTML STARTS ONLY AFTER ALL REDIRECT LOGIC -->
<h3>Login</h3>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email or Username</label>
        <input id="email" type="text" name="email" required />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" />
    </div>
    <input type="submit" value="Login" />
</form>

<script>
    function validate(form) {
        let email = form.email.value.trim();
        let password = form.password.value.trim();

        if (email.length === 0) {
            alert("Email or username is required.");
            return false;
        }

        if (password.length === 0) {
            alert("Password is required.");
            return false;
        }

        return true;
    }
</script>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>