<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $product_name = trim($_POST['product_name']);
    $stock = intval($_POST['stock']);

    if (!empty($product_name)) {
        add_product($product_name, $stock);
        header("Location: edit_product.php");
        echo "Completed";
        exit();
    }
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $product_id = intval($_GET['delete_id']);
    delete_product($product_id);
    header("Location: edit_product.php");
        echo "Completed";
    exit();
}

// Handle BOM modifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_bom'])) {
    $product_id = $_POST['product_id'];
    $component_id = $_POST['component_id'];
    $quantity = $_POST['quantity'];
    add_component_to_bom($product_id, $component_id, $quantity);
}

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = $_POST['product_id'];
    $new_stock = $_POST['stock'];
    update_product_stock($product_id, $new_stock);
    header("Location: edit_product.php?id=$product_id");
        echo "Completed";
    exit();
}

if (isset($_GET['remove_bom_id']) && isset($_GET['product_id'])) {
    $component_id = $_GET['remove_bom_id'];
    $product_id = $_GET['product_id'];
    
    remove_component_from_bom($product_id, $component_id);
    header("Location: edit_product.php?id=$product_id");
        echo "Completed";
    exit();
}

// Fetch data
$products = get_all_products();
$components = get_all_components();
$current_product = isset($_GET['id']) ? get_product_by_id($_GET['id']) : null;
$bom = $current_product ? get_product_bom($current_product['id']) : [];
require 'menu.php';
?>

<div class="main-content">
    <h1>Edit Product</h1>
    
    <!-- Create Product -->
    <div class="widget">
        <h3>Create Product</h3>
        <form method="POST">
            <input type="text" name="product_name" required placeholder="Product Name">
            <input type="number" name="stock" required placeholder="Initial Stock" value="0">
            <button type="submit" name="create_product">Create</button>
        </form>
    </div>
    
    <!-- Product List -->
    <div class="widget">
        <h3>Product List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a> | 
                            <a href="edit_product.php?delete_id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($current_product): ?>
    <div class="widget">
        <h3>Edit <?php echo $current_product['name']; ?></h3>

        <!-- Update Stock Form -->
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $current_product['id']; ?>">
            <label for="stock">Update Stock:</label>
            <input type="number" name="stock" value="<?php echo $current_product['stock']; ?>" required>
            <button type="submit" name="update_stock">Update Stock</button>
        </form>

        <h3>Edit BOM for <?php echo $current_product['name']; ?></h3>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $current_product['id']; ?>">
            <select name="component_id">
                <?php foreach ($components as $component): ?>
                    <option value="<?php echo $component['id']; ?>">
                        <?php echo $component['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity" required placeholder="Quantity">
            <button type="submit" name="add_to_bom">Add to BOM</button>
        </form>
        
        <h4>Current BOM</h4>
        <ul>
            <?php foreach ($bom as $item): ?>
                <li>
                    <?php echo $item['name'] . " (" . $item['quantity_required'] . ")"; ?>
                    <a href="edit_product.php?remove_bom_id=<?php echo $item['component_id']; ?>&product_id=<?php echo $current_product['id']; ?>" onclick="return confirm('Remove from BOM?');">[Remove]</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
</body>
</html>

