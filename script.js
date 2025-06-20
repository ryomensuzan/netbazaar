  function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        function signupModal() {
            document.getElementById("signupModal").style.display = "block";
        }

        function closesignupModal() {
            document.getElementById("signupModal").style.display = "none";
        }

        function sellerModal() {
            document.getElementById("sellerModal").style.display = "block";
        }

        function closesellerModal() {
            document.getElementById("sellerModal").style.display = "none";
        }

         window.onclick = function(event) {
            const loginModal = document.getElementById("myModal");
            const signupModal = document.getElementById("signupModal");
            const sellerModal = document.getElementById("sellerModal");

            if (event.target === loginModal) {
                closeModal();
            }
            if (event.target === signupModal) {
                closesignupModal();
            }
            if (event.target === sellerModal) {
                closesellerModal();
            }
        }


        // Mobile menu toggle functionality
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const navbarVertical = document.querySelector('.navbar');

        mobileMenuToggle.addEventListener('click', () => {
            navbarVertical.style.display =
                navbarVertical.style.display === 'none' || navbarVertical.style.display === '' ?
                'block' :
                'none';
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navbarVertical.contains(e.target) &&
                !mobileMenuToggle.contains(e.target) &&
                window.innerWidth <= 768) {
                navbarVertical.style.display = 'none';
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                navbarVertical.style.display = 'block';
            }
        });

        function toggleUserMenu(icon) {
            icon.classList.toggle("open");
            document.querySelector('.nav-items').classList.toggle("active");
        }

                function updateQuantity(cartId, change) {
            fetch('update_cart_quantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_id=${cartId}&change=${change}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function removeItem(cartId) {
            if (confirm('Remove this item from cart?')) {
                fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `cart_id=${cartId}`
                    })
                    .then(() => {
                        location.reload();
                    });
            }
        }