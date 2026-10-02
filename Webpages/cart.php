<?php
session_start();
include 'tours_db.php';

// Ensure cart exists
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error_message = "";

// Handle quantity updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if (isset($_SESSION['cart'][$id])) {
        if ($_GET['action'] === 'increase') {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } elseif ($_GET['action'] === 'decrease' && $_SESSION['cart'][$id]['quantity'] > 1) {
            $_SESSION['cart'][$id]['quantity'] -= 1;
        } elseif ($_GET['action'] === 'remove') {
            unset($_SESSION['cart'][$id]);
        }
    }

    // Sync booking cart
    $_SESSION['booking_cart'] = $_SESSION['cart'];
    header("Location: cart.php");
    exit();
}

// When user clicks Proceed to Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dates = isset($_POST['booking_date']) ? $_POST['booking_date'] : [];
    $cleanDates = [];

    // Validate: all tours must have dates
    foreach ($_SESSION['cart'] as $id => $item) {
        if (!isset($dates[$id]) || empty($dates[$id])) {
            $error_message = "Please select a date for all tours.";
            break;
        }
        $cleanDates[$id] = $dates[$id];
    }

    // Validate spacing – no tours within 3 days of each other
    if (!$error_message) {
        $sorted = $cleanDates;
        asort($sorted);
        $dateList = array_values($sorted);

        for ($i = 0; $i < count($dateList) - 1; $i++) {
            $d1 = strtotime($dateList[$i]);
            $d2 = strtotime($dateList[$i + 1]);

            if (abs($d2 - $d1) < (3 * 86400)) {
                $error_message = "Tours must be at least 3 days apart.";
                break;
            }
        }
    }

    // If valid, send to process_booking
    if (!$error_message) {
        $_SESSION['booking_dates'] = $cleanDates;
        header("Location: process_booking.php");
        exit();
    }

    // Save for repopulation
    $_SESSION['temp_dates'] = $cleanDates;
}

// Restore previously saved dates if validation failed
$saved_dates = isset($_SESSION['temp_dates']) ? $_SESSION['temp_dates'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nàdair Tours</title>
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

<div class="container">
    <h2>Your Basket</h2>

    <?php if (!empty($error_message)): ?>
        <p style="color:red; font-weight:bold; text-align:center;">
             <?php echo htmlspecialchars($error_message); ?>
        </p>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>

        <p>Your basket is empty. <a class="btn" href="tours.php">Browse Tours</a></p>

    <?php else: ?>

        <form action="cart.php" method="POST">

            <table class="cart-table">
                <thead>
                <tr>
                    <th>Tour</th>
                    <th>Price</th>
                    <th>Participants</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>

                <?php
                $grandTotal = 0;
                foreach ($_SESSION['cart'] as $id => $item):

                    // All fields
                    $participants = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                    $title = $item['Title'];
                    $price = (float)$item['Price'];
                    $thumb = $item['Thumbnail Link'];

                    $subtotal = $price * $participants;
                    $grandTotal += $subtotal;
                    ?>
                    <tr>
                        <td>
                            <?php if ($thumb != ''): ?>
                                <img src="../<?php echo htmlspecialchars($thumb); ?>" width="60" height="60">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($title); ?>
                        </td>

                        <td>£<?php echo number_format($price, 2); ?></td>

                        <td>
                            <a href="cart.php?action=decrease&id=<?php echo $id; ?>" class="qty-btn">-</a>
                            <span><?php echo $participants; ?></span>
                            <a href="cart.php?action=increase&id=<?php echo $id; ?>" class="qty-btn">+</a>
                        </td>

                        <td>£<?php echo number_format($subtotal, 2); ?></td>

                        <td>
                            <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="btn danger">Remove</a>
                        </td>
                    </tr>

                    <!-- Date field -->
                    <tr>
                        <td colspan="5" style="padding:10px 0; text-align:center;">
                            <label><strong>Select Booking Date:</strong></label><br>
                            <input type="date"
                                   name="booking_date[<?php echo $id; ?>]"
                                   required
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo isset($saved_dates[$id]) ? htmlspecialchars($saved_dates[$id]) : ''; ?>"
                                   style="padding:8px; margin-top:5px; border-radius:6px; border:1px solid #ccc;">
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

            <p><strong>Grand Total:</strong> £<?php echo number_format($grandTotal, 2); ?></p>

            <button type="submit" class="btn proceed">Proceed to Booking</button>

        </form>

    <?php endif; ?>

</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Nadair Tours</p>
</footer>

</body>
</html>
