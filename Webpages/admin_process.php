<?php
session_start();
require_once 'admin_db.php';

// Make sure $conn exists
if (!isset($conn) && isset($admin_conn)) {
    $conn = $admin_conn;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        header("Location: admin.php?error=Please+enter+both+fields");
        exit;
    }

    // Match lowercase column name
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();

            // FIXED: correct column name
            $db_pass = $admin['password'];

            if ($password === $db_pass) {
                $_SESSION['admin'] = $admin['username'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: admin.php?error=Invalid+password");
                exit;
            }
        } else {
            header("Location: admin.php?error=Invalid+username");
            exit;
        }

        $stmt->close();
    } else {
        header("Location: admin.php?error=Database+error");
        exit;
    }
} else {
    header("Location: admin.php");
    exit;
}

// Delete an order
if (isset($_GET['delete_order'])) {
    $id = (int)$_GET['delete_order'];
    $conn->query("DELETE FROM orders WHERE OrderID = $id");
    header("Location: admin_dashboard.php?tab=orders");
    exit;
}

if (!empty($_FILES['image_upload']['name'])) {
    $target = "images/" . basename($_FILES['image_upload']['name']);
    move_uploaded_file($_FILES['image_upload']['tmp_name'], $target);
    $image = $target;
}
?>
