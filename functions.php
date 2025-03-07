<?php



// Retrieve all components
function get_all_components() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM components ORDER BY name ASC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Retrieve all products
function get_all_products() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY name ASC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Retrieve a single product by ID
function get_product_by_id($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Retrieve BOM for a given product
function get_product_bom($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT pc.component_id, c.name, pc.quantity_required 
                            FROM product_components pc 
                            JOIN components c ON pc.component_id = c.id 
                            WHERE pc.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}




// Add a new component
function add_component($name, $stock) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO components (name, stock) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $stock);
    return $stmt->execute();
}

// Update stock for a component
function update_component_stock($component_id, $new_stock) {
    global $conn;
    $stmt = $conn->prepare("UPDATE components SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $component_id);
    return $stmt->execute();
}

// Add a new product
function add_product($name) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    return $stmt->execute();
}

// Add a component to a product's BOM
function add_component_to_bom($product_id, $component_id, $quantity) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO product_components (product_id, component_id, quantity_required) 
                            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity_required = ?");
    $stmt->bind_param("iiii", $product_id, $component_id, $quantity, $quantity);
    return $stmt->execute();
}


// Authenticate user login
function authenticate_user($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        return $user['id'];
    }
    return false;
}

// Register a new user (for admin setup)
function register_user($username, $password) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    return $stmt->execute();
}
// Retrieve all transactions (component stock changes, product builds, and orders)
function get_all_transactions() {
    global $conn;
    $stmt = $conn->prepare("
        SELECT 'Component Addition' AS type, c.name AS item, t.quantity, t.date 
        FROM transactions t
        JOIN components c ON t.component_id = c.id
        UNION
        SELECT 'Product Build' AS type, p.name AS item, t.quantity, t.date 
        FROM transactions t
        JOIN products p ON t.product_id = p.id
        UNION
        SELECT 'Order Shipment' AS type, p.name AS item, -oi.quantity AS quantity, o.date_ordered AS date
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN products p ON oi.product_id = p.id
        ORDER BY date DESC
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function update_order_details($order_id, $customer_name, $customer_address, $date_shipped) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET customer_name = ?, customer_address = ?, date_shipped = ? WHERE id = ?");
    $stmt->bind_param("sssi", $customer_name, $customer_address, $date_shipped, $order_id);
    return $stmt->execute();
}
function update_order_product_quantity($order_id, $product_id, $quantity) {
    global $conn;
    $stmt = $conn->prepare("UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $order_id, $product_id);
    return $stmt->execute();
}


//---------------------------//

function add_stock_history($component_id, $quantity) {
    global $conn;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert the stock history entry
        $stmt = $conn->prepare("INSERT INTO stock_history (component_id, quantity) VALUES (?, ?)");
        $stmt->bind_param("ii", $component_id, $quantity);
        $stmt->execute();

        // Update the stock level in the components table
        $stmt = $conn->prepare("UPDATE components SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $component_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return false;
    }
}
function get_all_stock_history() {
    //echo "getting history<br>";
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM stock_history ORDER BY date_time DESC");
    
    if ($stmt) {
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
    echo "failed<br>";
        return [];
    }
}
function delete_stock_history($id) {
    global $conn;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Retrieve the component_id and quantity from the stock history entry
        $stmt = $conn->prepare("SELECT component_id, quantity FROM stock_history WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $entry = $result->fetch_assoc();

        if (!$entry) {
            throw new Exception("Stock history entry not found.");
        }

        $component_id = $entry['component_id'];
        $quantity = $entry['quantity'];

        // Subtract the quantity from the components table
        $stmt = $conn->prepare("UPDATE components SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $component_id);
        $stmt->execute();

        // Delete the stock history entry
        $stmt = $conn->prepare("DELETE FROM stock_history WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return false;
    }
}

function get_component_name_by_id($component_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT name FROM components WHERE id = $component_id");
    $stmt->bind_param("i", $component_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['name'];
}




//------------------------------------------//

function delete_product($id) {
    global $conn;

    $conn->begin_transaction();

    try {
        // Delete related BOM entries
        $stmt = $conn->prepare("DELETE FROM product_components WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}



// Remove a component from a product's BOM
function remove_component_from_bom($product_id, $component_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM product_components WHERE product_id = ? AND component_id = ?");
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ii", $product_id, $component_id);

    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    return true;
}




//--------------------------------------------------//

// Add stock for a product and update component stock accordingly
function add_product_stock($product_id, $quantity) {
    global $conn;

    // Start a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Retrieve BOM for the product
        $stmt = $conn->prepare("SELECT component_id, quantity_required FROM product_components WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $bom = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Loop through each component and reduce stock
        foreach ($bom as $item) {
            $component_id = $item['component_id'];
            $total_quantity_needed = $item['quantity_required'] * $quantity;

            // Check if enough stock is available
            $stmt = $conn->prepare("SELECT stock FROM components WHERE id = ?");
            $stmt->bind_param("i", $component_id);
            $stmt->execute();
            $component = $stmt->get_result()->fetch_assoc();

            if ($component['stock'] < $total_quantity_needed) {
                throw new Exception("Not enough stock for component ID $component_id.");
            }

            // Deduct from component stock
            $stmt = $conn->prepare("UPDATE components SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $total_quantity_needed, $component_id);
            $stmt->execute();

            // Record component stock update in stock history
            $stmt = $conn->prepare("INSERT INTO stock_history (component_id, quantity) VALUES (?, ?)");
            $negative_quantity = -$total_quantity_needed; // Represent deduction as negative value
            $stmt->bind_param("ii", $component_id, $negative_quantity);
            $stmt->execute();
        }

        // Increase product stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();

        // Record in product history
        $stmt = $conn->prepare("INSERT INTO product_history (product_id, quantity) VALUES (?, ?)");
        $stmt->bind_param("ii", $product_id, $quantity);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Retrieve all product stock history
function get_all_product_history() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM product_history ORDER BY date_time DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function delete_product_history_entry($history_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM product_history WHERE id = ?");
    $stmt->bind_param("i", $history_id);
    $stmt->execute();
}

function get_product_name_by_id($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['name'] ?? 'Unknown Product';
}

//------------------------------------------------//

function update_product_stock($product_id, $new_stock) {
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $product_id);
    return $stmt->execute();
}

//--------------------------------------------------//

// Create a new order and return its ID
function create_order($customer_name, $customer_address) {
    global $conn;
    $order_number = date('Y') . "-" . date('W') . "-" . rand(1000, 9999);
    $date_ordered = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_address, date_ordered) 
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $order_number, $customer_name, $customer_address, $date_ordered);
    $stmt->execute();
    return $stmt->insert_id;
}

// Add a product to an order
function add_product_to_order($order_id, $product_id, $quantity, $serial_range = null) {
    global $conn;

    // Check stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product['stock'] < $quantity) {
        return false; // Not enough stock
    }

    // Deduct stock
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();

    // Insert order item
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, serial_range) 
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $order_id, $product_id, $quantity, $serial_range);
    return $stmt->execute();
}

// Retrieve all orders
function get_all_orders() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM orders ORDER BY date_ordered DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Retrieve order by ID
function get_order_by_id($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Retrieve items in an order
function get_order_items($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT oi.product_id, p.name, oi.quantity, oi.serial_range 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// Update date shipped for an order
function update_order_date_shipped($order_id, $date_shipped) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET date_shipped = ? WHERE id = ?");
    $stmt->bind_param("si", $date_shipped, $order_id);
    return $stmt->execute();
}
function update_order_date_installed($order_id, $date_installed) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET date_installed = ? WHERE id = ?");
    $stmt->bind_param("si", $date_installed, $order_id);
    return $stmt->execute();
}

//-----------------------------------//

function remove_order_item($order_id, $product_id) {
    global $conn;

    // Get the quantity of the product in the order before deleting
    $stmt = $conn->prepare("SELECT quantity FROM order_items WHERE order_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $order_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        return false; // If no item found, exit early
    }

    $quantity = $item['quantity'];

    // Remove the item from the order
    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $order_id, $product_id);
    $stmt->execute();

    // Add the quantity back to product stock
    $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    return $stmt->execute();
}



?>

