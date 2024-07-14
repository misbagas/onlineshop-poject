<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['auth_valid']) || !$_SESSION['auth_valid']) {
    header('Location: forgot_password.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_id = $_SESSION['user_id'];

    // Update user's password
    $sql = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $sql->bind_param('si', $password, $user_id);
    $sql->execute();
    
    $_SESSION['success_message'] = "Your password has been reset successfully.";
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Reset Password</h1>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form action="reset_password.php" method="post">
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
</body>
</html>
