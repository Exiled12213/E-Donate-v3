// API Base URL
const API_URL = 'api';

// User registration
async function registerUser(username, email, password) {
    try {
        const response = await fetch(`${API_URL}/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username,
                email,
                password
            })
        });

        return await response.json();
    } catch (error) {
        console.error('Registration error:', error);
        return {
            success: false,
            message: 'Registration failed: Network error'
        };
    }
}

// User login
async function loginUser(email, password) {
    try {
        const response = await fetch(`${API_URL}/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email,
                password
            })
        });

        const result = await response.json();
        console.log('Login response:', result); // Debug log
        
        // If login is successful, store the user data
        if (result.success) {
            const userData = {
                ...result.user,
                is_admin: result.user.is_admin // Use the is_admin value from the server directly
            };
            console.log('Storing user data:', userData); // Debug log
            sessionStorage.setItem('user', JSON.stringify(userData));
            
            // Update navigation immediately
            const adminDashboardBtn = document.getElementById('admin-dashboard-btn');
            if (adminDashboardBtn) {
                if (userData.is_admin && userData.email === 'admin@ua.edu.ph') {
                    console.log('Showing admin dashboard button'); // Debug log
                    adminDashboardBtn.classList.remove('hidden');
                } else {
                    console.log('Hiding admin dashboard button'); // Debug log
                    adminDashboardBtn.classList.add('hidden');
                }
            }
        }

        return result;
    } catch (error) {
        console.error('Login error:', error);
        return {
            success: false,
            message: 'Login failed: Network error'
        };
    }
}

// Create donation
async function createDonation(userId, categoryId, title, description, condition) {
    try {
        console.log('Creating donation with:', { userId, categoryId, title, description, condition });
        const response = await fetch(`${API_URL}/create_donation.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                category_id: categoryId,
                title,
                description,
                condition
            })
        });
        
        const result = await response.json();
        console.log('Donation creation result:', result);
        return result;
    } catch (error) {
        console.error('Error creating donation:', error);
        return { success: false, message: 'Failed to create donation: ' + error.message };
    }
}

// Function to check if user is logged in
function isLoggedIn() {
    return sessionStorage.getItem('user') !== null;
}

// Function to get current user
function getCurrentUser() {
    const userStr = sessionStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
}

// Example usage in your forms:
/*
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    const result = await registerUser(username, email, password);
    if (result.success) {
        alert('Registration successful!');
    } else {
        alert('Registration failed: ' + result.message);
    }
});
*/ 