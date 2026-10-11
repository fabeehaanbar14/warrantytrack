<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT user_id, name, email, password FROM users WHERE email = ? AND role = 'admin'"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['user_id'];
            $_SESSION['name']    = $admin['name'];
            $_SESSION['email']   = $admin['email'];
            $_SESSION['role']    = 'admin';

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Incorrect email or password.";
        }
    } else {
        $error = "Incorrect email or password.";
    }
    $stmt->close();
}
?>

<div class="card card-narrow">
    <h1>Admin Login</h1>
    <p class="subtitle mt-0">Staff access to manage warranties and claims.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="admin_login.php">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log In</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
