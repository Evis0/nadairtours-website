<?php
require_once 'tours_db.php';

// Ensure $conn is accessible
if (!isset($conn) || $conn === null) {
    die("Database connection not established.");
}

if (isset($_GET['id'])) {
    $tourID = intval($_GET['id']);
    $sql = "SELECT * FROM tour WHERE TourID = $tourID";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $tour = mysqli_fetch_assoc($result);
    } else {
        echo "<p style='text-align:center;'>Tour not found.</p>";
        exit;
    }
} else {
    echo "<p style='text-align:center;'>No tour selected.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Eco-friendly Scottish tours and sustainable travel experiences.">
    <title><?php echo htmlspecialchars($tour['Title']); ?> - Nàdair Tours</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">

    <!-- Switch Between images for Tours -->
    <script>
        function startSlideshow(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const imgs = container.querySelectorAll("img");
            if (imgs.length < 2) return; // only slideshow if 2+ images

            let index = 0;

            setInterval(() => {
                imgs[index].style.opacity = 0;
                index = (index + 1) % imgs.length;
                imgs[index].style.opacity = 1;
            }, 15000); // 15 seconds
        }
    </script>

</head>
<body>

<header class="navbar">
    <div class="nav-container">
        <div class="logo">Nàdair Tours</div>
        <ul class="nav-menu">
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin.php">Admin</a></li>
        </ul>
    </div>
</header>

<main class="tour-details-wrapper">
    <!-- Tour Image -->
    <div class="slideshow-container" id="tour-slide">
        <img src="../<?php echo htmlspecialchars($tour['Image links']); ?>" class="active" alt="">
        <img src="../<?php echo htmlspecialchars($tour['Image links 2']); ?>" alt="">
    </div>

    <script>
        startSlideshow("tour-slide");
    </script>


    <!-- Title & Info -->
    <div class="tour-info-header">
        <h1><?php echo htmlspecialchars($tour['Title']); ?></h1>
        <div class="tour-info">
            <p><strong>Location:</strong> <?php echo htmlspecialchars($tour['Location']); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($tour['Duration']); ?></p>
            <p><strong>Price:</strong> £<?php echo number_format($tour['Price'], 2); ?></p>
        </div>
    </div>

    <!-- About Section -->
    <section class="about-section">
        <h2>About this Tour</h2>
        <p><?php echo nl2br(htmlspecialchars($tour['Descriptions'])); ?></p>
        <p><?php echo nl2br(htmlspecialchars($tour['Itinerary'])); ?></p>
    </section>

    <!-- Add to Cart Button -->
    <div class="tour-actions">
        <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="TourID" value="<?php echo $tour['TourID']; ?>">
            <button type="submit" class="btn">Add to Cart</button>
        </form>
    </div>
</main>

<footer>
    <p>© 2025 Nàdair Tours</p>
</footer>
</body>
</html>
