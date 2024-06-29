<?php
$servername = "172.27.98.229"; // Replace with actual IP address or hostname
$username = "appuser";
$password = "@Bagaskara123";
$database = "online_shop";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$delete_message = '';
$upload_message = '';

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_product_id'])) {
        // Handle product deletion
        $product_id = $_POST['delete_product_id'];

        // Fetch image path before deletion
        $select_sql = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $select_sql->bind_param('i', $product_id);
        $select_sql->execute();
        $select_sql->bind_result($image_path);
        $select_sql->fetch();
        $select_sql->close();

        // Delete product from database
        $delete_sql = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete_sql->bind_param('i', $product_id);

        if ($delete_sql->execute()) {
            $delete_message = '<div class="alert alert-success" role="alert">Product deleted successfully</div>';

            // Delete image file
            $full_image_path = "/var/www/html/online_shop/public/" . $image_path;
            if (file_exists($full_image_path)) {
                if (unlink($full_image_path)) {
                    $delete_message .= '<div class="alert alert-success" role="alert">Image deleted successfully</div>';
                } else {
                    $delete_message .= '<div class="alert alert-danger" role="alert">Error deleting image file</div>';
                }
            } else {
                $delete_message .= '<div class="alert alert-warning" role="alert">Image file not found</div>';
            }
        } else {
            $delete_message = '<div class="alert alert-danger" role="alert">Error deleting product: ' . $delete_sql->error . '</div>';
        }

        $delete_sql->close();
        header("Location: admin_dashboard.php?delete_success=1");
        exit();
    } elseif (isset($_FILES['image'])) {
        // Handle product addition with image upload
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];

        $target_dir = "/var/www/html/online_shop/public/photo_product/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        // Validate file upload
        if ($_FILES["image"]["size"] > 500000) {
            $upload_message = '<div class="alert alert-danger" role="alert">Sorry, your file is too large.</div>';
        } elseif (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'png', 'gif'))) {
            $upload_message = '<div class="alert alert-danger" role="alert">Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>';
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            chmod($target_file, 0644);
            $image_relative_path = "photo_product/" . basename($_FILES["image"]["name"]);

            // Prepare the SQL statement to insert the new product
            $insert_sql = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $insert_sql->bind_param('ssds', $name, $description, $price, $image_relative_path);

            if ($insert_sql->execute()) {
                $upload_message = '<div class="alert alert-success" role="alert">Product added successfully</div>';
            } else {
                $upload_message = '<div class="alert alert-danger" role="alert">Error adding product: ' . $insert_sql->error . '</div>';
            }

            $insert_sql->close();
            header("Location: admin_dashboard.php?upload_success=1");
            exit();
        } else {
            echo '<pre>';
            echo 'Debugging Info Before Move:';
            print_r($_FILES);
            echo 'Target Directory: ' . $target_dir . PHP_EOL;
            echo 'Target File: ' . $target_file . PHP_EOL;
            echo '</pre>';
            die('File upload failed - check permissions and configuration.');
        }
    }
}

// Fetch products from database
$sql_fetch_products = "SELECT * FROM products";
$result = $conn->query($sql_fetch_products);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Admin Dashboard</h1>

        <!-- Product Addition Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add Product
            </div>
            <div class="card-body">
                <?php if (!empty($upload_message)) echo $upload_message; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="text" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Image:</label>
                        <input type="file" class="form-control-file" id="image" name="image" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Product Display Section -->
        <h2 class="mb-4">Manage Products</h2>
        <?php if (!empty($delete_message)) echo $delete_message; ?>
        <div class="card-columns">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="card">';
                    echo '<img src="/online_shop/public/' . $row['image'] . '" class="card-img-top" alt="Product Image">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row['name'] . '</h5>';
                    echo '<p class="card-text">' . $row['description'] . '</p>';
                    echo '<p class="card-text">Price: ' . $row['price'] . '</p>';
                    echo '<form method="POST" onsubmit="return confirm(\'Are you sure you want to delete this product?\');">';
                    echo '<input type="hidden" name="delete_product_id" value="' . $row['id'] . '">';
                    echo '<button type="submit" class="btn btn-danger">Delete</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
