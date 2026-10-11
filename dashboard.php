<?php
$required_role = 'customer';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

// --- Search & sort options coming from the URL (GET) ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort   = isset($_GET['sort']) ? $_GET['sort'] : 'expiry_asc';

$sql = "SELECT w.warranty_id, w.purchase_date, w.expiry_date, p.name AS product_name, p.category
        FROM warranties w
        JOIN products p ON w.product_id = p.product_id
        WHERE w.user_id = ?";

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.category LIKE ?)";
}

switch ($sort) {
    case 'expiry_desc':
        $sql .= " ORDER BY w.expiry_date DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    default: // expiry_asc
        $sql .= " ORDER BY w.expiry_date ASC";
}

$stmt = $conn->prepare($sql);

if ($search !== '') {
    $like = "%$search%";
    $stmt->bind_param("iss", $user_id, $like, $like);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Helper to figure out badge based on expiry date
function warrantyStatus($expiry_date) {
    $today = new DateTime();
    $expiry = new DateTime($expiry_date);
    $days_left = (int)$today->diff($expiry)->format('%r%a');

    if ($days_left < 0) {
        return ['label' => 'Expired', 'class' => 'badge-expired'];
    } elseif ($days_left <= 30) {
        return ['label' => 'Expiring Soon', 'class' => 'badge-expiring'];
    } else {
        return ['label' => 'Active', 'class' => 'badge-active'];
    }
}
?>

<h1>My Warranties</h1>
<p class="subtitle mt-0">All products you've registered with Company X.</p>

<form method="get" action="dashboard.php" class="filter-bar">
    <input type="text" name="search" placeholder="Search by product or category" value="<?php echo htmlspecialchars($search); ?>">
    <select name="sort">
        <option value="expiry_asc"  <?php if ($sort === 'expiry_asc') echo 'selected'; ?>>Expiry Date (soonest first)</option>
        <option value="expiry_desc" <?php if ($sort === 'expiry_desc') echo 'selected'; ?>>Expiry Date (latest first)</option>
        <option value="name_asc"    <?php if ($sort === 'name_asc') echo 'selected'; ?>>Product Name (A-Z)</option>
    </select>
    <button type="submit">Apply</button>
</form>

<?php if ($result->num_rows === 0): ?>
    <div class="card">
        <p class="mt-0">No warranties found<?php echo $search !== '' ? ' for "' . htmlspecialchars($search) . '"' : ''; ?>.</p>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Purchase Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()):
                $status = warrantyStatus($row['expiry_date']);
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['purchase_date']); ?></td>
                <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                <td><span class="badge <?php echo $status['class']; ?>"><?php echo $status['label']; ?></span></td>
                <td><a href="warranty_detail.php?id=<?php echo $row['warranty_id']; ?>">View</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
