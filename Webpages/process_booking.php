<?php
session_start();

// Ensure $conn exists
require_once 'tours_db.php';
require_once __DIR__ . '/orders_db.php';

// Make sure $conn always exists before any logic
if (!isset($conn)) {
    if (isset($tours_conn)) $conn = $tours_conn;
    if (isset($orders_conn)) $conn = $orders_conn;
}
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }

// Load cart
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (!is_array($cart) || count($cart) === 0) {
    header("Location: tours.php");
    exit();
}

// Load booking dates from cart page
$booking_dates = isset($_SESSION['booking_dates']) ? $_SESSION['booking_dates'] : [];

if (empty($booking_dates)) {
    $_SESSION['error_message'] = "You must select booking dates before continuing.";
    header("Location: cart.php");
    exit();
}

// Handle booking submission
$errors = [];
$name = $email = $phone = $requests = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize input
    $name = trim(strip_tags($_POST['name']));
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $phone = trim(strip_tags($_POST['phone']));
    $requests = trim(strip_tags($_POST['requests']));

    // Validate
    if (empty($name)) $errors[] = "Please enter your name.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (empty($phone)) {
        $errors[] = "Please enter your phone number.";
    } elseif (!preg_match('/^[0-9]+$/', $phone)) {
        $errors[] = "Phone number can only contain digits.";
    }

    // Process booking if no errors
    if (empty($errors)) {

        $query = "INSERT INTO orders (TourID, Name, Email, Phone, `Booking Date`, Participants, `Special Requests`)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) die("Prepare failed: " . $conn->error);

        foreach ($cart as $id => $item) {

            $tourID = (int)$item['TourID'];
            $participants = isset($item['quantity']) ? (int)$item['quantity'] : 1;

            // Date selected in cart
            $date = isset($booking_dates[$tourID]) ? $booking_dates[$tourID] : null;

            $stmt->bind_param(
                    "issssis",
                    $tourID,
                    $name,
                    $email,
                    $phone,
                    $date,
                    $participants,
                    $requests
            );

            $stmt->execute();
        }

        $stmt->close();

        // Clear session
        unset($_SESSION['cart']);
        unset($_SESSION['booking_dates']);
        unset($_SESSION['temp_dates']);
        unset($_SESSION['booking_cart']);

        // Confirmation HTML
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
              <link rel='stylesheet' href='styles.css'>
              <title>Booking Confirmed</title></head><body>";

        echo "<nav class='navbar'><div class='nav-container'><div class='logo'>Nadair Tours</div>
              <ul class='nav-menu'>
                <li><a href='HomePage.php'>Home</a></li>
                <li><a href='tours.php'>Tours</a></li>
                <li><a href='cart.php'>Cart</a></li>
              </ul></div></nav>";

        echo "<div class='container'>
                <div class='success-bubble'>
                    <h2>Booking Confirmed!</h2>
                    <p>Thank you, <strong>" . htmlspecialchars($name) . "</strong>. 
                    Your tours have been successfully booked.</p>
                </div>
                <a href='tours.php' class='btn'>Back to Tours</a>
              </div>";

        echo "<footer><p>&copy; " . date('Y') . " Nadair Tours.</p></footer>
              </body></html>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Booking - Nadair Tours</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">Nadair Tours</div>
        <ul class="nav-menu">
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a class="active" href="cart.php">Cart</a></li>
        </ul>
    </div>
</nav>

<div class="container booking-container">
    <h2>Complete Your Booking</h2>
    <h3>Your Selected Tours</h3>

    <table class="cart-table booking-summary">
        <thead>
        <tr>
            <th>Tour</th>
            <th>Date</th>
            <th>Participants</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $grandTotal = 0;
        foreach ($cart as $item):
            $id = $item['TourID'];
            $title = $item['Title'];
            $price = $item['Price'];
            $participants = isset($item['quantity']) ? $item['quantity'] : 1;
            $subtotal = $price * $participants;
            $grandTotal += $subtotal;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($title); ?></td>
                <td><?php echo htmlspecialchars($booking_dates[$id]); ?></td>
                <td><?php echo $participants; ?></td>
                <td>£<?php echo number_format($price, 2); ?></td>
                <td>£<?php echo number_format($subtotal, 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Grand Total:</strong> £<?php echo number_format($grandTotal, 2); ?></p>
    <hr>

    <?php if (!empty($errors)): ?>
        <div class="error-bubble">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="booking-form">

        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

        <label>Special Requests (optional):</label>
        <textarea name="requests"><?php echo htmlspecialchars($requests); ?></textarea>

        <button type="submit" class="btn proceed">Confirm Booking</button>
    </form>
</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Nadair Tours</p>
</footer>
</body>
</html>
