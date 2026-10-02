<?php
session_start();

// Include your database connection file
require_once __DIR__ . '/tours_db.php';

// Ensure $conn exists even if tours_db.php doesn't define it
if (!isset($conn) || !$conn) {
    $servername = "devweb2025.cis.strath.ac.uk";
    $username   = "gfb23140";
    $password   = "yoo5Ohtaepha";
    $dbname     = "gfb23140";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
}

// Pagination setup
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Build search + filters
$conditions = [];
$search = "";

// Search
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "Title LIKE '%$search%'";
}

// Price Filter
if (!empty($_GET['price'])) {
    if ($_GET['price'] == "low") $conditions[] = "Price < 50";
    if ($_GET['price'] == "mid") $conditions[] = "Price BETWEEN 50 AND 150";
    if ($_GET['price'] == "high") $conditions[] = "Price > 150";
}

// Duration Filter
if (!empty($_GET['duration'])) {
    $duration = mysqli_real_escape_string($conn, $_GET['duration']);
    $conditions[] = "Duration LIKE '%$duration%'";
}

// Location Filter
if (!empty($_GET['location'])) {
    $location = mysqli_real_escape_string($conn, $_GET['location']);
    $conditions[] = "Location = '$location'";
}

// Build WHERE clause
$whereSQL = "";
if (!empty($conditions)) {
    $whereSQL = "WHERE " . implode(" AND ", $conditions);
}

// Get total tours
$total_result = $conn->query("SELECT COUNT(*) AS total FROM tour $whereSQL");
$total_row = $total_result ? $total_result->fetch_assoc() : ['total' => 0];
$total_pages = ceil($total_row['total'] / $limit);

// Fetch tours
$result = $conn->query("SELECT * FROM tour $whereSQL LIMIT $start, $limit");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tours - Nàdair Tours</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">

    <!-- Switch Between images for Tours -->
    <script>
        function startSlideshow(id) {
            const box = document.getElementById(id);
            if (!box) return;

            const images = box.querySelectorAll("img");
            if (images.length < 2) return;

            let index = 0;
            images[0].classList.add("active");

            setInterval(() => {
                images[index].classList.remove("active");
                index = (index + 1) % images.length;
                images[index].classList.add("active");
            }, 15000);
        }
    </script>
</head>

<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">Nadair Tours</div>
        <ul class="nav-menu">
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="tours.php" class="active">Tours</a></li>
            <li><a href="cart.php">Cart</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 style="margin:2rem 0 1rem;">Our Eco-Friendly Tours</h1>

    <!-- Messages -->
    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<p class='success' style='color:green;'>{$_SESSION['success_message']}</p>";
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo "<p class='error' style='color:red;'>{$_SESSION['error_message']}</p>";
        unset($_SESSION['error_message']);
    }
    ?>

    <!-- Search bar -->
    <form method="GET" action="tours.php" style="margin-bottom:20px; display:flex; gap:10px; align-items:center;">
        <input type="text" name="search" placeholder="Search tours..."
               value="<?php echo htmlspecialchars($search); ?>"
               style="padding:10px; width:250px; border-radius:6px; border:1px solid #ccc;">
        <button type="submit" class="btn">Search</button>

        <?php if (!empty($search)): ?>
            <a href="tours.php" class="btn secondary" style="background:#aaa; color:white;">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Filter toggle -->
    <button onclick="toggleFilters()" class="btn" style="margin-bottom:10px; background:#2e462e; color:white;">Filters ▼</button>

    <script>
        function toggleFilters() {
            const box = document.getElementById("filter-box");
            box.style.display = (box.style.display === "block") ? "none" : "block";
        }
    </script>

    <!-- Filter box -->
    <div id="filter-box" style="display:none; padding:15px; border:1px solid #ccc; border-radius:8px; background:#f9f9f9; margin-bottom:20px;">
        <form method="GET" action="tours.php">

            <input type="hidden" name="search" value="<?php echo $search; ?>">

            <div style="display:flex; gap:20px; flex-wrap:wrap;">

                <!-- Price -->
                <div>
                    <label><strong>Price:</strong></label><br>
                    <select name="price" style="padding:8px;">
                        <option value="">Any</option>
                        <option value="low"  <?php if(@$_GET['price']=="low") echo "selected"; ?>>Under £50</option>
                        <option value="mid"  <?php if(@$_GET['price']=="mid") echo "selected"; ?>>£50 – £150</option>
                        <option value="high" <?php if(@$_GET['price']=="high") echo "selected"; ?>>Over £150</option>
                    </select>
                </div>

                <!-- Duration -->
                <div>
                    <label><strong>Duration:</strong></label><br>
                    <select name="duration" style="padding:8px;">
                        <option value="">Any</option>
                        <option value="1" <?php if(@$_GET['duration']=="1") echo "selected"; ?>>1 Day</option>
                        <option value="2" <?php if(@$_GET['duration']=="2") echo "selected"; ?>>2 Days</option>
                        <option value="3" <?php if(@$_GET['duration']=="3") echo "selected"; ?>>3 Days</option>
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label><strong>Location:</strong></label><br>
                    <select name="location" style="padding:8px;">
                        <option value="">Any</option>
                        <?php
                        $locs = $conn->query("SELECT DISTINCT Location FROM tour ORDER BY Location");
                        while ($loc = $locs->fetch_assoc()) {
                            $l = htmlspecialchars($loc['Location']);
                            echo "<option value='$l'" . (@$_GET['location']==$l ? " selected" : "") . ">$l</option>";
                        }
                        ?>
                    </select>
                </div>

            </div>

            <button type="submit" class="btn" style="margin-top:15px;">Apply Filters</button>
            <a href="tours.php" class="btn secondary" style="margin-top:15px; background:#aaa; color:white;">Clear Filters</a>

        </form>
    </div>

    <!-- Tours List -->
    <div class="tours-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="tour-card">

                    <!-- ⭐ Slideshow -->
                    <div class="slideshow-container" id="slide-<?php echo $row['TourID']; ?>">
                        <img src="../<?php echo htmlspecialchars($row['Image links']); ?>" class="active" alt="">
                        <?php if (!empty($row['Image links 2'])): ?>
                            <img src="../<?php echo htmlspecialchars($row['Image links 2']); ?>" alt="">
                        <?php endif; ?>
                    </div>

                    <!-- Start slideshow AFTER page loads -->
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            startSlideshow("slide-<?php echo $row['TourID']; ?>");
                        });
                    </script>

                    <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                    <p class="price">£<?php echo number_format($row['Price'], 2); ?></p>

                    <a href="add_to_cart.php?id=<?php echo $row['TourID']; ?>&redirect=cart" class="btn">Add to Cart</a>
                    <a href="tour_details.php?id=<?php echo $row['TourID']; ?>" class="btn secondary">View Details</a>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No tours found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="tours.php?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="tours.php?page=<?php echo $i; ?>" class="<?php echo $page==$i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="tours.php?page=<?php echo $page + 1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

</div>

<footer>
    <p>© <?php echo date('Y'); ?> Nadair Tours. All rights reserved.</p>
</footer>

<?php $conn->close(); ?>

</body>
</html>
