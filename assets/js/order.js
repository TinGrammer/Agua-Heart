const PRICES = { Slim: 25, Round: 30 };

function updateSummary() {
    const type = document.querySelector('input[name="gallon_type"]:checked')?.value || 'Slim';
    const qty = parseInt(document.getElementById('quantity').value) || 1;
    const price = PRICES[type];
    const total = price * qty;

    document.getElementById('sumType').textContent = type + ' Gallon';
    document.getElementById('sumQty').textContent = qty;
    document.getElementById('sumPrice').textContent = '₱' + price + ' each';
    document.getElementById('sumTotal').textContent = '₱' + total.toLocaleString();
}

function changeQty(delta) {
    const input = document.getElementById('quantity');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    input.value = val;
    updateSummary();
}

document.addEventListener('DOMContentLoaded', () => {
    updateSummary();

    document.querySelectorAll('input[name="gallon_type"]').forEach(r => {
        r.addEventListener('change', updateSummary);
    });

    document.getElementById('quantity').addEventListener('input', () => {
        let v = parseInt(document.getElementById('quantity').value);
        if (isNaN(v) || v < 1) document.getElementById('quantity').value = 1;
        if (v > 99) document.getElementById('quantity').value = 99;
        updateSummary();
    });

    // Form submission
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
