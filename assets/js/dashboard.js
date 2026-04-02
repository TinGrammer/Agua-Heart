document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
    Chart.defaults.color = '#6c757d';

    const gradientBlue = (ctx) => {
        const g = ctx.createLinearGradient(0, 0, 0, 300);
        g.addColorStop(0, 'rgba(0,119,182,0.8)');
        g.addColorStop(1, 'rgba(0,180,216,0.1)');
        return g;
    };

    // Weekly Sales Chart
    const weeklyCtx = document.getElementById('weeklyChart')?.getContext('2d');
    if (weeklyCtx && window.weeklyData) {
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: window.weeklyData.map(d => d.day),
                datasets: [{
                    label: 'Sales (₱)',
                    data: window.weeklyData.map(d => d.sales),
                    backgroundColor: gradientBlue(weeklyCtx),
                    borderColor: '#0077b6',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => '₱' + v.toLocaleString() }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Monthly Sales Chart
    const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
    if (monthlyCtx && window.monthlyData) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: window.monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Sales (₱)',
                    data: window.monthlyData.map(d => d.sales),
                    borderColor: '#0077b6',
                    backgroundColor: gradientBlue(monthlyCtx),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0077b6',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => '₱' + v.toLocaleString() }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Daily Orders Chart
    const dailyCtx = document.getElementById('dailyChart')?.getContext('2d');
    if (dailyCtx && window.dailyData) {
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: window.dailyData.map(d => d.day),
                datasets: [{
                    label: 'Orders',
                    data: window.dailyData.map(d => d.orders),
                    borderColor: '#00b4d8',
                    backgroundColor: 'rgba(0,180,216,0.1)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#00b4d8',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Gallon Type Doughnut
    const typeCtx = document.getElementById('typeChart')?.getContext('2d');
    if (typeCtx && window.typeData) {
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Slim', 'Round'],
                datasets: [{
                    data: [window.typeData.slim, window.typeData.round],
                    backgroundColor: ['#0077b6', '#00b4d8'],
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } }
                }
            }
        });
    }
});

// Sidebar toggle for mobile
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

// Status update
async function updateStatus(id, status) {
    if (!confirm('Update status to ' + status + '?')) return;
    const res = await fetch('ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&id=${id}&status=${status}`
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert('Failed to update status.');
}
