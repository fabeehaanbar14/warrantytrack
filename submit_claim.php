<?php
$required_role = 'customer';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$warranty_id = isset($_GET['warranty_id']) ? (int)$_GET['warranty_id'] : 0;
$error = "";
$success = "";

// Confirm this warranty belongs to the logged-in customer and hasn't expired.
$stmt = $conn->prepare(
    "SELECT w.warranty_id, w.expiry_date, p.name AS product_name
     FROM warranties w
     JOIN products p ON w.product_id = p.product_id
     WHERE w.warranty_id = ? AND w.user_id = ?"
);
$stmt->bind_param("ii", $warranty_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='card'><p class='mt-0'>Warranty not found.</p></div>";
    include 'includes/footer.php';
    exit();
}

$warranty = $result->fetch_assoc();
$expired = (new DateTime($warranty['expiry_date'])) < (new DateTime());

if ($expired) {
    echo "<div class='card'><div class='alert alert-error'>This warranty has expired and is not eligible for a claim.</div>
          <a href='warranty_detail.php?id=$warranty_id' class='btn btn-secondary'>Back</a></div>";
    include 'includes/footer.php';
    exit();
}

// Handle the form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description']);

    if ($description === '') {
        $error = "Please describe the issue before submitting.";
    } else {
        $insert = $conn->prepare(
            "INSERT INTO claims (warranty_id, issue_description, status) VALUES (?, ?, 'pending')"
        );
        $insert->bind_param("is", $warranty_id, $description);

        if ($insert->execute()) {
            $success = "Your claim has been submitted. You can track its status under My Claims.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $insert->close();
    }
}
?>

<h1>Submit a Claim</h1>
<p class="subtitle mt-0">For: <?php echo htmlspecialchars($warranty['product_name']); ?></p>

<div class="card card-narrow">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <a href="my_claims.php" class="btn">View My Claims</a>
        <a href="dashboard.php" class="btn btn-secondary">Back to My Warranties</a>
    <?php else: ?>
        <form method="post" action="submit_claim.php?warranty_id=<?php echo $warranty_id; ?>">
            <label for="description">Describe the issue</label>
            <textarea id="description" name="description" required placeholder="What's wrong with the product?"></textarea>
            <button type="submit">Submit Claim</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
