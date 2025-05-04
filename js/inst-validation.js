document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.onsubmit = validateForm;

        // Real-time validation
        const inputs = ['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password', 'phone', 'department_id'];
        inputs.forEach(input => {
            const element = document.getElementById(input);
            if (element) {
                element.addEventListener('input', () => validateField(input));
            }
        });
    }
});

function validateForm() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    const phone = document.getElementById('phone').value.trim();


    let isValid = true;

    if (!validateName(firstName)) {
        showError('first_name', 'First Name must contain only letters and be at least 2 characters long.');
        isValid = false;
    }
    if (!validateName(lastName)) {
        showError('last_name', 'Last Name must contain only letters and be at least 2 characters long.');
        isValid = false;
    }
    if (!validateUsername(username)) {
        showError('username', 'Username must be alphanumeric or underscores, and 3-16 characters long.');
        isValid = false;
    }
    if (!validateEmail(email)) {
        showError('email', 'Please enter a valid email address.');
        isValid = false;
    }
    if (!validatePassword(password)) {
        showError('password', 'Password must be at least 8 characters long and include both letters and numbers.');
        isValid = false;
    }
    if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match.');
        isValid = false;
    }
    if (phone && !validatePhone(phone)) {
        showError('phone', 'Phone number must start with "+2519", "+2517", "09", or "07" and be 10 or 13 digits long.');
        isValid = false;
    }

    return isValid;
}

function validateField(field) {
    const value = document.getElementById(field).value.trim();
    switch (field) {
        case 'first_name':
        case 'last_name':
            if (!validateName(value)) {
                showError(field, `${field.replace('_', ' ').toUpperCase()} must contain only letters and be at least 2 characters long.`);
            } else {
                clearError(field);
            }
            break;
        case 'username':
            if (!validateUsername(value)) {
                showError(field, 'Username must be alphanumeric or underscores, and 3-16 characters long.');
            } else {
                clearError(field);
            }
            break;
        case 'email':
            if (!validateEmail(value)) {
                showError(field, 'Please enter a valid email address.');
            } else {
                clearError(field);
            }
            break;
        case 'password':
            if (!validatePassword(value)) {
                showError(field, 'Password must be at least 8 characters long and include both letters and numbers.');
            } else {
                clearError(field);
            }
            break;
        case 'confirm_password':
            const password = document.getElementById('password').value.trim();
            if (value !== password) {
                showError(field, 'Passwords do not match.');
            } else {
                clearError(field);
            }
            break;
        case 'phone':
            if (value && !validatePhone(value)) {
                showError(field, 'Phone number must start with "+2519", "+2517", "09", or "07" and be 10 or 13 digits long.');
            } else {
                clearError(field);
            }
            break;
        
    }
}

function validateName(name) {
    return /^[a-zA-Z\s]{2,}$/.test(name);
}

function validateUsername(username) {
    return /^[a-zA-Z0-9_]{3,16}$/.test(username);
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePassword(password) {
    return /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(password);
}

function validatePhone(phone) {
    return /^(?:\+251|0)?[97]\d{8}$/.test(phone);
}

function showError(field, message) {
    const errorElement = document.getElementById(`${field}_error`);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        document.getElementById(field).classList.add('is-invalid');
    }
}

function clearError(field) {
    const errorElement = document.getElementById(`${field}_error`);
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
        document.getElementById(field).classList.remove('is-invalid');
    }
}


//instructor dashboardfunction toggleSidebar() {
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
    
        if (sidebar.classList.contains('sidebar-open')) {
            sidebar.classList.remove('sidebar-open');
            sidebar.classList.add('sidebar-closed');
            content.style.marginLeft = '0';
        } else {
            sidebar.classList.remove('sidebar-closed');
            sidebar.classList.add('sidebar-open');
            content.style.marginLeft = '250px';
        }
    }