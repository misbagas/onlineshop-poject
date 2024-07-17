<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "172.27.98.229"; // Replace with your actual database server IP or hostname
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
$edit_message = '';

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
        $stock = $_POST['stock'];
        $categories = $_POST['categories'];
        $tags = $_POST['tags'];
        $shipping_destination = $_POST['shipping_destination'];

        $target_dir = "/var/www/html/online_shop/public/photo_product/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        // Check and set permissions for the upload directory
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        chmod($target_dir, 0755);

        // Validate file upload
        if ($_FILES["image"]["size"] > 500000) {
            $upload_message = '<div class="alert alert-danger" role="alert">Sorry, your file is too large.</div>';
        } elseif (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'png', 'gif'))) {
            $upload_message = '<div class="alert alert-danger" role="alert">Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>';
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            chmod($target_file, 0644);
            $image_relative_path = "photo_product/" . basename($_FILES["image"]["name"]);

            // Prepare the SQL statement to insert the new product
            $insert_sql = $conn->prepare("INSERT INTO products (name, description, price, image, stock, categories, tags, shipping_destination) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_sql->bind_param('ssdsssss', $name, $description, $price, $image_relative_path, $stock, $categories, $tags, $shipping_destination);

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
    } elseif (isset($_POST['edit_product_id'])) {
        // Handle product editing
        $product_id = $_POST['edit_product_id'];
        $name = $_POST['edit_name'];
        $description = $_POST['edit_description'];
        $price = $_POST['edit_price'];
        $stock = $_POST['edit_stock'];
        $categories = $_POST['edit_categories'];
        $tags = $_POST['edit_tags'];
        $shipping_destination = $_POST['shipping_destination'];

        // Prepare update query
        $update_sql = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, categories = ?, tags = ?, shipping_destination = ? WHERE id = ?");
        $update_sql->bind_param('ssdisssi', $name, $description, $price, $stock, $categories, $tags, $shipping_destination, $product_id);

        if ($update_sql->execute()) {
            $edit_message = '<div class="alert alert-success" role="alert">Product updated successfully</div>';
        } else {
            $edit_message = '<div class="alert alert-danger" role="alert">Error updating product: ' . $update_sql->error . '</div>';
        }

        $update_sql->close();
        header("Location: admin_dashboard.php?edit_success=1");
        exit();
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
                        <label for="stock">Stock:</label>
                        <input type="number" class="form-control" id="stock" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label for="categories">Categories:</label>
                        <input type="text" class="form-control" id="categories" name="categories">
                        <small id="categoriesHelp" class="form-text text-muted">Enter categories separated by commas (e.g., Category1, Category2).</small>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags:</label>
                        <input type="text" class="form-control" id="tags" name="tags">
                        <small id="tagsHelp" class="form-text text-muted">Enter tags separated by commas (e.g., Tag1, Tag2).</small>
                    </div>
                    <div class="form-group">
                        <label for="shipping_destination">Shipping Destination:</label>
                        <input type="text" class="form-control" id="shipping_destination" name="shipping_destination">
                    </div>
                    <div class="form-group">
                        <label for="image">Image:</label>
                        <input type="file" class="form-control-file" id="image" name="image">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Product List -->
        <div class="card">
            <div class="card-header">
                Product List
            </div>
            <div class="card-body">
                <?php echo $delete_message; ?>
                <?php echo $edit_message; ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Categories</th>
                            <th>Tags</th>
                            <th>Shipping Destination</th>
                            <th>Image</th>
                            <th>Bitcoin Payment Status</th>
                            <th>Bitcoin Transaction ID</th>
                            <th>Bitcoin Amount</th>
                            <th>Bitcoin Payment Time</th>
                            <th>Bitcoin User Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . $row["name"] . "</td>";
                                echo "<td>" . $row["description"] . "</td>";
                                echo "<td>" . $row["price"] . "</td>";
                                echo "<td>" . $row["stock"] . "</td>";
                                echo "<td>" . $row["categories"] . "</td>";
                                echo "<td>" . $row["tags"] . "</td>";
                                echo "<td>" . htmlspecialchars($row["shipping_destination"]) . "</td>";
                                echo "<td><img src='/online_shop/public/" . $row["image"] . "' alt='" . $row["name"] . "' style='max-width: 100px;'></td>";
                                echo "<td>" . $row["bitcoin_payment_status"] . "</td>";
                                echo "<td>" . $row["bitcoin_transaction_id"] . "</td>";
                                echo "<td>" . $row["bitcoin_amount"] . "</td>";
                                echo "<td>" . $row["bitcoin_payment_time"] . "</td>";
                                echo "<td>" . $row["bitcoin_user_email"] . "</td>";
                                echo "<td>
                                        <form method='POST'>
                                            <input type='hidden' name='delete_product_id' value='" . $row["id"] . "'>
                                            <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
                                        </form>
                                        <form method='GET' action='/online_shop/public/onlineshop/product_detail.php'>
                                            <input type='hidden' name='product_id' value='" . $row["id"] . "'>
                                            <button type='submit' class='btn btn-primary btn-sm'>View Details</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='14'>No products found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
