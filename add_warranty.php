<?php
$required_role = 'admin';
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';

$error = "";
$success = "";
$found_customer = null;

// --- Step 1: Look up a customer by email ---
if (isset($_POST['action']) && $_POST['action'] === 'find_customer') {
    $lookup_email = trim($_POST['lookup_email']);

    $stmt = $conn->prepare("SELECT user_id, name, email, phone FROM users WHERE email = ? AND role = 'customer'");
    $stmt->bind_param("s", $lookup_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $found_customer = $result->fetch_assoc();
    } else {
        $error = "No customer found with that email. You can create a new customer account below.";
    }
}

// --- Step 1b: Create a new customer (if one wasn't found) ---
if (isset($_POST['action']) && $_POST['action'] === 'create_customer') {
    $name  = trim($_POST['new_name']);
    $email = trim($_POST['new_email']);
    $phone = trim($_POST['new_phone']);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "A customer with that email already exists.";
    } else {
        $insert = $conn->prepare("INSERT INTO users (name, email, phone, role) VALUES (?, ?, ?, 'customer')");
        $insert->bind_param("sss", $name, $email, $phone);
        $insert->execute();
        $found_customer = [
            'user_id' => $insert->insert_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ];
    }
}

// --- Step 2: Create the warranty ---
if (isset($_POST['action']) && $_POST['action'] === 'add_warranty') {
    $customer_id = (int)$_POST['customer_id'];
    $product_id  = (int)$_POST['product_id'];
    $purchase_date = $_POST['purchase_date'];
    $length_months = (int)$_POST['length_months'];

    $expiry = new DateTime($purchase_date);
    $expiry->modify("+$length_months months");
    $expiry_date = $expiry->format('Y-m-d');

    $insert = $conn->prepare(
        "INSERT INTO warranties (user_id, product_id, purchase_date, warranty_length_months, expiry_date)
         VALUES (?, ?, ?, ?, ?)"
    );
    $insert->bind_param("iisis", $customer_id, $product_id, $purchase_date, $length_months, $expiry_date);

    if ($insert->execute()) {
        $success = "Warranty added successfully.";
    } else {
        $error = "Something went wrong adding the warranty.";
    }
}

// Product list for the dropdown
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name ASC");
?>

<h1>Add Warranty</h1>
<p class="subtitle mt-0">Look up a customer, then register a new product warranty for them.</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (!$found_customer): ?>
    <!-- Step 1: find a customer -->
    <div class="card card-narrow">
        <h2>Find Customer</h2>
        <form method="post" action="add_warranty.php">
            <input type="hidden" name="action" value="find_customer">
            <label for="lookup_email">Customer Email</label>
            <input type="email" id="lookup_email" name="lookup_email" required>
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if ($error): ?>
    <!-- Step 1b: create a new customer -->
    <div class="card card-narrow">
        <h2>Create New Customer</h2>
        <form method="post" action="add_warranty.php">
            <input type="hidden" name="action" value="create_customer">
            <label for="new_name">Full Name</label>
            <input type="text" id="new_name" name="new_name" required>
            <label for="new_email">Email</label>
            <input type="email" id="new_email" name="new_email" required>
            <label for="new_phone">Phone</label>
            <input type="tel" id="new_phone" name="new_phone" required>
            <button type="submit">Create Customer</button>
        </form>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Step 2: add the warranty for the found/created customer -->
    <div class="card card-narrow">
        <h2>Customer: <?php echo htmlspecialchars($found_customer['name']); ?></h2>
        <p class="text-muted mt-0"><?php echo htmlspecialchars($found_customer['email']); ?> &middot; <?php echo htmlspecialchars($found_customer['phone']); ?></p>

        <form method="post" action="add_warranty.php">
            <input type="hidden" name="action" value="add_warranty">
            <input type="hidden" name="customer_id" value="<?php echo $found_customer['user_id']; ?>">

            <label for="product_id">Product</label>
            <select id="product_id" name="product_id" required>
                <?php while ($p = $products->fetch_assoc()): ?>
                    <option value="<?php echo $p['product_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endwhile; ?>
            </select>

            <label for="purchase_date">Purchase Date</label>
            <input type="date" id="purchase_date" name="purchase_date" required>

            <label for="length_months">Warranty Length (months)</label>
            <input type="number" id="length_months" name="length_months" min="1" value="12" required>

            <button type="submit">Add Warranty</button>
        </form>
    </div>
    <a href="add_warranty.php" class="btn btn-secondary">Look Up a Different Customer</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
