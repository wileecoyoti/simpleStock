<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';


// Fetch data
$components = get_all_components();
$products = get_all_products(); // Fetch all products
$stock_history = get_all_stock_history();

require 'menu.php';
?>

<div class="main-content">
    <!-- Product Stock Levels -->
    <div class="widget">
        
        <table>
            <tr><th>Product Stock Levels</th><th>Stock</th></tr>
            <?php foreach ($products as $p): ?>
                <tr><td><?= $p['name'] ?></td><td><?= $p['stock'] ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Component Stock Levels -->
    <div class="widget">
        
        <table>
            <tr><th>Component Stock Levels</th><th>Stock</th></tr>
            <?php foreach ($components as $c): ?>
                <tr><td><?= $c['name'] ?></td><td><?= $c['stock'] ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>

    <br>
    <!-- Stock History Table -->
    <div class="widget">
        <h3>Component Stock History</h3>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stock_history as $history): ?>
                    <tr>
                        <td><?php echo get_component_name_by_id($history['component_id']); ?></td>
                        <td><?php echo $history['quantity']; ?></td>
                        <td><?php echo $history['date_time']; ?></td>
                        <td>
                            <a href="manage_stock_history.php?delete_id=<?php echo $history['id']; ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

