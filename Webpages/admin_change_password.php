<?php
session_start();
require_once 'admin_db.php';

// Ensure connection
if (!isset($conn) && isset($admin_conn)) {
    $conn = $admin_conn;
}

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php?error=Please+login+first");
    exit;
}

// Get the logged-in admin username
$currentAdmin = $_SESSION['admin'];

$message = "";
$messageType = ""; // success or error

// Handle Password Change

if (isset($_POST['change_password'])) {

    $old = trim($_POST['old_password']);
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    // Validation
    if ($old === "" || $new === "" || $confirm === "") {
        $message = "Please fill in all password fields.";
        $messageType = "error";
    } else {

        // Fetch admin
        $stmt = $conn->prepare("SELECT * FROM admin WHERE Username = ? LIMIT 1");
        $stmt->bind_param("s", $currentAdmin);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if (!$admin) {
            $message = "Admin account not found.";
            $messageType = "error";
        } else if ($old !== $admin['password']) {
            $message = "Old password is incorrect.";
            $messageType = "error";
        } else if ($new !== $confirm) {
            $message = "New passwords do not match.";
            $messageType = "error";
        } else {

            // Update password
            $stmt = $conn->prepare("UPDATE admin SET Password = ? WHERE Username = ?");
            $stmt->bind_param("ss", $new, $currentAdmin);
            $stmt->execute();

            $message = "Password successfully updated.";
            $messageType = "success";
        }

        $stmt->close();
    }
}

// Handle Adding A New Admin
if (isset($_POST['add_admin'])) {

    $newUser = trim($_POST['new_username']);
    $newPass = trim($_POST['new_password2']);

    if ($newUser === "" || $newPass === "") {
        $message = "Please fill in both username and password.";
        $messageType = "error";
    } else {

        // Check if username exists
        $check = $conn->prepare("SELECT * FROM admin WHERE Username = ? LIMIT 1");
        $check->bind_param("s", $newUser);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows > 0) {
            $message = "That username already exists.";
            $messageType = "error";
        } else {
            // Insert new admin
            $stmt = $conn->prepare("INSERT INTO admin (Username, Password) VALUES (?, ?)");
            $stmt->bind_param("ss", $newUser, $newPass);
            $stmt->execute();

            $message = "New admin created successfully!";
            $messageType = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings - Nàdair Tours</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">Nàdair Tours Admin</div>
        <ul class="nav-menu">
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <li><a href="logout.php" style="color:#ffb3b3;">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="max-width:650px; margin-top:30px;">

    <h2>Admin Settings</h2>

    <!-- Result Message -->
    <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === 'success' ? 'success' : 'error'; ?>"
             style="padding:10px; margin-bottom:15px; border-radius:6px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Change Password -->
    <section>
        <h3>Change Your Password</h3>
        <form method="POST">
            <label>Old Password:</label>
            <input type="password" name="old_password" required>

            <label>New Password:</label>
            <input type="password" name="new_password" required>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="change_password" class="btn"
                    style="margin-top:10px;">Update Password</button>
        </form>
    </section>

    <hr style="margin:40px 0;">

    <!-- Add Admin -->
    <section>
        <h3>Create a New Admin</h3>
        <form method="POST">
            <label>New Username:</label>
            <input type="text" name="new_username" required>

            <label>New Password:</label>
            <input type="password" name="new_password2" required>

            <button type="submit" name="add_admin" class="btn"
                    style="background:#3366ff; margin-top:10px;">
                Create Admin
            </button>
        </form>
    </section>

</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Nàdair Tours</p>
</footer>

</body>
</html>
