<?php
session_start();
require_once 'tours_db.php';
require_once __DIR__ . '/orders_db.php';

// Make sure $conn always exists before any logic
if (!isset($conn)) {
    if (isset($tours_conn)) $conn = $tours_conn;
    if (isset($orders_conn)) $conn = $orders_conn;
}

// Make sure only admin can access
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php?error=Please+login+first");
    exit;
}

// Handle deleting an order
if (isset($_GET['delete_order'])) {
    $id = (int)$_GET['delete_order'];
    $conn->query("DELETE FROM orders WHERE OrderID = $id");

    $_SESSION['message'] = "Order deleted successfully.";
    header("Location: admin_dashboard.php?tab=orders");
    exit;
}

// Handle deleting a tour
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tour WHERE TourID = $id");
    $_SESSION['message'] = "Tour deleted successfully.";
    header("Location: admin_dashboard.php?tab=tours");
    exit;
}

// Handle adding a new tour (with image upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tour'])) {

    $name       = trim($_POST['name']);
    $desc       = trim($_POST['description']);
    $itinerary  = trim($_POST['itinerary']);
    $price      = (float)$_POST['price'];
    $duration   = trim($_POST['duration']);
    $location   = trim($_POST['location']);

    // Uploaded files
    $image1     = "";
    $image2     = "";
    $thumbnail  = "";

    // Handle Image 1
    if (!empty($_FILES['image1']['name'])) {
        $image1 = "images/" . basename($_FILES['image1']['name']);
        move_uploaded_file($_FILES['image1']['tmp_name'], "../" . $image1);
    }

    // Handle Image 2
    if (!empty($_FILES['image2']['name'])) {
        $image2 = "images/" . basename($_FILES['image2']['name']);
        move_uploaded_file($_FILES['image2']['tmp_name'], "../" . $image2);
    }

    // Handle Thumbnail
    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbnail = "images/" . basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "../" . $thumbnail);
    }

    $stmt = $conn->prepare("
        INSERT INTO tour 
        (Title, Descriptions, Itinerary, Price, `Image links`, `Image links 2`, `Thumbnail Link`, Duration, Location)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssdsssss",
            $name,
            $desc,
            $itinerary,
            $price,
            $image1,
            $image2,
            $thumbnail,
            $duration,
            $location
    );

    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "New tour added successfully!";
    header("Location: admin_dashboard.php?tab=tours");
    exit;
}

// Handle updating an existing tour (text fields only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tour'])) {

    $id         = (int)$_POST['id'];
    $name       = trim($_POST['name']);
    $desc       = trim($_POST['description']);
    $itinerary  = trim($_POST['itinerary']);
    $price      = (float)$_POST['price'];
    $duration   = trim($_POST['duration']);
    $location   = trim($_POST['location']);

    $image1     = trim($_POST['image1_existing']);
    $image2     = trim($_POST['image2_existing']);
    $thumbnail  = trim($_POST['thumbnail_existing']);

    // Replace Image 1
    if (!empty($_FILES['image1']['name'])) {
        $image1 = "images/" . basename($_FILES['image1']['name']);
        move_uploaded_file($_FILES['image1']['tmp_name'], "../" . $image1);
    }

    // Replace Image 2
    if (!empty($_FILES['image2']['name'])) {
        $image2 = "images/" . basename($_FILES['image2']['name']);
        move_uploaded_file($_FILES['image2']['tmp_name'], "../" . $image2);
    }

    // Replace Thumbnail
    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbnail = "images/" . basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "../" . $thumbnail);
    }

    $stmt = $conn->prepare("
        UPDATE tour 
        SET Title=?, Descriptions=?, Itinerary=?, Price=?, `Image links`=?, `Image links 2`=?, `Thumbnail Link`=?, Duration=?, Location=?
        WHERE TourID=?
    ");

    $stmt->bind_param("sssdsssssi",
            $name,
            $desc,
            $itinerary,
            $price,
            $image1,
            $image2,
            $thumbnail,
            $duration,
            $location,
            $id
    );

    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Tour updated successfully!";
    header("Location: admin_dashboard.php?tab=tours");
    exit;
}


// Determine which section to show
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Nàdair Tours</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this tour?');
        }
        function confirmUpdate() {
            return confirm('Are you sure you want to update this tour?');
        }

        // Toggle an accordion card open/closed
        function toggleCard(id) {
            var body = document.getElementById('tour-body-' + id);
            if (!body) return;

            if (body.style.display === 'none' || body.style.display === '') {
                body.style.display = 'block';
            } else {
                body.style.display = 'none';
            }
        }
    </script>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">Nàdair Tours Admin</div>
        <ul class="nav-menu">
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="tours.php">Tours</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin_dashboard.php" class="active">Admin</a></li>
            <li><a href="logout.php" style="color:#ffb3b3;">Logout</a></li>
            <li><a href="admin_change_password.php" class="btn">Admin Settings</a></li>
        </ul>
    </div>
</nav>

<section class="container" style="padding-top:30px;">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?></h2>
    <div style="margin:20px 0;">
        <a href="?tab=orders" class="btn <?php echo $tab=='orders'?'active':''; ?>">View Orders</a>
        <a href="?tab=tours" class="btn <?php echo $tab=='tours'?'active':''; ?>">Manage Tours</a>
    </div>

    <!-- Success message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-bubble">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?php if ($tab == 'orders'): ?>
        <h3>Customer Orders</h3>
        <?php
        $orders = $orders_conn->query("SELECT * FROM orders ORDER BY OrderID DESC");
        if ($orders && $orders->num_rows > 0): ?>
            <table class="cart-table">
                <tr>
                    <th>Order ID</th>
                    <th>Tour ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Participants</th>
                    <th>Date</th>
                    <th>Special Requests</th>
                </tr>
                <?php while ($row = $orders->fetch_assoc()): ?>
                    <tr>

                        <td><?php echo $row['OrderID']; ?></td>
                        <td><?php echo $row['TourID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td><?php echo $row['Participants']; ?></td>
                        <td><?php echo htmlspecialchars($row['Booking Date']); ?></td>
                        <td><?php echo htmlspecialchars($row['Special Requests']); ?></td>
                        <td>
                            <a href="admin_dashboard.php?tab=orders&delete_order=<?php echo $row['OrderID']; ?>"
                               class="btn danger"
                               onclick="return confirm('Delete this order?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>

    <?php elseif ($tab == 'tours'): ?>
        <h3>Manage Tours</h3>

        <!-- Add Tour Form -->
        <form method="post" enctype="multipart/form-data" style="margin-bottom:30px;">
            <input type="hidden" name="add_tour" value="1">
            <h4>Add New Tour</h4>

            <!-- Tour Name -->
            <input type="text" name="name" placeholder="Tour Name" required style="width:100%; padding:8px; margin:5px 0;">

            <!-- Description -->
            <textarea name="description" rows="3" placeholder="Description" style="width:100%; padding:8px; margin:5px 0;"></textarea>

            <!-- Thumbnail file -->
            <label>Upload Tour Thumbnail:</label>
            <input type="file" name="thumbnail" accept="image/*" required style="width:100%; padding:8px; margin:5px 0;">

            <!-- Main image file -->
            <label>Upload Tour Image:</label>
            <input type="file" name="image1" accept="image/*" required style="width:100%; padding:8px; margin:5px 0;">

            <label>Upload Second Tour Image:</label>
            <input type="file" name="image2" accept="image/*" required style="width:100%; padding:8px; margin:5px 0;">

            <!-- Duration -->
            <input type="text" name="duration" placeholder="Duration (e.g. 3 days)" style="width:100%; padding:8px; margin:5px 0;">

            <!-- Location -->
            <input type="text" name="location" placeholder="Location" style="width:100%; padding:8px; margin:5px 0;">

            <!-- Itinerary -->
            <textarea name="itinerary" rows="3" placeholder="Itinerary" style="width:100%; padding:8px; margin:5px 0;"></textarea>

            <!-- Price -->
            <input type="number" name="price" placeholder="Price (£)" required step="0.01" style="width:100%; padding:8px; margin:5px 0;">

            <button type="submit" class="btn">Add Tour</button>
        </form>

        <?php
        // Pagination: 3 tours per page
        $limit = 3;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $tours = $conn->query("SELECT * FROM tour ORDER BY TourID ASC LIMIT $offset, $limit");

        $total_result = $conn->query("SELECT COUNT(*) AS total FROM tour");
        $total_row = $total_result ? $total_result->fetch_assoc() : ['total' => 0];
        $total_pages = max(1, ceil($total_row['total'] / $limit));
        ?>

        <?php if ($tours && $tours->num_rows > 0): ?>
            <?php while ($t = $tours->fetch_assoc()): ?>
                <div class="tour-card-admin">
                    <div class="tour-header" onclick="toggleCard(<?php echo $t['TourID']; ?>)">
                        <span><?php echo htmlspecialchars($t['Title']); ?></span>
                        <span>£<?php echo number_format($t['Price'], 2); ?></span>
                        <span class="arrow">▼</span>
                    </div>

                    <div class="tour-body" id="tour-body-<?php echo $t['TourID']; ?>" style="display:none;">
                        <form method="post" class="admin-form">
                            <input type="hidden" name="update_tour" value="1">
                            <input type="hidden" name="id" value="<?php echo $t['TourID']; ?>">

                            <div class="field-group">
                                <label>Title</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($t['Title']); ?>" required>
                            </div>

                            <div class="field-group">
                                <label>Description</label>
                                <textarea name="description" rows="3" required><?php echo htmlspecialchars($t['Descriptions']); ?></textarea>
                            </div>

                            <div class="field-group">
                                <label>Image Link</label>
                                <input type="text" name="image1_existing" value="<?php echo htmlspecialchars($t['Image links']); ?>" required>
                            </div>

                            <div class="field-group">
                                <label>Second Image Link</label>
                                <input type="text" name="image2_existing" value="<?php echo htmlspecialchars($t['Image links 2']); ?>">
                            </div>

                            <div class="field-group">
                                <label>Thumbnail Link</label>
                                <input type="text" name="thumbnail_existing" value="<?php echo htmlspecialchars($t['Thumbnail Link']); ?>" required>
                            </div>

                            <div class="field-group">
                                <label>Duration</label>
                                <input type="text" name="duration" value="<?php echo htmlspecialchars($t['Duration']); ?>" required>
                            </div>

                            <div class="field-group">
                                <label>Location</label>
                                <input type="text" name="location" value="<?php echo htmlspecialchars($t['Location']); ?>" required>
                            </div>

                            <div class="field-group">
                                <label>Itinerary</label>
                                <textarea name="itinerary" rows="3"><?php echo htmlspecialchars($t['Itinerary']); ?></textarea>
                            </div>

                            <div class="field-group">
                                <label>Price (£)</label>
                                <input type="number" name="price" value="<?php echo htmlspecialchars($t['Price']); ?>" step="0.01" required>
                            </div>

                            <div class="admin-actions">
                                <button type="submit" class="btn outline" onclick="return confirmUpdate();">
                                    Update
                                </button>
                                <a href="?tab=tours&delete=<?php echo $t['TourID']; ?>"
                                   class="btn danger"
                                   onclick="return confirmDelete();">
                                    Delete
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Pagination -->
            <div class="admin-pagination">
                <?php if ($page > 1): ?>
                    <a href="admin_dashboard.php?tab=tours&page=<?php echo $page - 1; ?>">&laquo; Prev</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Prev</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="admin_dashboard.php?tab=tours&page=<?php echo $i; ?>"
                       class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="admin_dashboard.php?tab=tours&page=<?php echo $page + 1; ?>">Next &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Next &raquo;</span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>No tours found.</p>
        <?php endif; ?>
    <?php endif; ?>

</section>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Nàdair Tours</p>
</footer>

</body>
</html>
