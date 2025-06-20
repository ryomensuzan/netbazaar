        // Function to check if user is logged in before adding to cart
        function checkuserloggedin() {
            const isLoggedIn = <?= json_encode(isset($_SESSION['user_id'])) ?>; // Check if user is logged in
            if (isLoggedIn) {
                alert("Product added to cart!");
                // Add your add-to-cart logic here
                // For example, you can send an AJAX request to add the product to the cart
                $.post('add_to_cart.php', {
                    product_id: productId
                }, function(response) {
                    if (response.success) {
                        alert("Product added to cart!");
                    } else {
                        alert("Failed to add product to cart. Please try again.");
                    }
                }, 'json');
            } else {
                alert("Please log in to add products to your cart.");
                loadPageContent('login'); // Load login page content
            }
        }