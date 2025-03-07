<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';

// Get all components for the dropdown
$components = get_all_components();

// Handle adding new stock history entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['component_id']) && isset($_POST['quantity'])) {
    $component_id = $_POST['component_id'];
    $quantity = $_POST['quantity'];

    // Insert the stock history entry into the database
    $add_stock_history_result = add_stock_history($component_id, $quantity);

    if ($add_stock_history_result) {
        echo "complete";
        header("Location: manage_stock_history.php");
        echo "Completed";
        exit();
    } else {
        $error_message = "Failed to add stock history entry.";
    }
}

// Handle deleting stock history entry
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete the stock history entry
    $delete_result = delete_stock_history($delete_id);

    if ($delete_result) {
        header("Location: manage_stock_history.php");
        echo "Completed";
        exit();
    } else {
        $error_message = "Failed to delete stock history entry.";
    }
}

// Get all stock history entries
$stock_history = get_all_stock_history();
require 'menu.php';
?>


    <div class="main-content">
        <h1>Add/Remove Stock</h1>

        <!-- Add Stock History Form -->
        <div class="widget">
            <h3>Add Stock History Entry</h3>
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="manage_stock_history.php" method="POST">
                <label for="component_id">Component</label>
                <select name="component_id" id="component_id" required>
                    <?php foreach ($components as $component): ?>
                        <option value="<?php echo $component['id']; ?>"><?php echo $component['name']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" required>

                <button type="submit">Add Entry</button>
            </form>
        </div>

        <!-- Stock History Table -->
        <div class="widget">
            <h3>Stock History</h3>
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

