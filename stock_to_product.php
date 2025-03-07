<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';;

// Fetch data
$products = get_all_products();
$product_history = get_all_product_history(); // Fetch product stock movement history

// Handle product stock adjustments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    add_product_stock($product_id, $quantity); // Function to update product stock and deduct components
    header("Location: stock_to_product.php");
    exit();
}

require 'menu.php';
?>

<div class="main-content">
    <h1>Manage Product Stock</h1>
    Note! this will remove components from the component stock based on the assembly contents.<br>
    Also this will not work if component stock levels are not available for assembly.<br><br>
    <!-- Add Product Stock -->
    <div class="widget">
        <h3>Add Product Stock</h3>
        <form method="POST">
            <select name="product_id">
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>">
                        <?php echo $product['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity" required placeholder="Quantity">
            <button type="submit" name="add_stock">Add</button>
        </form>
    </div>

    <br>

    <!-- Product History Table -->
    <div class="widget">
        <h3>Product History</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($product_history as $history): ?>
                    <tr>
                        <td><?php echo get_product_name_by_id($history['product_id']); ?></td>
                        <td><?php echo $history['quantity']; ?></td>
                        <td><?php echo $history['date_time']; ?></td>
                        <td>
                            <a href="stock_to_product.php?delete_id=<?php echo $history['id']; ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

