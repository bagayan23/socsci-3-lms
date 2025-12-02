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
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside (optional but good for "pop up" feel)
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !burgerMenu.contains(e.target) && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
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

    // --- Input Validation ---
    const numberInputs = document.querySelectorAll('input[data-validate="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    const textInputs = document.querySelectorAll('input[data-validate="text"]');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        });
    });


    // --- File Preview Modal Injection ---
    if (!document.getElementById('file-preview-modal')) {
        const modalHTML = `
        <div id="file-preview-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center;">
            <div style="background:white; padding:20px; width:80%; height:80%; position:relative; display:flex; flex-direction:column; border-radius: 8px;">
                <button id="close-preview" class="btn" style="width: auto; align-self: flex-end; margin-bottom: 10px; background-color: #f44336;">Close</button>
                <div id="preview-content-wrapper" style="flex: 1; overflow: hidden; display: flex; justify-content: center; align-items: center;"></div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        document.getElementById('close-preview').addEventListener('click', function() {
            document.getElementById('file-preview-modal').style.display = 'none';
            document.getElementById('preview-content-wrapper').innerHTML = ''; // Clear content
        });
    }

    // Expose preview function globally
    window.previewFile = function(url) {
        const modal = document.getElementById('file-preview-modal');
        const contentWrapper = document.getElementById('preview-content-wrapper');
        const extension = url.split('.').pop().toLowerCase();

        let content = '';

        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) {
            content = `<img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain;">`;
        } else if (['mp4', 'webm', 'ogg', 'mov'].includes(extension)) {
            content = `<video src="${url}" controls style="max-width:100%; max-height:100%;"></video>`;
        } else if (extension === 'pdf') {
            content = `<iframe src="${url}" style="width:100%; height:100%; border:none;"></iframe>`;
        } else if (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(extension)) {
            // Use Google Docs Viewer
            // Construct absolute URL logic if needed, but for now assuming relative works if public, or just fallback
            const fullUrl = new URL(url, document.baseURI).href;
            content = `<iframe src="https://docs.google.com/gview?url=${encodeURIComponent(fullUrl)}&embedded=true" style="width:100%; height:100%; border:none;"></iframe>`;
        } else {
            content = `<div style="text-align:center; padding:20px;">
                <p>Cannot preview this file type.</p>
                <a href="${url}" download class="btn" style="text-decoration:none;">Download File</a>
            </div>`;
        }

        contentWrapper.innerHTML = content;
        modal.style.display = 'flex';
    };

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

        // Fetch All Provinces initially to allow independent selection if needed
        // Note: This is heavy, but required if we want to allow Province selection without Region selection
        // OR if we want them mutually exclusive.
        // However, fetching all provinces (80+) is manageable.
        let allProvinces = [];
        fetch('https://psgc.gitlab.io/api/provinces/')
            .then(response => response.json())
            .then(data => {
                data.sort((a,b) => a.name.localeCompare(b.name));
                allProvinces = data;
                // If the design was hierarchical, we wouldn't populate province yet.
                // But request says: "select the region has a value make the province disabled, then if the user selected a valaue on province make the region field disable."
                // This implies they can be entry points.
                // So we populate province list initially too?
                // If we do, we lose the filtering by Region.
                // If we keep filtering, we can't select Province without Region.
                // If we select Region, Province gets disabled (per request).
                // So the only way this request makes sense is if they are mutually exclusive search criteria.
                // I will populate provinces here.
                data.forEach(prov => {
                    const option = document.createElement('option');
                    option.value = prov.code;
                    option.textContent = prov.name;
                    provinceSelect.appendChild(option);
                });
            });


        regionSelect.addEventListener('change', function() {
            const regionCode = this.value;

            // Mutual Exclusion Logic
            if (regionCode) {
                provinceSelect.disabled = true;
                provinceSelect.value = ""; // Clear province selection
            } else {
                provinceSelect.disabled = false;
            }

            // Standard Flow (Fetch Cities based on Region if Province is skipped/disabled)
            // If Province is disabled, we must fetch cities for the Region directly (like NCR)
            // or maybe the user implies we stop there?
            // Assuming we need cities:
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (regionCode) {
                 // Try fetching cities directly for the region (common for NCR, but maybe weird for others)
                 fetchCities(regionCode, true);
            }
        });

        provinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;

            // Mutual Exclusion Logic
            if (provinceCode) {
                regionSelect.disabled = true;
                regionSelect.value = ""; // Clear region selection
            } else {
                regionSelect.disabled = false;
            }

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
