<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/auth.css') ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Task Tracker</h2>
                <p>Admin Login</p>
            </div>
            <div class="login-body">
                <form id="loginForm">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div id="loginMessage" class="message-container"></div>
                    
                    <div class="form-actions">
                        <button type="submit" class="login-button">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const messageContainer = document.getElementById('loginMessage');
            
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                // Clear previous messages
                messageContainer.innerHTML = '';
                messageContainer.className = 'message-container';
                
                // Show loading message
                messageContainer.innerHTML = '<div class="loading">Authenticating...</div>';
                
                // Call login API
                fetch('/users/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Check if user is admin
                        if (data.data.role === 'admin') {
                            // Success - redirect to dashboard after storing session
                            fetch('/auth/set-session', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(data.data)
                            })
                            .then(response => response.json())
                            .then(sessionData => {
                                if (sessionData.status) {
                                    window.location.href = '/dashboard';
                                } else {
                                    showError(sessionData.msg || 'Failed to create session');
                                }
                            })
                            .catch(error => {
                                console.error('Session error:', error);
                                showError('Error creating session: ' + error.message);
                            });
                        } else {
                            showError('Access denied. Only administrators can login to this system.');
                        }
                    } else {
                        showError(data.msg || 'Login failed');
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    showError('Login error: ' + error.message);
                });
            });
            
            // Function to show error message
            function showError(message) {
                messageContainer.innerHTML = `<div class="error">${message}</div>`;
                messageContainer.className = 'message-container error-container';
            }
        });
    </script>
</body>
</html>