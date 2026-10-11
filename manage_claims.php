<?php
$required_role = 'admin';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$message = "";

// --- Update a claim's status / notes ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $claim_id = (int)$_POST['claim_id'];
    $status   = $_POST['status'];
    $notes    = trim($_POST['admin_notes']);

    $allowed_statuses = ['pending', 'approved', 'rejected', 'completed'];
    if (in_array($status, $allowed_statuses, true)) {
        $stmt = $conn->prepare("UPDATE claims SET status = ?, admin_notes = ? WHERE claim_id = ?");
        $stmt->bind_param("ssi", $status, $notes, $claim_id);
        $stmt->execute();
        $message = "Claim #$claim_id updated.";
    }
}

// --- Delete an outdated/duplicate claim ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_claim') {
    $claim_id = (int)$_POST['claim_id'];
    $stmt = $conn->prepare("DELETE FROM claims WHERE claim_id = ?");
    $stmt->bind_param("i", $claim_id);
    $stmt->execute();
    $message = "Claim #$claim_id deleted.";
}

// --- Filter by status ---
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT c.claim_id, c.issue_description, c.date_submitted, c.status, c.admin_notes,
               p.name AS product_name, u.name AS customer_name, u.email
        FROM claims c
        JOIN warranties w ON c.warranty_id = w.warranty_id
        JOIN products p ON w.product_id = p.product_id
        JOIN users u ON w.user_id = u.user_id";

if ($filter !== 'all') {
    $sql .= " WHERE c.status = ?";
}
$sql .= " ORDER BY c.date_submitted DESC";

$stmt = $conn->prepare($sql);
if ($filter !== 'all') {
    $stmt->bind_param("s", $filter);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h1>Manage Claims</h1>
<p class="subtitle mt-0">Review and update customer warranty claims.</p>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="get" action="manage_claims.php" class="filter-bar">
    <select name="status">
        <option value="all"       <?php if ($filter === 'all') echo 'selected'; ?>>All Statuses</option>
        <option value="pending"   <?php if ($filter === 'pending') echo 'selected'; ?>>Pending</option>
        <option value="approved"  <?php if ($filter === 'approved') echo 'selected'; ?>>Approved</option>
        <option value="rejected"  <?php if ($filter === 'rejected') echo 'selected'; ?>>Rejected</option>
        <option value="completed" <?php if ($filter === 'completed') echo 'selected'; ?>>Completed</option>
    </select>
    <button type="submit">Filter</button>
</form>

<?php if ($result->num_rows === 0): ?>
    <div class="card"><p class="mt-0">No claims found.</p></div>
<?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="card">
        <h2><?php echo htmlspecialchars($row['product_name']); ?>
            <span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
        </h2>
        <p class="text-muted mt-0">
            <?php echo htmlspecialchars($row['customer_name']); ?> &middot; <?php echo htmlspecialchars($row['email']); ?>
            &middot; Submitted <?php echo htmlspecialchars(date("M j, Y", strtotime($row['date_submitted']))); ?>
        </p>
        <p><?php echo nl2br(htmlspecialchars($row['issue_description'])); ?></p>

        <form method="post" action="manage_claims.php">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="claim_id" value="<?php echo $row['claim_id']; ?>">

            <label for="status_<?php echo $row['claim_id']; ?>">Status</label>
            <select id="status_<?php echo $row['claim_id']; ?>" name="status">
                <?php foreach (['pending','approved','rejected','completed'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php if ($row['status'] === $s) echo 'selected'; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="notes_<?php echo $row['claim_id']; ?>">Admin Notes</label>
            <textarea id="notes_<?php echo $row['claim_id']; ?>" name="admin_notes"><?php echo htmlspecialchars($row['admin_notes']); ?></textarea>

            <button type="submit">Update Claim</button>
        </form>

        <form method="post" action="manage_claims.php" onsubmit="return confirm('Delete this claim permanently?');" style="display:inline;">
            <input type="hidden" name="action" value="delete_claim">
            <input type="hidden" name="claim_id" value="<?php echo $row['claim_id']; ?>">
            <button type="submit" class="btn-danger">Delete Claim</button>
        </form>
    </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
