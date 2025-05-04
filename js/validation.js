// Function to validate the form
function validateForm() {
    const form = document.getElementById('registrationForm');
    const firstName = form.first_name.value.trim();
    const lastName = form.last_name.value.trim();
    const username = form.username.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value.trim();
    const confirmPassword = form.confirm_password.value.trim();
    const phone = form.phone.value.trim();
    const department = form.department_id.value;

    // Regular expressions for validation
    const nameRegex = /^[a-zA-Z\s]{2,}$/; // Only letters and spaces, min 2 characters
    const usernameRegex = /^[a-zA-Z0-9_]{3,16}$/; // Alphanumeric and underscores, 3-16 characters
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email format
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/; // Min 8 chars, at least one letter and one number
    const phoneRegex = /^(?:\+251|0)?[97]\d{8}$/; // Ethiopian phone number format

    // Clear previous errors
    clearErrors();

    let isValid = true;

    // Validation checks
    if (!nameRegex.test(firstName)) {
        showError('first_name', "First Name must contain only letters and be at least 2 characters long.");
        isValid = false;
    }
    if (!nameRegex.test(lastName)) {
        showError('last_name', "Last Name must contain only letters and be at least 2 characters long.");
        isValid = false;
    }
    if (!usernameRegex.test(username)) {
        showError('username', "Username must be alphanumeric or underscores, and 3-16 characters long.");
        isValid = false;
    }
    if (!emailRegex.test(email)) {
        showError('email', "Please enter a valid email address.");
        isValid = false;
    }
    if (!passwordRegex.test(password)) {
        showError('password', "Password must be at least 8 characters long and include both letters and numbers.");
        isValid = false;
    }
    if (password !== confirmPassword) {
        showError('confirm_password', "Passwords do not match.");
        isValid = false;
    }
    if (phone && !phoneRegex.test(phone)) {
        showError('phone', "Phone number must start with '+2519', '+2517', '09', or '07' and be 10 or 13 digits long.");
        isValid = false;
    }
    if (!department || department === "") {
        showError('department_id', "Please select a department.");
        isValid = false;
    }

    // If all validations pass
    return isValid;
}

// Real-time validation
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.onsubmit = function () {
            return validateForm();
        };

        // Add real-time validation listeners
        const inputs = ['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password', 'phone', 'department_id'];
        inputs.forEach(input => {
            const element = document.getElementById(input);
            if (element) {
                element.addEventListener('input', () => {
                    validateField(input);
                });
            }
        });
    }
});

// Validate individual field
function validateField(field) {
    const value = document.getElementById(field).value.trim();
    switch (field) {
        case 'first_name':
        case 'last_name':
            const nameRegex = /^[a-zA-Z\s]{2,}$/;
            if (!nameRegex.test(value)) {
                showError(field, `${field.replace('_', ' ').toUpperCase()} must contain only letters and be at least 2 characters long.`);
            } else {
                clearError(field);
            }
            break;
        case 'username':
            const usernameRegex = /^[a-zA-Z0-9_]{3,16}$/;
            if (!usernameRegex.test(value)) {
                showError(field, "Username must be alphanumeric or underscores, and 3-16 characters long.");
            } else {
                clearError(field);
            }
            break;
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                showError(field, "Please enter a valid email address.");
            } else {
                clearError(field);
            }
            break;
        case 'password':
            const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
            if (!passwordRegex.test(value)) {
                showError(field, "Password must be at least 8 characters long and include both letters and numbers.");
            } else {
                clearError(field);
            }
            break;
        case 'confirm_password':
            const password = document.getElementById('password').value.trim();
            if (value !== password) {
                showError(field, "Passwords do not match.");
            } else {
                clearError(field);
            }
            break;
        case 'phone':
            const phoneRegex = /^(?:\+251|0)?[97]\d{8}$/;
            if (value && !phoneRegex.test(value)) {
                showError(field, "Phone number must start with '+2519', '+2517', '09', or '07' and be 10 or 13 digits long.");
            } else {
                clearError(field);
            }
            break;
        case 'department_id':
            if (!value || value === "") {
                showError(field, "Please select a department.");
            } else {
                clearError(field);
            }
            break;
    }
}
// Add async username check
async function checkUsernameAvailability(username) {
    const response = await fetch(`/check-username?username=${username}`);
    return response.json();
}
// Add eye icon to toggle password visibility
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}
// Show error message
function showError(field, message) {
    const errorElement = document.getElementById(`${field}_error`);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        document.getElementById(field).classList.add('is-invalid');
    }
}

// Clear error message
function clearError(field) {
    const errorElement = document.getElementById(`${field}_error`);
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
        document.getElementById(field).classList.remove('is-invalid');
    }
}

// Clear all errors
function clearErrors() {
    const inputs = ['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password', 'phone', 'department_id'];
    inputs.forEach(input => {
        clearError(input);
    });
}