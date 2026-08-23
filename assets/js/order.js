const PRICES = { Slim: 25, Round: 30 };

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
    const required = ['customer_name', 'contact_number', 'location'];
    required.forEach(id => {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            el.classList.add('error');
            valid = false;
        } else {
            el.classList.remove('error');
        }
    });

    const slimQty = getQtyValue('slim_quantity');
    const roundQty = getQtyValue('round_quantity');
    if (slimQty === 0 && roundQty === 0) {
        alert('Please choose at least one gallon quantity.');
        valid = false;
    }

    const phone = document.getElementById('contact_number').value.trim();
    if (phone && !/^[0-9+\-\s()]{7,15}$/.test(phone)) {
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
