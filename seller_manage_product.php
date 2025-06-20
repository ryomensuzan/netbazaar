<?php
session_start();
require_once 'db.php'; // Include database connection

// Check if the seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Add Product Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';

        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Create directory with read/write permissions
        }

        // Generate a unique file name to avoid conflicts
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $message = "File is not an image.";
            $image = null;
        } else {
            // Save the file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = $targetFile; // Set the image path for database insertion
            } else {
                $message = "Error uploading image.";
                $image = null;
            }
        }
    } else {
        $message = "No image uploaded or an error occurred.";
    }

    // Debugging: Check the value of $image
    var_dump($image);

    // Insert product into database
    if ($image) {
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock, image, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssi", $category_id, $name, $description, $price, $stock, $image, $seller_id);
        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully.');
            window.location.href = 'seller_dashboard.php';
            </script>";
            exit;
        } else {
            $message = "Error adding product: " . $stmt->error;
        }
    } else {
        $message = "Image upload failed. Product not added.";
    }
}

// Edit Product Logic
if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_GET['edit'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_POST['image']; // You can add logic to handle image update as well

    // Update product details
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category_id = ?, price = ?, stock = ?, image = ? WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ssdiissi", $name, $description, $category_id, $price, $stock, $image, $product_id, $seller_id);
    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully.');
        window.location.href = 'seller_dashboard.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Error updating product.');
        window.location.href = 'seller_dashboard.php';
        </script>";
        exit;
    }
}

// Delete Product Logic
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $product_id, $seller_id);
    if ($stmt->execute()) {
        echo "<script>alert('Product deleted successfully.');
        window.location.href = 'seller_dashboard.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Error deleting product.');
        window.location.href = 'seller_dashboard.php';
        </script>";
    }
}

// Fetch products for this seller
$stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$productsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <title>Manage Products - Net Bazaar</title>
    <link rel="stylesheet" href="src/seller_mp.css">
</head>

<body>
    <h1>Manage Your Products</h1>

    <div class="container">
        <!-- Add New Product Form -->
        <div class="form-section">
            <h2>Add New Product</h2>
            <form method="POST" action="seller_manage_product.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category_id" required>
                        <?php 
                        $categoryResult = $conn->query("SELECT * FROM categories");
                        while ($category = $categoryResult->fetch_assoc()) {
                            echo "<option value='{$category['id']}'>{$category['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price (NRP)</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" required>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>
                </div>

                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>

        <!-- Products List -->
        <div class="product-section">
            <h2>Your Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $productsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['category_id']) ?></td>
                        <td>NRP <?= number_format($product['price'], 2) ?></td>
                        <td><?= $product['stock'] ?></td>
                        <td>
                            <?php
                            $stockClass = '';
                            $stockText = '';
                            if ($product['stock'] > 10) {
                                $stockClass = 'in-stock';
                                $stockText = 'In Stock';
                            } elseif ($product['stock'] > 0) {
                                $stockClass = 'low-stock';
                                $stockText = 'Low Stock';
                            } else {
                                $stockClass = 'out-of-stock';
                                $stockText = 'Out of Stock';
                            }
                            ?>
                            <span class="status-tag <?= $stockClass ?>"><?= $stockText ?></span>
                        </td>
                        <td class="action-links">
                            <a href="seller_manage_product.php?edit=<?= $product['id'] ?>" class="edit-link">Edit</a>
                            <a href="seller_manage_product.php?delete=<?= $product['id'] ?>" 
                               class="delete-link"
                               onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>