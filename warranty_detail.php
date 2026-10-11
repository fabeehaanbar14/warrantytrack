<?php
$required_role = 'customer';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$warranty_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Only fetch this warranty if it belongs to the logged-in customer.
$stmt = $conn->prepare(
    "SELECT w.warranty_id, w.purchase_date, w.expiry_date, w.warranty_length_months,
            p.name AS product_name, p.category, p.price
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

$w = $result->fetch_assoc();
$expired = (new DateTime($w['expiry_date'])) < (new DateTime());
?>

<h1><?php echo htmlspecialchars($w['product_name']); ?></h1>
<p class="subtitle mt-0"><?php echo htmlspecialchars($w['category']); ?></p>

<div class="card">
    <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($w['purchase_date']); ?></p>
    <p><strong>Warranty Length:</strong> <?php echo (int)$w['warranty_length_months']; ?> months</p>
    <p><strong>Expiry Date:</strong> <?php echo htmlspecialchars($w['expiry_date']); ?></p>
    <p><strong>Price:</strong> $<?php echo number_format($w['price'], 2); ?></p>

    <?php if ($expired): ?>
        <div class="alert alert-error">This warranty has expired and is no longer eligible for a claim.</div>
    <?php else: ?>
        <a href="submit_claim.php?warranty_id=<?php echo $w['warranty_id']; ?>" class="btn">Submit a Claim</a>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary">Back to My Warranties</a>
</div>

<?php include 'includes/footer.php'; ?>
