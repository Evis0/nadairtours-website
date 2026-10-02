<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Nàdair Tours</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">Nàdair Tours</div>
        <ul class="nav-menu">
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin.php" class="active">Admin</a></li>
        </ul>
    </div>
</nav>

<section class="container" style="padding-top:40px; max-width:400px; margin:auto;">
    <h2 style="text-align:center;">Admin Login</h2>
    <form method="post" action="admin_process.php" style="margin-top:30px;">
        <label>Username:</label>
        <input type="text" name="username" required style="width:100%; padding:10px; margin-bottom:15px;">

        <label>Password:</label>
        <input type="password" name="password" required style="width:100%; padding:10px; margin-bottom:20px;">

        <button type="submit" class="btn" style="width:100%;">Login</button>
    </form>

    <?php if (isset($_GET['error'])): ?>
        <p style="color:red; text-align:center; margin-top:10px;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
</section>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Nàdair Tours</p>
</footer>

</body>
</html>
