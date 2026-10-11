<?php
$required_role = 'admin';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort   = isset($_GET['sort']) ? $_GET['sort'] : 'expiry_asc';

$sql = "SELECT w.warranty_id, w.purchase_date, w.expiry_date, p.name AS product_name,
               u.name AS customer_name, u.email, u.phone
        FROM warranties w
        JOIN products p ON w.product_id = p.product_id
        JOIN users u ON w.user_id = u.user_id
        WHERE 1=1";

if ($search !== '') {
    $sql .= " AND (u.email LIKE ? OR u.phone LIKE ? OR p.name LIKE ?)";
}

switch ($sort) {
    case 'expiry_desc':
        $sql .= " ORDER BY w.expiry_date DESC";
        break;
    case 'customer_asc':
        $sql .= " ORDER BY u.name ASC";
        break;
    default:
        $sql .= " ORDER BY w.expiry_date ASC";
}

$stmt = $conn->prepare($sql);

if ($search !== '') {
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<h1>All Warranties</h1>
<p class="subtitle mt-0">Every warranty on file across all customers.</p>

<form method="get" action="admin_dashboard.php" class="filter-bar">
    <input type="text" name="search" placeholder="Search by customer email, phone, or product" value="<?php echo htmlspecialchars($search); ?>">
    <select name="sort">
        <option value="expiry_asc"   <?php if ($sort === 'expiry_asc') echo 'selected'; ?>>Expiry Date (soonest first)</option>
        <option value="expiry_desc"  <?php if ($sort === 'expiry_desc') echo 'selected'; ?>>Expiry Date (latest first)</option>
        <option value="customer_asc" <?php if ($sort === 'customer_asc') echo 'selected'; ?>>Customer Name (A-Z)</option>
    </select>
    <button type="submit">Apply</button>
</form>

<?php if ($result->num_rows === 0): ?>
    <div class="card"><p class="mt-0">No warranties found.</p></div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Contact</th>
                <th>Product</th>
                <th>Purchase Date</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?><br><span class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></span></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['purchase_date']); ?></td>
                <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
