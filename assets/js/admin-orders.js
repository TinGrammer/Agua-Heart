document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('paymentModal');
    const form = document.getElementById('paymentForm');
    const orderIdInput = document.getElementById('paymentOrderId');
    const paymentTypeInput = document.getElementById('paymentType');
    const paymentAmountInput = document.getElementById('paymentAmount');

    const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('.payment-edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            if (!modal || !form || !orderIdInput || !paymentTypeInput || !paymentAmountInput) return;

            orderIdInput.value = button.dataset.orderId || '';
            paymentTypeInput.value = button.dataset.paymentType || '';
            paymentAmountInput.value = button.dataset.amount || '';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            paymentTypeInput.focus();
        });
    });

    document.querySelectorAll('[data-close-payment]').forEach(button => {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const id = orderIdInput.value;
        const paymentType = paymentTypeInput.value.trim();
        const amount = paymentAmountInput.value.trim();

        if (!id) {
            alert('Please select an order first.');
            return;
        }

        if (paymentType && (!amount || Number(amount) < 0 || !/^\d+(?:\.\d{1,2})?$/.test(amount))) {
            alert('Please enter a valid amount for the selected payment type.');
            return;
        }

        if (!paymentType && amount) {
            alert('Choose a payment type before saving an amount.');
            return;
        }

        const body = new URLSearchParams({
            action: 'update_payment',
            id,
            payment_type: paymentType,
            amount: paymentType ? amount : ''
        });

        try {
            const response = await fetch('ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            });

            const data = await response.json();
            if (!data.success) {
                alert(data.error || 'Payment update failed.');
                return;
            }

            window.location.reload();
        } catch (error) {
            alert('Network error while updating payment.');
        }
    });
});
