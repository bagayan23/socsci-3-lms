'use strict';

document.addEventListener("DOMContentLoaded", () => {
    // Fetch API example to get data from PHP endpoint
    function fetchData(endpoint) {
        fetch(endpoint)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                // Handle data here
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    // Sidenav toggle functionality
    const sidenav = document.getElementById('sidenav');
    const toggleBtn = document.getElementById('sidenav-toggle');
    
    toggleBtn.addEventListener('click', () => {
        sidenav.classList.toggle('collapsed');
    });

    // Dark mode toggle functionality
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    
    darkModeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
    });

    // Role-specific functionality
    function checkUserRole(role) {
        if (role === 'admin') {
            // Admin specific logic
        } else if (role === 'user') {
            // User specific logic
        }
    }

    // Call the fetchData function with a specific endpoint
    fetchData('/path/to/your/php/endpoint');
    
    // Example: Check user role (this could be set based on user data)
    const userRole = 'admin'; // This should come from your user management system
    checkUserRole(userRole);
});
