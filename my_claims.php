<?php
$required_role = 'customer';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Handle cancelling a pending claim (delete).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_claim_id'])) {
    $claim_id = (int)$_POST['cancel_claim_id'];

    // Only allow cancelling a claim that belongs to this customer and is still pending.
    $stmt = $conn->prepare(
        "DELETE c FROM claims c
         JOIN warranties w ON c.warranty_id = w.warranty_id
         WHERE c.claim_id = ? AND w.user_id = ? AND c.status = 'pending'"
    );
    $stmt->bind_param("ii", $claim_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Claim cancelled.";
    } else {
        $message = "That claim could not be cancelled (it may already be in review).";
    }
    $stmt->close();
}

// --- Sort option ---
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$order_sql = ($sort === 'date_asc') ? "c.date_submitted ASC" : "c.date_submitted DESC";

$stmt = $conn->prepare(
    "SELECT c.claim_id, c.issue_description, c.date_submitted, c.status, p.name AS product_name
     FROM claims c
     JOIN warranties w ON c.warranty_id = w.warranty_id
     JOIN products p ON w.product_id = p.product_id
     WHERE w.user_id = ?
     ORDER BY $order_sql"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1>My Claims</h1>
<p class="subtitle mt-0">Track the status of claims you've submitted.</p>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="get" action="my_claims.php" class="filter-bar">
    <select name="sort">
        <option value="date_desc" <?php if ($sort === 'date_desc') echo 'selected'; ?>>Newest First</option>
        <option value="date_asc"  <?php if ($sort === 'date_asc') echo 'selected'; ?>>Oldest First</option>
    </select>
    <button type="submit">Apply</button>
</form>

<?php if ($result->num_rows === 0): ?>
    <div class="card"><p class="mt-0">You haven't submitted any claims yet.</p></div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Issue</th>
                <th>Submitted</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['issue_description']); ?></td>
                <td><?php echo htmlspecialchars(date("M j, Y", strtotime($row['date_submitted']))); ?></td>
                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="post" action="my_claims.php" onsubmit="return confirm('Cancel this claim?');" style="margin:0;">
                            <input type="hidden" name="cancel_claim_id" value="<?php echo $row['claim_id']; ?>">
                            <button type="submit" class="btn-danger" style="margin:0; padding:5px 10px; font-size:0.82rem;">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
