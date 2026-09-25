const PRICES = { Slim: 25, Round: 30 };
const SERVICE_AREA_MESSAGE = 'Sorry, we currently only deliver to selected areas in Sta. Rosa. Please enter an address within our service area.';

function isAllowedLocation(value) {
    const normalized = value.toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
    return /\bpooc\b|\bmarket\s+area\b|\bbalibago\b|\bdila\b|\bdita\b|\b(?:brgy|barangay)\s+labas\b|\bmalusak\b|\b(?:sta|santa)\s+rosa\b/.test(normalized);
}

function validateLocation(showMessage = true) {
    const location = document.getElementById('location');
    const message = document.getElementById('locationMessage');
    const value = location.value.trim();
    const allowed = value !== '' && isAllowedLocation(value);

    if (value === '') {
        location.classList.remove('error');
        message.textContent = '';
        message.classList.remove('show');
    } else if (!allowed) {
        location.classList.add('error');
        message.textContent = SERVICE_AREA_MESSAGE;
        message.classList.add('show');
    } else {
        location.classList.remove('error');
        message.textContent = '';
        message.classList.remove('show');
    }

    return allowed;
}

function getQtyValue(id) {
    const value = parseInt(document.getElementById(id).value) || 0;
    return Math.max(0, Math.min(99, value));
}

function updateSummary() {
    const slimQty = getQtyValue('slim_quantity');
    const roundQty = getQtyValue('round_quantity');
    const slimTotal = slimQty * PRICES.Slim;
    const roundTotal = roundQty * PRICES.Round;
    const total = slimTotal + roundTotal;

    document.getElementById('sumSlimQty').textContent = slimQty ? slimQty + ' items • ₱' + slimTotal.toLocaleString() : '0 items';
    document.getElementById('sumRoundQty').textContent = roundQty ? roundQty + ' items • ₱' + roundTotal.toLocaleString() : '0 items';
    document.getElementById('sumTotal').textContent = '₱' + total.toLocaleString();
}

document.addEventListener('DOMContentLoaded', () => {
    updateSummary();

    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.target;
            const input = document.getElementById(target);
            const step = Number(btn.dataset.step) || 0;
            let value = getQtyValue(target) + step;
            if (value < 0) value = 0;
            if (value > 99) value = 99;
            input.value = value;
            updateSummary();
        });
    });

    ['slim_quantity', 'round_quantity'].forEach(id => {
        const input = document.getElementById(id);
        input.addEventListener('input', () => {
            let v = parseInt(input.value) || 0;
            if (v < 0) v = 0;
            if (v > 99) v = 99;
            input.value = v;
            updateSummary();
        });
    });

    document.getElementById('location').addEventListener('input', () => validateLocation());

    document.getElementById('orderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateForm()) return;

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loader-spinner" style="width:20px;height:20px;border-width:3px"></span> Placing Order...';

        const formData = new FormData(e.target);

        try {
            const res = await fetch('process_order.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                document.getElementById('modalOrderNum').textContent = data.order_number;
                document.getElementById('modalName').textContent = data.name;
                document.getElementById('successModal').classList.add('show');
                e.target.reset();
                updateSummary();
            } else {
                alert('Error: ' + (data.error || 'Something went wrong.'));
            }
        } catch {
            alert('Network error. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '🛒 Place Order';
        }
    });
});

function validateForm() {
    let valid = true;
    const required = ['customer_name', 'location'];
    required.forEach(id => {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            el.classList.add('error');
            valid = false;
        } else {
            el.classList.remove('error');
        }
    });

    const locationAllowed = validateLocation();
    if (!locationAllowed) {
        valid = false;
        if (!document.getElementById('location').value.trim()) {
            const message = document.getElementById('locationMessage');
            message.textContent = 'Please enter a delivery address within our service area.';
            message.classList.add('show');
        }
    }

    const slimQty = getQtyValue('slim_quantity');
    const roundQty = getQtyValue('round_quantity');
    if (slimQty === 0 && roundQty === 0) {
        alert('Please choose at least one gallon quantity.');
        valid = false;
    }

    const phone = document.getElementById('contact_number').value.trim();
    if (phone && !/^09\d{9}$/.test(phone)) {
        document.getElementById('contact_number').classList.add('error');
        valid = false;
    }

    if (!valid) {
        document.querySelector('.form-control.error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return valid;
}

function closeModal() {
    document.getElementById('successModal').classList.remove('show');
}
