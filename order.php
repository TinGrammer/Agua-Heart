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
                    <div class="gallon-options">
                        <div class="gallon-option">
                            <input type="radio" name="gallon_type" id="slim" value="Slim" checked>
                            <label class="gallon-label" for="slim">
                                <span class="gallon-emoji">🫙</span>
                                <span class="gallon-name">Slim</span>
                                <span class="gallon-price">₱25 / gallon</span>
                            </label>
                        </div>
                        <div class="gallon-option">
                            <input type="radio" name="gallon_type" id="round" value="Round">
                            <label class="gallon-label" for="round">
                                <span class="gallon-emoji">🪣</span>
                                <span class="gallon-name">Round</span>
                                <span class="gallon-price">₱30 / gallon</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Gallons <span>*</span></label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                        <input type="number" id="quantity" name="quantity" class="qty-input" value="1" min="1" max="99">
                        <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
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
                        <span>Gallon Type</span>
                        <span id="sumType">Slim Gallon</span>
                    </div>
                    <div class="summary-row">
                        <span>Quantity</span>
                        <span id="sumQty">1</span>
                    </div>
                    <div class="summary-row">
                        <span>Price</span>
                        <span id="sumPrice">₱25 each</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount</span>
                        <span id="sumTotal">₱25</span>
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

<script src="assets/js/main.js"></script>
<script src="assets/js/order.js"></script>
</body>
</html>
