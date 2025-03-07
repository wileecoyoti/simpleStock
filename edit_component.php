<?php
session_start();
require 'config.php';
check_login();
require 'functions.php';

// Fetch component details for editing
$component_id = $_GET['id'];
$component = get_component_by_id($component_id);

// Update component if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_component'])) {
    $name = $_POST['name'];
    $stock = $_POST['stock'];

    // Sanitize inputs
    $name = htmlspecialchars($name);
    $stock = (int)$stock;

    // Update component in the database
    update_component($component_id, $name, $stock);
    header("Location: manage_components.php"); // Redirect to component management page
        echo "Completed";
    exit();
}

function update_component($component_id, $name, $stock) {
    global $conn;
    
    // Prepare the SQL UPDATE statement
    $stmt = $conn->prepare("UPDATE components SET name = ?, stock = ? WHERE id = ?");
    
    // Bind the parameters and execute the statement
    $stmt->bind_param("sii", $name, $stock, $component_id);
    
    // Execute the query
    if ($stmt->execute()) {
        echo "Component updated successfully.";
    } else {
        echo "Error updating component: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}


// Function to fetch component details by ID
function get_component_by_id($component_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM components WHERE id = $component_id");
    //$stmt->bind_param("i", $component_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}


require 'menu.php';
?>


    <div class="main-content">
        <h1>Edit Component</h1>

        <!-- Edit Component Form -->
        <div class="widget">
            <h3>Component Details</h3>
            <form action="edit_component.php?id=<?php echo $component['id']; ?>" method="POST">
                <label for="name">Component Name</label>
                <input type="text" name="name" id="name" value="<?php echo $component['name']; ?>">

                <label for="stock">Stock</label>
                <input type="number" name="stock" id="stock" value="<?php echo $component['stock']; ?>">

                <button type="submit" name="update_component">Update Component</button>
            </form>
        </div>
    </div>
</body>
</html>

