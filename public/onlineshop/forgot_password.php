<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $auth_code = $_POST['auth_code'];

    // Check if username and auth_code exist in the database
    $sql = $conn->prepare("SELECT id FROM users WHERE username = ? AND auth_code = ?");
    $sql->bind_param('ss', $username, $auth_code);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['auth_valid'] = true;
        $_SESSION['user_id'] = $result->fetch_assoc()['id'];
        header('Location: reset_password.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid username or authentication code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Forgot Password</h1>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form action="forgot_password.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="auth_code">Authentication Code:</label>
                <input type="text" name="auth_code" id="auth_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</body>
</html>
