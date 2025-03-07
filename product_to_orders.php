<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';

// Fetch products and orders
$products = get_all_products();
$orders = get_all_orders();

// Handle creating a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];

    $order_id = create_order($customer_name, $customer_address);
    if ($order_id) {
        header("Location: product_to_orders.php?order_id=$order_id");
        exit();
    }
}

// Handle updating the date shipped
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_date_shipped'])) {
    $order_id = $_POST['order_id'];
    $date_shipped = $_POST['date_shipped'];

    update_order_date_shipped($order_id, $date_shipped);
    header("Location: product_to_orders.php?order_id=$order_id");
        echo "Completed";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_date_installed'])) {
    $order_id = $_POST['order_id'];
    $date_installed = $_POST['date_installed'];

    update_order_date_installed($order_id, $date_installed);
    header("Location: product_to_orders.php?order_id=$order_id");
        echo "Completed";
    exit();
}

// Handle adding product to order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_order'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $serial_range = $_POST['serial_range'] ?? null;

    if (add_product_to_order($order_id, $product_id, $quantity, $serial_range)) {
        header("Location: product_to_orders.php?order_id=$order_id");
        echo "Completed";
        exit();
    }
}

// Handle removing a product from an order
if (isset($_GET['remove_item']) && isset($_GET['order_id']) && isset($_GET['product_id'])) {
    $order_id = intval($_GET['order_id']);
    $product_id = intval($_GET['product_id']);

    if (remove_order_item($order_id, $product_id)) {
        header("Location: product_to_orders.php?order_id=$order_id");
        echo "Completed";
        exit();
    }
}



// Handle updating the date installed


// Get current order details
$current_order = isset($_GET['order_id']) ? get_order_by_id($_GET['order_id']) : null;
$order_items = $current_order ? get_order_items($_GET['order_id']) : [];

require 'menu.php';
?>

<div class="main-content">
    <h1>Orders</h1>

    <!-- Create New Order -->
    <div class="widget">
        <h3>Create New Order</h3>
        <form method="POST">
            <label for="customer_name">Customer Name:</label>
            <input type="text" name="customer_name" required>

            <label for="customer_address">Customer Address:</label>
            <textarea name="customer_address" placeholder="(optional)"></textarea>

            <button type="submit" name="create_order">Create Order</button>
        </form>
    </div>

    <!-- Order List -->
    <div class="widget">
        <h3>Existing Orders</h3>
        <ul>
            <?php foreach ($orders as $order): ?>
                <li>
                    <a href="product_to_orders.php?order_id=<?php echo $order['id']; ?>">
                        <?php echo "Order #" . $order['order_number'] . " - " . $order['customer_name']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($current_order): ?>
    <div class="widget">
        <h3>Order Details (<?php echo $current_order['order_number']; ?>)</h3>
        <p><strong>Customer:</strong> <?php echo $current_order['customer_name']; ?></p>
        <p><strong>Address:</strong> <?php echo $current_order['customer_address']; ?></p>
        <p><strong>Date Ordered:</strong> <?php echo $current_order['date_ordered']; ?></p>

        <!-- Update Date Shipped -->
        <h4>Update Date Shipped</h4>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?php echo $current_order['id']; ?>">
            <label for="date_shipped">Date Shipped:</label>
            
            <input type="date" name="date_shipped" value="<?php echo !empty($current_order['date_shipped']) ? substr($current_order['date_shipped'], 0, 10) : ''; ?>">
            <button type="submit" name="update_date_shipped">Update</button>
        </form>

        <form method="POST">
            <input type="hidden" name="order_id" value="<?php echo $current_order['id']; ?>">
            <label for="date_installed">Date Installed:</label>
            <input type="date" name="date_installed" value="<?php echo !empty($current_order['date_installed']) ? substr($current_order['date_installed'], 0, 10) : ''; ?>">
            <button type="submit" name="update_date_installed">Update</button>
        </form>


        <!-- Add Products to Order -->
        <h4>Add Product to Order</h4>
        <form method="POST">
            <input type="hidden" name="order_id" value="<?php echo $current_order['id']; ?>">
            <select name="product_id">
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>">
                        <?php echo $product['name'] . " (Stock: " . $product['stock'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity" required placeholder="Quantity">
            <input type="text" name="serial_range" placeholder="Serial Range (optional)">
            <button type="submit" name="add_to_order">Add to Order</button>
        </form>

        <!-- Order Items -->
        <h4>Order Items</h4>
            <ul>
                <?php foreach ($order_items as $item): ?>
                    <li>
                        <?php echo $item['name'] . " (Quantity: " . $item['quantity'] . ")"; ?>
                        <?php if (!empty($item['serial_range'])): ?>
                            <br><small>Serial Range: <?php echo $item['serial_range']; ?></small>
                        <?php endif; ?>
                        <a href="product_to_orders.php?order_id=<?php echo $current_order['id']; ?>&product_id=<?php echo $item['product_id']; ?>&remove_item=1" 
                           onclick="return confirm('Are you sure you want to remove this item?');">
                            [Remove]
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

    </div>
    <?php endif; ?>
</div>
</body>
</html>

