<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Now – Agua Heart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="nav-logo">
        <div class="logo-icon">💧</div>
        <span>Agua Heart</span>
    </a>
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php">Home</a></li>
        <li><a href="index.php#about">About</a></li>
        <li><a href="index.php#products">Products</a></li>
        <li><a href="index.php#contact">Contact</a></li>
        <li><a href="order.php" class="btn-order active">Order Now</a></li>
        <li><a href="admin/dashboard.php">Dashboard</a></li>
    </ul>
    <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
    </div>
</nav>

<!-- Order Page -->
<div class="order-page">
    <div class="order-container">
        <div class="order-header">
            <h1>💧 Place Your Order</h1>
            <p>Fill in the form below and we'll deliver fresh water to your door!</p>
        </div>

        <div class="order-form">
            <form id="orderForm" novalidate>

                <!-- Customer Info -->
                <div class="form-section-title">👤 Customer Information</div>

                <div class="form-group">
                    <label for="customer_name">Full Name <span>*</span></label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control"
                           placeholder="e.g. Maria Santos" autocomplete="name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_number">Contact Number <span>*</span></label>
                        <input type="tel" id="contact_number" name="contact_number" class="form-control"
                               placeholder="e.g. 09171234567" autocomplete="tel">
                    </div>
                    <div class="form-group">
                        <label for="location">Address / Location <span>*</span></label>
                        <input type="text" id="location" name="location" class="form-control"
                               placeholder="Street, Barangay, City" autocomplete="street-address">
                    </div>
                </div>

                <!-- Order Details -->
                <div class="form-section-title" style="margin-top:10px">🛒 Order Details</div>

                <div class="form-group">
                    <label>Type of Gallon <span>*</span></label>
                    <div class="gallon-mixed-options">
                        <div class="gallon-mixed-card">
                            <div class="gallon-mixed-header">
                                <img src="slim.jpg" alt="Slim gallon" class="gallon-image">
                                <div>
                                    <div class="gallon-name">Slim</div>
                                    <div class="gallon-price">₱25 / gallon</div>
                                </div>
                            </div>
                            <div class="qty-control">
                                <button type="button" class="qty-btn" data-target="slim_quantity" data-step="-1">−</button>
                                <input type="number" id="slim_quantity" name="slim_quantity" class="qty-input" value="0" min="0" max="99">
                                <button type="button" class="qty-btn" data-target="slim_quantity" data-step="1">+</button>
                            </div>
                        </div>

                        <div class="gallon-mixed-card">
                            <div class="gallon-mixed-header">
                                <img src="ROUND.jpg" alt="Round gallon" class="gallon-image">
                                <div>
                                    <div class="gallon-name">Round</div>
                                    <div class="gallon-price">₱30 / gallon</div>
                                </div>
                            </div>
                            <div class="qty-control">
                                <button type="button" class="qty-btn" data-target="round_quantity" data-step="-1">−</button>
                                <input type="number" id="round_quantity" name="round_quantity" class="qty-input" value="0" min="0" max="99">
                                <button type="button" class="qty-btn" data-target="round_quantity" data-step="1">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"
                              placeholder="e.g. Please deliver in the morning, call before arriving..."></textarea>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Slim</span>
                        <span id="sumSlimQty">0 items</span>
                    </div>
                    <div class="summary-row">
                        <span>Round</span>
                        <span id="sumRoundQty">0 items</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount</span>
                        <span id="sumTotal">₱0</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    🛒 Place Order
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <div class="modal-icon">✓</div>
        <h2>Order Placed!</h2>
        <p>Thank you, <strong id="modalName"></strong>!</p>
        <p>Your order number is:</p>
        <div class="order-num" id="modalOrderNum"></div>
        <p style="margin-top:10px;font-size:0.85rem">We'll contact you shortly to confirm your delivery.</p>
        <div style="display:flex;gap:10px;margin-top:25px;justify-content:center">
            <a href="order.php" class="btn btn-blue" style="font-size:0.9rem;padding:10px 20px" onclick="closeModal()">New Order</a>
            <button onclick="closeModal()" class="btn" style="background:#f0f8ff;color:#0077b6;font-size:0.9rem;padding:10px 20px">Close</button>
        </div>
    </div>
</div>

<script src="assets/js/main.js?v=3"></script>
<script src="assets/js/order.js?v=3"></script>
</body>
</html>
