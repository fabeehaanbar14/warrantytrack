<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare(
        "SELECT user_id, name, email FROM users WHERE email = ? AND phone = ? AND role = 'customer'"
    );
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = 'customer';

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "We couldn't find an account with that email and phone number.";
    }
    $stmt->close();
}
?>

<div class="card card-narrow">
    <h1>Customer Login</h1>
    <p class="subtitle mt-0">Enter the email and phone number you registered with.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required>

        <button type="submit">Log In</button>
    </form>

    <p class="text-muted">Don't have an account? <a href="register.php">Register</a></p>
</div>

<?php include 'includes/footer.php'; ?>
