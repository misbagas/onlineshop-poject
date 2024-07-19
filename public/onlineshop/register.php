<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$username = "";
$password = "";
$address = "";
$error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    include_once "db_connect.php";

    // Check database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Function to sanitize input values
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Sanitize and validate username
    if (isset($_POST["username"])) {
        $username = sanitize($_POST["username"]);
        // Validate username (optional additional checks can be added)
        if (empty($username)) {
            $error_message = "Username is required.";
        } elseif (strlen($username) < 4) {
            $error_message = "Username must be at least 4 characters.";
        }

        // Check if username already exists
        $sql_check_username = "SELECT id FROM users WHERE username = ?";
        $stmt_check_username = $conn->prepare($sql_check_username);
        $stmt_check_username->bind_param("s", $username);
        $stmt_check_username->execute();
        $result = $stmt_check_username->get_result();
        if ($result->num_rows > 0) {
            $error_message = "Username '$username' is already taken.";
        }
    }

    // Sanitize and validate password
    if (isset($_POST["password"])) {
        $password = sanitize($_POST["password"]);
        // Validate password (optional additional checks can be added)
        if (empty($password)) {
            $error_message = "Password is required.";
        } elseif (strlen($password) < 6) {
            $error_message = "Password must be at least 6 characters.";
        }
    }

    // Sanitize and validate address
    if (isset($_POST["address"])) {
        $address = sanitize($_POST["address"]);
        // Validate address (optional additional checks can be added)
        if (empty($address)) {
            $error_message = "Address is required.";
        }
    }

    // If no errors, proceed with registration
    if (empty($error_message)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Generate a unique authentication code
        $auth_code = bin2hex(random_bytes(16));

        // SQL query to insert user into database
        $sql = "INSERT INTO users (username, password, address, auth_code) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $hashed_password, $address, $auth_code);
            // Execute the prepared statement
            if ($stmt->execute()) {
                // Display the authentication code to the user
                echo "<script>alert('Registration successful! Your authentication code is: $auth_code. Please save this code.');</script>";
                // Redirect to login page
                echo "<script>window.location.href = 'login.php';</script>";
                exit();
            } else {
                // Registration failed
                $error_message = "Registration failed. Please try again later.";
            }
            $stmt->close();
        } else {
            $error_message = "Database error. Please try again later.";
        }
    }

    // Close database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your custom stylesheet -->
    <style>
        /* Your custom CSS styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
        }
        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: calc(100% - 20px);
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .bottom-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .bottom-text a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit">Register</button>
            </div>
        </form>
        <div class="bottom-text">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
