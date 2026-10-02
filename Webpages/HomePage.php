<?php
require_once "tours_db.php";

// Ensure $conn is accessible
if (!isset($conn) || $conn === null) {
    die("Database connection not established.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Eco-friendly Scottish tours and sustainable travel experiences.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nàdair Tours</title>
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

<!-- Navigation -->
<header class="navbar">
    <div class="nav-container">
        <h1 class="logo">Nàdair Tours</h1>
        <ul class="nav-menu">
            <li><a href="HomePage.php" class="active">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin.php">Admin</a></li>
        </ul>
    </div>
</header>

<main>
    <!-- Hero Section -->
    <section style="background-color: #2e462e; color: white; text-align: center; padding: 80px 20px;">
        <h2 style="font-size: 2.5rem; margin-bottom: 10px;">Eco-Conscious Scottish Adventures</h2>
        <p style="font-size: 1.1rem; margin-bottom: 25px;">
            Experience Scotland's natural beauty while supporting sustainable tourism and local communities
        </p>
        <a href="tours.php" class="btn">Explore Tours</a>
    </section>

    <!-- Sustainability Section -->
    <section class="container" style="text-align: center; margin-top: 60px;">
        <h2 style="color: #2e462e;">Our Commitment to Sustainability</h2>
        <p style="margin-top: 10px;">
            Nàdair Tours is dedicated to promoting responsible travel that aligns with UN Sustainable Development Goals:
        </p>

        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px; margin-top: 30px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 220px;">
                <h3 style="color: #2e462e;">Goal 11</h3>
                <p>Sustainable Cities & Communities</p>
            </div>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 220px;">
                <h3 style="color: #2e462e;">Goal 12</h3>
                <p>Responsible Consumption</p>
            </div>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 220px;">
                <h3 style="color: #2e462e;">Goal 15</h3>
                <p>Life on Land</p>
            </div>
        </div>
    </section>

    <!-- Featured Tours Section -->
    <section class="container" style="text-align: center; margin-top: 60px;">
        <h2 style="color: #2e462e;">Featured Tours</h2>
        <div class="tours-grid">
            <?php
            $result = $conn->query("SELECT TourID, Title, Price, `Image links`, `Image links 2`, `Thumbnail Link` FROM tour LIMIT 3");

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="tour-card">

                        <div class="slideshow-container" id="slide-<?php echo $row['TourID']; ?>">
                            <img src="../<?php echo htmlspecialchars($row['Image links']); ?>" class="active" alt="">
                            <img src="../<?php echo htmlspecialchars($row['Image links 2']); ?>" alt="">
                        </div>

                        <script>
                            startSlideshow("slide-<?php echo $row['TourID']; ?>");
                        </script>

                        <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                        <p class="price">£<?php echo number_format($row['Price'], 2); ?></p>
                        <a href="tour_details.php?id=<?php echo $row['TourID']; ?>" class="btn secondary">View Details</a>
                    </div>

                    <?php
                }
            } else {
                echo '<p>No tours available at the moment.</p>';
            }
            ?>
        </div>
    </section>
</main>

<!-- Footer -->
<footer>
    <p>© 2025 Nadair Tours</p>
</footer>
</body>
</html>
