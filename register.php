<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($name === '' || $email === '' || $phone === '') {
        $error = "Please fill in all fields.";
    } else {
        // Check if an account with this email already exists.
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with that email already exists. Try logging in instead.";
        } else {
            $stmt->close();
            $insert = $conn->prepare(
                "INSERT INTO users (name, email, phone, role) VALUES (?, ?, ?, 'customer')"
            );
            $insert->bind_param("sss", $name, $email, $phone);

            if ($insert->execute()) {
                $success = "Account created! You can now log in with your email and phone number.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $insert->close();
        }
    }
}
?>

<div class="card card-narrow">
    <h1>Create your account</h1>
    <p class="subtitle mt-0">Register to view your product warranties.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            &nbsp;<a href="login.php">Go to login</a>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" action="register.php">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="e.g. 2505551234" required>

        <button type="submit">Register</button>
    </form>
    <?php endif; ?>

    <p class="text-muted">Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php include 'includes/footer.php'; ?>
