document.addEventListener('DOMContentLoaded', function() {
    
    // --- Auth Forms Handling ---
    const loginCard = document.getElementById('login-card');
    const signupCard = document.getElementById('signup-card');
    const showSignup = document.getElementById('show-signup');
    const showLogin = document.getElementById('show-login');

    if (showSignup && showLogin) {
        showSignup.addEventListener('click', function(e) {
            e.preventDefault();
            loginCard.classList.add('hidden');
            signupCard.classList.remove('hidden');
        });

        showLogin.addEventListener('click', function(e) {
            e.preventDefault();
            signupCard.classList.add('hidden');
            loginCard.classList.remove('hidden');
        });
    }

    // --- Password Visibility Toggle ---
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            // Simple visual toggle (optional: change SVG path)
            this.style.opacity = type === 'text' ? '1' : '0.6';
        });
    });

    // --- Dynamic Form Fields for Student/Teacher ---
    const roleSelect = document.getElementById('role');
    const studentFields = document.getElementById('student-fields');

    if (roleSelect && studentFields) {
        roleSelect.addEventListener('change', function() {
            if (this.value === 'student') {
                studentFields.classList.remove('hidden');
                setRequired(studentFields, true);
            } else {
                studentFields.classList.add('hidden');
                setRequired(studentFields, false);
            }
        });

        // Trigger on load
        roleSelect.dispatchEvent(new Event('change'));
    }

    function setRequired(container, isRequired) {
        const inputs = container.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.dataset.required === "true") { // Use a data attribute to mark originally required fields
                input.required = isRequired;
            } else if (isRequired) {
                 // If specific fields are mandatory for students only
                 if(['student_id', 'year', 'program', 'section'].includes(input.name)) {
                     input.required = true;
                 }
            } else {
                input.required = false;
            }
        });
    }

    // --- Burger Menu ---
    const burgerMenu = document.querySelector('.burger-menu');
    const sidebar = document.querySelector('.sidebar');
    if (burgerMenu && sidebar) {
        burgerMenu.addEventListener('click', function() {
            sidebar.classList.toggle('closed');
        });
    }

    // --- User Dropdown ---
    const userMenu = document.querySelector('.header-user-menu');
    const dropdown = document.querySelector('.dropdown-menu');
    if (userMenu && dropdown) {
        userMenu.addEventListener('click', function() {
            dropdown.classList.toggle('show');
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    // --- Carousel (Simple JS Implementation) ---
    const carouselItems = document.querySelectorAll('.carousel-item');
    if (carouselItems.length > 0) {
        let currentSlide = 0;
        const slideInterval = 5000;

        function showSlide(index) {
            carouselItems.forEach(item => item.classList.remove('active'));
            carouselItems[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % carouselItems.length;
            showSlide(currentSlide);
        }

        setInterval(nextSlide, slideInterval);
    }

    // --- Address API (PSGC Integration Mock) ---
    // In a real scenario, fetch from https://psgc.gitlab.io/api/regions/
    // Here we will just populate some dummy data or try to fetch if online.
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    if (regionSelect) {
        // Fetch Regions
        fetch('https://psgc.gitlab.io/api/regions/')
            .then(response => response.json())
            .then(data => {
                data.sort((a,b) => a.name.localeCompare(b.name));
                data.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.code;
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
            })
            .catch(err => console.error('Error fetching regions:', err));

        regionSelect.addEventListener('change', function() {
            const regionCode = this.value;
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (regionCode) {
                 fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`)
                    .then(response => response.json())
                    .then(data => {
                         data.sort((a,b) => a.name.localeCompare(b.name));
                         data.forEach(prov => {
                            const option = document.createElement('option');
                            option.value = prov.code;
                            option.textContent = prov.name;
                            provinceSelect.appendChild(option);
                         });
                         // Also fetch cities/munis for special regions like NCR which have no provinces sometimes or act differently
                         // But for simplicity, let's assume standard hierarchy or handle NCR specifically
                         if(data.length === 0) {
                             // Try fetching cities directly for the region (e.g. NCR)
                             fetchCities(regionCode, true); 
                         }
                    });
            }
        });

        provinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;
             citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
             barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
             if(provinceCode) fetchCities(provinceCode, false);
        });

        citySelect.addEventListener('change', function() {
            const cityCode = this.value; // or city/muni code
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            if(cityCode) {
                fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`)
                .then(response => response.json())
                .then(data => {
                    data.sort((a,b) => a.name.localeCompare(b.name));
                    data.forEach(brgy => {
                        const option = document.createElement('option');
                        option.value = brgy.code;
                        option.textContent = brgy.name;
                        barangaySelect.appendChild(option);
                    });
                });
            }
        });

        function fetchCities(code, isRegion) {
            let url = isRegion 
                ? `https://psgc.gitlab.io/api/regions/${code}/cities-municipalities/`
                : `https://psgc.gitlab.io/api/provinces/${code}/cities-municipalities/`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    data.sort((a,b) => a.name.localeCompare(b.name));
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.code;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });
                });
        }
    }
});
