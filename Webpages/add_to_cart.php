<?php
session_start();

// Include the database connection
require_once __DIR__ . '/tours_db.php';

// Use the tours DB connection correctly
if (isset($tours_conn)) {
    $conn = $tours_conn;
}

// If still no $conn, stop the script safely
if (!isset($conn) || !$conn) {
    die("Database connection missing.");
}


// Read Tour ID
// Accept multiple possible parameter names
$tourID = null;

// Get Values
if (isset($_GET['id']))         $tourID = (int)$_GET['id'];
if (isset($_GET['tour']))       $tourID = (int)$_GET['tour'];
if (isset($_GET['tourID']))     $tourID = (int)$_GET['tourID'];

// Post Values
if (isset($_POST['id']))        $tourID = (int)$_POST['id'];
if (isset($_POST['tour']))      $tourID = (int)$_POST['tour'];
if (isset($_POST['tourID']))    $tourID = (int)$_POST['tourID'];
if (isset($_POST['TourID']))    $tourID = (int)$_POST['TourID']; // From tour_details form

// Validate
if (!$tourID || $tourID <= 0) {
    $_SESSION['error_message'] = "Invalid tour selected.";
    header("Location: tours.php");
    exit;
}

// Fetch Your Data

$stmt = $conn->prepare("SELECT TourID, Title, Price, `Thumbnail Link`, `Image links`
                        FROM tour WHERE TourID = ?");
$stmt->bind_param("i", $tourID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Tour not found.";
    header("Location: tours.php");
    exit;
}

$tour = $result->fetch_assoc();
$stmt->close();

// Add To Cart

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$tourID])) {
    // Increase quantity
    $_SESSION['cart'][$tourID]['quantity'] += 1;
} else {
    // Create new entry
    $_SESSION['cart'][$tourID] = [
        'TourID'         => $tour['TourID'],
        'Title'          => $tour['Title'],
        'Price'          => $tour['Price'],
        'Thumbnail Link' => $tour['Thumbnail Link'],
        'Image links'    => $tour['Image links'],
        'quantity'       => 1
    ];
}

$_SESSION['success_message'] = "{$tour['Title']} added to cart successfully.";

// REDIRECT

if (isset($_GET['redirect']) && $_GET['redirect'] === 'cart') {
    header("Location: cart.php");
} else {
    header("Location: tours.php");
}
exit;
?>
