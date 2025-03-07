<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';

// Fetch all components
$components = get_all_components();

// Handle adding a new component
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_component'])) {
    $name = trim($_POST['name']);
    $stock = intval($_POST['stock']);

    if (!empty($name)) {
        add_component($name, $stock);
        header("Location: manage_components.php"); // Refresh the page
        echo "Completed";
        exit();
    }
}

// Handle deletion of a component
if (isset($_GET['delete_id'])) {
    $component_id = intval($_GET['delete_id']);
    delete_component($component_id);
    header("Location: manage_components.php"); // Refresh the page
        echo "Completed";
    exit();
}

// Function to delete a component
function delete_component($component_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM components WHERE id = ?");
    $stmt->bind_param("i", $component_id);
    $stmt->execute();
}

require 'menu.php';
?>

<div class="main-content">
    <h1>Manage Components</h1>

    <!-- Add New Component Form -->
    <div class="widget">
        <h3>Add New Component</h3>
        <form action="manage_components.php" method="POST">
            <label for="name">Component Name:</label>
            <input type="text" name="name" id="name" required>
            
            <label for="stock">Initial Stock:</label>
            <input type="number" name="stock" id="stock" value="0" required>
            
            <button type="submit" name="add_component">Add Component</button>
        </form>
    </div>

    <!-- Component List Table -->
    <div class="widget">
        <h3>Component List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($components as $component): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($component['name']); ?></td>
                        <td><?php echo $component['stock']; ?></td>
                        <td>
                            <a href="edit_component.php?id=<?php echo $component['id']; ?>">Edit</a> | 
                            <a href="manage_components.php?delete_id=<?php echo $component['id']; ?>" onclick="return confirm('Are you sure you want to delete this component?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>

