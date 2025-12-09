// Authentication Manager
class AuthManager {
    constructor() {
        this.user = null;
    }

    async login(email, password) {
        try {
            const response = await fetch('/SOCSCI_3/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            
            if (data.success) {
                this.user = data.user;
                return data;
            } else {
                throw new Error(data.error || 'Login failed');
            }
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    async register(formData) {
        try {
            const response = await fetch('/SOCSCI_3/api/auth/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Registration failed');
            }
            
            return data;
        } catch (error) {
            console.error('Registration error:', error);
            throw error;
        }
    }

    async checkSession() {
        try {
            const response = await fetch('/SOCSCI_3/api/auth/session.php');
            const data = await response.json();
            
            if (data.authenticated) {
                this.user = data.user;
                return data.user;
            } else {
                this.user = null;
                return null;
            }
        } catch (error) {
            console.error('Session check error:', error);
            this.user = null;
            return null;
        }
    }

    async logout() {
        try {
            const response = await fetch('/SOCSCI_3/api/auth/logout.php', {
                method: 'POST'
            });

            const data = await response.json();
            this.user = null;
            
            return data;
        } catch (error) {
            console.error('Logout error:', error);
            throw error;
        }
    }

    async requireAuth(requiredRole = null) {
        const user = await this.checkSession();
        
        if (!user) {
            window.location.href = '/index.html';
            return null;
        }

        if (requiredRole && user.role !== requiredRole) {
            window.location.href = '/index.html';
            return null;
        }

        return user;
    }

    getUser() {
        return this.user;
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthManager;
}
