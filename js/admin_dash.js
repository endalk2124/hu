// Toggle Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    sidebar.classList.toggle('collapsed');
    content.classList.toggle('collapsed');
}

/// // Refresh every 5 seconds

// Confirmation Dialogs for Approve/Reject Actions
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('approve-btn')) {
        event.preventDefault();
        const userId = event.target.getAttribute('data-id');
        if (confirm('Are you sure you want to approve this user?')) {
            window.location.href = `approve_user.php?user_id=${userId}&action=approve`;
        }
    }

    if (event.target.classList.contains('reject-btn')) {
        event.preventDefault();
        const userId = event.target.getAttribute('data-id');
        if (confirm('Are you sure you want to reject this user?')) {
            window.location.href = `approve_user.php?user_id=${userId}&action=reject`;
        }
    }
});

// Search and Filter Functionality
document.getElementById('searchInput').addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#pendingUsersTable tbody tr');

    rows.forEach(row => {
        const username = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        if (username.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});