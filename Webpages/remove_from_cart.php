<?php
session_start();

// Handle removing or decreasing items from cart
$tour_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$decrement = isset($_GET['decrement']) ? (int)$_GET['decrement'] : 0;

if ($tour_id > 0 && isset($_SESSION['cart'][(string)$tour_id])) {
    $key = (string)$tour_id;

    if ($decrement) {
        $_SESSION['cart'][$key]['participants'] -= 1;
        if ($_SESSION['cart'][$key]['participants'] <= 0) {
            unset($_SESSION['cart'][$key]);
        }
    } else {
        unset($_SESSION['cart'][$key]);
    }
}

// Clean up if empty
if (isset($_SESSION['cart']) && count($_SESSION['cart']) === 0) {
    unset($_SESSION['cart']);
}

$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
header("Location: " . $back);
exit;
