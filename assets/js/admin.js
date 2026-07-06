/**
 * AromaLuxe Admin Dashboard Panel Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Order Status Updates
    const statusSelects = document.querySelectorAll('.admin-order-status-select');
    if (statusSelects) {
        statusSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                const orderId = e.currentTarget.getAttribute('data-order-id');
                const newStatus = e.currentTarget.value;

                fetch('orders.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_status&order_id=${orderId}&order_status=${newStatus}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Show alert
                        alert("Order Status updated to " + newStatus);
                        // Refresh badge colors
                        e.currentTarget.className = `form-select form-select-sm admin-order-status-select badge-status-${newStatus.toLowerCase().replace(/\s/g, '-')}`;
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(err => console.error("Admin order update failed:", err));
            });
        });
    }

    // 2. Toggle Sidebar responsive menu
    const sidebarToggle = document.getElementById('sidebarToggleBtn');
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('d-none');
        });
    }
});
