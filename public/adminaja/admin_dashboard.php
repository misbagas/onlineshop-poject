<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "172.27.98.229";
$username = "appuser";
$password = "@Bagaskara123";
$database = "online_shop";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$delete_message = '';
$upload_message = '';
$edit_message = '';

// Fetch chat messages
$chat_query = "SELECT * FROM chat_messages WHERE sender='admin' OR receiver='admin' ORDER BY timestamp DESC";
$chat_result = $conn->query($chat_query);

// Fetch list of users for selecting a recipient
$user_query = "SELECT username FROM users";
$user_result = $conn->query($user_query);

// Handle sending chat messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chat_message'])) {
    $receiver = $_POST['receiver'];
    $message = $_POST['chat_message'];
    $sender = 'admin';

    $stmt = $conn->prepare("INSERT INTO chat_messages (sender, receiver, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $product_id = $_POST['delete_product_id'];
    $select_sql = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $select_sql->bind_param('i', $product_id);
    $select_sql->execute();
    $select_sql->bind_result($image_path);
    $select_sql->fetch();
    $select_sql->close();

    $delete_sql = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete_sql->bind_param('i', $product_id);

    if ($delete_sql->execute()) {
        $delete_message = '<div class="alert alert-success" role="alert">Product deleted successfully</div>';

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
}

// Handle product upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $categories = $_POST['categories'];
    $tags = $_POST['tags'];
    $shipping_destination = $_POST['shipping_destination'];

    $target_dir = "/var/www/html/online_shop/public/photo_product/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    chmod($target_dir, 0755);

    if ($_FILES["image"]["size"] > 500000) {
        $upload_message = '<div class="alert alert-danger" role="alert">Sorry, your file is too large.</div>';
    } elseif (!in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'png', 'gif'))) {
        $upload_message = '<div class="alert alert-danger" role="alert">Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>';
    } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        chmod($target_file, 0644);
        $image_relative_path = "photo_product/" . basename($_FILES["image"]["name"]);

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
}

// Handle product edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product_id'])) {
    $product_id = $_POST['edit_product_id'];
    $name = $_POST['edit_name'];
    $description = $_POST['edit_description'];
    $price = $_POST['edit_price'];
    $stock = $_POST['edit_stock'];
    $categories = $_POST['edit_categories'];
    $tags = $_POST['edit_tags'];
    $shipping_destination = $_POST['shipping_destination'];

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

// Fetch chat messages
$chat_query = "SELECT * FROM chat_messages WHERE sender='admin' OR receiver='admin' ORDER BY timestamp DESC";
$chat_result = $conn->query($chat_query);

// Fetch detailed order information
$sql = "
    SELECT 
        users.username,
        users.address,
        products.name AS product_name,
        products.price,
        products.stock,
        products.description,
        orders.quantity,
        orders.total_price,
        transactions.bitcoin_transaction_id,
        transactions.bitcoin_amount
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN products ON orders.product_id = products.id
    JOIN transactions ON orders.id = transactions.order_id
";
$order_result = $conn->query($sql);

$sql_fetch_products = "SELECT * FROM products";
$product_result = $conn->query($sql_fetch_products);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
            font-size: 1.25rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .alert {
            margin-top: 15px;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            background-color: #fff;
        }
        .table th, .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        .table th {
            background-color: #f1f1f1;
            font-weight: 600;
            color: #495057;
        }
        .table td img {
            max-width: 80px;
            height: auto;
        }
        .chat-container {
            margin-top: 30px;
        }
        .chat-message {
            margin-bottom: 10px;
        }
        .chat-message.admin {
            color: #007bff;
        }
        .chat-message.user {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $delete_message; ?>
        <?php echo $upload_message; ?>
        <?php echo $edit_message; ?>

        <!-- Product Upload Form -->
        <div class="card">
            <div class="card-header">
                Upload New Product
            </div>
            <div class="card-body">
                <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label for="categories">Categories</label>
                        <input type="text" class="form-control" id="categories" name="categories" required>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_destination">Shipping Destination</label>
                        <input type="text" class="form-control" id="shipping_destination" name="shipping_destination" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" class="form-control-file" id="image" name="image" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Product</button>
                </form>
            </div>
        </div>

        <!-- Product Edit Form -->
        <div class="card">
            <div class="card-header">
                Edit Product
            </div>
            <div class="card-body">
                <form action="admin_dashboard.php" method="post">
                    <div class="form-group">
                        <label for="edit_product_id">Product ID</label>
                        <input type="text" class="form-control" id="edit_product_id" name="edit_product_id" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Product Name</label>
                        <input type="text" class="form-control" id="edit_name" name="edit_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="edit_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_price">Price</label>
                        <input type="number" class="form-control" id="edit_price" name="edit_price" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="edit_stock">Stock</label>
                        <input type="number" class="form-control" id="edit_stock" name="edit_stock">
                    </div>
                    <div class="form-group">
                        <label for="edit_categories">Categories</label>
                        <input type="text" class="form-control" id="edit_categories" name="edit_categories">
                    </div>
                    <div class="form-group">
                        <label for="edit_tags">Tags</label>
                        <input type="text" class="form-control" id="edit_tags" name="edit_tags">
                    </div>
                    <div class="form-group">
                        <label for="shipping_destination">Shipping Destination</label>
                        <input type="text" class="form-control" id="shipping_destination" name="shipping_destination">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </form>
            </div>
        </div>

        <!-- Product List -->
        <div class="card">
            <div class="card-header">
                Product List
            </div>
            <div class="card-body">
                <table class="table">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $product_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td><?php echo htmlspecialchars($row['categories']); ?></td>
                            <td><?php echo htmlspecialchars($row['tags']); ?></td>
                            <td><?php echo htmlspecialchars($row['shipping_destination']); ?></td>
                            <td><img src="/var/www/html/online_shop/public/photo_product<?php echo htmlspecialchars(basename($row['image'])); ?>" alt="Product Image"></td>
                            <td>
                                <form action="admin_dashboard.php" method="post" style="display:inline;">
                                    <input type="hidden" name="delete_product_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order List -->
        <div class="card">
            <div class="card-header">
                Order List
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Address</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Transaction ID</th>
                            <th>Transaction Amount (BTC)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $order_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                            <td><?php echo htmlspecialchars($row['bitcoin_transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['bitcoin_amount']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chat Section -->
        <div class="card">
            <div class="card-header">Chat with Users</div>
            <div class="card-body">
                <div class="chat-box" style="height: 300px; overflow-y: scroll;">
                    <?php while ($chat = $chat_result->fetch_assoc()) { ?>
                        <div>
                            <strong><?php echo htmlspecialchars($chat['sender']); ?>:</strong>
                            <span><?php echo htmlspecialchars($chat['message']); ?></span>
                            <small class="text-muted"><?php echo $chat['timestamp']; ?></small>
                        </div>
                    <?php } ?>
                </div>
                <form action="admin_dashboard.php" method="POST">
                    <div class="form-group">
                        <label for="receiver">Send to</label>
                        <select class="form-control" id="receiver" name="receiver" required>
                            <?php while ($user = $user_result->fetch_assoc()) { ?>
                                <option value="<?php echo htmlspecialchars($user['username']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="chat_message">Message</label>
                        <textarea class="form-control" id="chat_message" name="chat_message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>