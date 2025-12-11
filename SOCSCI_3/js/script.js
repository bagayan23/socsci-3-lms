document.addEventListener('DOMContentLoaded', function() {

    // --- Auto-wrap tables for responsive scrolling ---
    function wrapTablesForMobile() {
        const tables = document.querySelectorAll('table:not(.wrapped)');
        tables.forEach(table => {
            // Check if table is not already wrapped
            if (!table.parentElement.classList.contains('table-wrapper')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
                table.classList.add('wrapped');
                
                // Add scroll indicator for touch devices
                addScrollIndicator(wrapper);
                wrapper.addEventListener('scroll', () => updateScrollIndicator(wrapper));
            }
        });
    }

    function addScrollIndicator(wrapper) {
        // Check if table is wider than wrapper
        const table = wrapper.querySelector('table');
        if (table && table.offsetWidth > wrapper.offsetWidth) {
            wrapper.setAttribute('data-scrollable', 'true');
            
            // Add touch hint on mobile
            if ('ontouchstart' in window && window.innerWidth < 768) {
                const hint = document.createElement('div');
                hint.className = 'scroll-hint';
                hint.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Swipe to view more';
                hint.style.cssText = `
                    position: absolute;
                    top: 50%;
                    right: 10px;
                    transform: translateY(-50%);
                    background: rgba(99, 102, 241, 0.9);
                    color: white;
                    padding: 0.5rem 1rem;
                    border-radius: 20px;
                    font-size: 0.75rem;
                    pointer-events: none;
                    z-index: 10;
                    animation: fadeOut 3s forwards;
                `;
                wrapper.style.position = 'relative';
                wrapper.appendChild(hint);
                
                // Remove hint after animation
                setTimeout(() => hint.remove(), 3000);
            }
        }
    }

    function updateScrollIndicator(wrapper) {
        const scrollLeft = wrapper.scrollLeft;
        const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
        
        // Add visual feedback when scrolling
        if (scrollLeft > 0) {
            wrapper.classList.add('scrolled-left');
        } else {
            wrapper.classList.remove('scrolled-left');
        }
        
        if (scrollLeft < maxScroll - 5) {
            wrapper.classList.add('has-more-right');
        } else {
            wrapper.classList.remove('has-more-right');
        }
    }

    function checkTableScroll(wrapper) {
        // Function kept for compatibility but no longer manages shadow classes
    }

    // Initial wrap
    wrapTablesForMobile();

    // Re-wrap on dynamic content load
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                wrapTablesForMobile();
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // --- Dynamic Header Height Calculation ---
    const header = document.querySelector('header');
    if (header) {
        const updateHeaderHeight = () => {
            const height = header.offsetHeight;
            document.documentElement.style.setProperty('--header-height', `${height}px`);
        };

        // Initial set
        updateHeaderHeight();

        // Update on resize
        window.addEventListener('resize', updateHeaderHeight);
    }
    
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
            loginCard.style.animation = 'fadeOut 0.3s ease';
            signupCard.style.animation = 'fadeIn 0.3s ease';
        });

        showLogin.addEventListener('click', function(e) {
            e.preventDefault();
            signupCard.classList.add('hidden');
            loginCard.classList.remove('hidden');
            signupCard.style.animation = 'fadeOut 0.3s ease';
            loginCard.style.animation = 'fadeIn 0.3s ease';
        });
    }

    // --- Password Visibility Toggle ---
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (!input) return;
            
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Visual feedback
            this.style.fill = type === 'text' ? 'var(--primary-color)' : '#777';
            this.style.transform = type === 'text' ? 'translateY(-50%) scale(1.1)' : 'translateY(-50%) scale(1)';
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
            if (input.dataset.required === "true") {
                input.required = isRequired;
            } else if (isRequired) {
                if(['student_id', 'year', 'program', 'section'].includes(input.name)) {
                    input.required = true;
                }
            } else {
                input.required = false;
            }
        });
    }

    // --- Form Validation Enhancement ---
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Real-time validation
        const inputs = form.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const formInputs = this.querySelectorAll('.form-control');
            
            formInputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                showAlert('Please fix the errors in the form', 'error', this);
                
                // Scroll to first error
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });

    function validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const name = field.name;
        let isValid = true;
        let errorMessage = '';

        // Clear previous validation
        field.classList.remove('is-invalid', 'is-valid');
        
        // Remove old error message
        const oldError = field.parentElement.querySelector('.invalid-feedback');
        if (oldError) oldError.remove();

        // Check if required
        if (field.required && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        // Email validation
        else if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        // Password validation
        else if (type === 'password' && value && name === 'password') {
            if (value.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters';
            }
        }
        // Contact number validation
        else if (name === 'contact_number' && value) {
            const sanitizedValue = value.replace(/[\s-]/g, '');
            const philippinePhoneRegex = /^(?:\+63|63|0)9\d{9}$/;

            if (!philippinePhoneRegex.test(sanitizedValue)) {
                isValid = false;
                errorMessage = 'Enter a valid Philippine mobile number (e.g., 09123456789)';
            }
        }
        // Student ID validation
        else if (name === 'student_id' && value) {
            const studentIdRegex = /^\d{2}-\d{4}$/;
            if (!studentIdRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format should be 00-0000';
            }
        }

        // Apply validation classes
        if (!isValid) {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = errorMessage;
            field.parentElement.appendChild(errorDiv);
        } else if (value) {
            field.classList.add('is-valid');
        }

        return isValid;
    }

    // Alert system
    function showAlert(message, type = 'info', form = null) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i> ${message}`;
        
        // Remove existing alerts in the form
        if (form) {
            const existingAlerts = form.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Insert at the beginning of the form
            form.insertBefore(alertDiv, form.firstChild);
        } else {
            const container = document.querySelector('.auth-section') || document.querySelector('.main-content');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);
            }
        }
        
        setTimeout(() => {
            alertDiv.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }

    // --- Burger Menu ---
    const burgerMenu = document.querySelector('.burger-menu');
    const sidebar = document.querySelector('.sidebar');
    if (burgerMenu && sidebar) {
        burgerMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside
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

    // --- Flashcards (Stack/Shuffle Effect) ---
    const flashcards = document.querySelectorAll('.flashcard');
    const indicators = document.querySelectorAll('.indicator');
    
    if (flashcards.length > 0) {
        let currentCard = 0;
        const interval = 4000;
        let autoPlayInterval;

        function showCard(index) {
            flashcards.forEach((card, i) => {
                card.classList.remove('active', 'prev');
                if (i === index) {
                    card.classList.add('active');
                }
            });

            // Update indicators
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === index);
            });
        }

        function nextCard() {
            flashcards[currentCard].classList.add('prev');
            flashcards[currentCard].classList.remove('active');

            currentCard = (currentCard + 1) % flashcards.length;

            flashcards[currentCard].classList.remove('prev');
            flashcards[currentCard].classList.add('active');
            
            // Update indicators
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === currentCard);
            });
        }

        function startAutoPlay() {
            stopAutoPlay();
            autoPlayInterval = setInterval(nextCard, interval);
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
            }
        }

        // Indicator click handlers
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', function() {
                stopAutoPlay();
                currentCard = index;
                showCard(index);
                startAutoPlay();
            });
        });

        // Start autoplay
        startAutoPlay();

        // Pause on hover
        const flashcardContainer = document.querySelector('.flashcard-container');
        if (flashcardContainer) {
            flashcardContainer.addEventListener('mouseenter', stopAutoPlay);
            flashcardContainer.addEventListener('mouseleave', startAutoPlay);
        }
    }

    // --- Input Validation ---
    const numberInputs = document.querySelectorAll('input[data-validate="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    });

    const textInputs = document.querySelectorAll('input[data-validate="text"]');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        });
    });


    // --- Grading Validation ---
    window.validateGrade = function(form, maxScore) {
        const gradeInput = form.querySelector('input[name="grade"]');
        const grade = parseFloat(gradeInput.value);

        if (isNaN(grade)) {
            alert('Please enter a valid number');
            return false;
        }

        if (grade < 0) {
            alert('Grade cannot be negative.');
            return false;
        }

        if (grade > maxScore) {
            alert(`Grade cannot exceed the total score of ${maxScore}.`);
            return false;
        }

        return true;
    };

    // --- Table Search & Filter ---
    window.setupTableSearch = function(tableId, searchInputId) {
        const table = document.querySelector(tableId); // Supports class or ID selector
        const searchInput = document.querySelector(searchInputId);

        if (!table || !searchInput) return;

        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    };

    // Initialize searches for known tables if inputs exist
    // We will assume a convention: Input ID = "search-{PageName}"
    const searchInputs = document.querySelectorAll('.search-bar');
    searchInputs.forEach(input => {
        const targetTable = input.dataset.target;
        if (targetTable) {
            setupTableSearch(targetTable, `#${input.id}`);
        }
    });

    // --- File Preview Modal Injection ---
    if (!document.getElementById('file-preview-modal')) {
        const modalHTML = `
        <div id="file-preview-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:2000; justify-content:center; align-items:center; padding:10px;">
            <div style="background:white; padding:15px; width:100%; height:100%; max-width:1200px; max-height:100%; position:relative; display:flex; flex-direction:column; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow:hidden;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:15px; border-bottom: 2px solid #e2e8f0; flex-wrap:wrap; gap:10px; min-height:50px;">
                    <h3 id="preview-filename" style="margin:0; color: var(--primary-color); font-size: 1.1rem; word-break:break-word; flex:1; min-width:0; max-width:100%; overflow:hidden; text-overflow:ellipsis; line-height:1.4;">File Preview</h3>
                    <div style="display:flex; gap:8px; flex-shrink:0; flex-wrap:wrap;">
                        <a id="download-file" class="btn" download style="width: auto; padding: 0.5rem 1rem; background: var(--success-color); text-decoration: none; font-size:0.875rem; display:flex; align-items:center; gap:0.25rem; white-space:nowrap;">
                            <i class="fas fa-download"></i> <span class="btn-text">Download</span>
                        </a>
                        <button id="close-preview" class="btn" style="width: auto; padding: 0.5rem 1rem; background: var(--error-color); font-size:0.875rem; display:flex; align-items:center; gap:0.25rem; white-space:nowrap;">
                            <i class="fas fa-times"></i> <span class="btn-text">Close</span>
                        </button>
                    </div>
                </div>
                <div id="preview-content-wrapper" style="flex: 1; overflow: auto; display: flex; justify-content: center; align-items: center; background: #f8fafc; border-radius: 8px; padding: 15px; min-height:0;"></div>
            </div>
        </div>
        <style>
            @media (max-width: 768px) {
                #file-preview-modal > div {
                    padding: 10px !important;
                    border-radius: 8px !important;
                }
                #file-preview-modal > div > div:first-child {
                    min-height: 60px !important;
                }
                #preview-filename {
                    font-size: 0.95rem !important;
                    max-width: calc(100vw - 200px) !important;
                }
                #download-file, #close-preview {
                    padding: 0.4rem 0.75rem !important;
                    font-size: 0.8rem !important;
                }
                #preview-content-wrapper {
                    padding: 10px !important;
                }
            }
            @media (max-width: 480px) {
                #file-preview-modal {
                    padding: 5px !important;
                }
                #file-preview-modal > div {
                    padding: 8px !important;
                    border-radius: 6px !important;
                }
                #file-preview-modal > div > div:first-child {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                    min-height: auto !important;
                    padding-bottom: 10px !important;
                }
                #preview-filename {
                    font-size: 0.875rem !important;
                    margin-bottom: 8px !important;
                    max-width: 100% !important;
                    white-space: normal !important;
                    display: -webkit-box !important;
                    -webkit-line-clamp: 2 !important;
                    -webkit-box-orient: vertical !important;
                    overflow: hidden !important;
                }
                #file-preview-modal > div > div:first-child > div {
                    width: 100% !important;
                    justify-content: stretch !important;
                }
                #download-file, #close-preview {
                    flex: 1 !important;
                    justify-content: center !important;
                    padding: 0.5rem !important;
                    font-size: 0.8rem !important;
                }
                .btn-text {
                    display: none !important;
                }
                #preview-content-wrapper {
                    padding: 8px !important;
                }
            }
            @media (max-width: 360px) {
                #preview-filename {
                    font-size: 0.8rem !important;
                }
                #download-file, #close-preview {
                    padding: 0.4rem !important;
                    font-size: 0.75rem !important;
                }
            }
        </style>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        document.getElementById('close-preview').addEventListener('click', function() {
            const modal = document.getElementById('file-preview-modal');
            modal.style.display = 'none';
            document.getElementById('preview-content-wrapper').innerHTML = '';
            document.body.style.overflow = 'auto';
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('file-preview-modal');
                if (modal.style.display === 'flex') {
                    document.getElementById('close-preview').click();
                }
            }
        });
    }

    // Enhanced preview function with support for all file types
    window.previewFile = function(url, filename = '') {
        const modal = document.getElementById('file-preview-modal');
        const contentWrapper = document.getElementById('preview-content-wrapper');
        const filenameDisplay = document.getElementById('preview-filename');
        const downloadBtn = document.getElementById('download-file');
        
        // Normalize the file path - handle both relative and absolute paths
        let normalizedUrl = url;
        
        // If the URL is relative and starts with ../, remove one level
        // This is because the JS is called from pages like student/resources.php
        // but the uploads are at ../uploads/ relative to that page
        if (!url.startsWith('http') && !url.startsWith('/')) {
            // Keep the URL as-is since it's already relative to the current page
            normalizedUrl = url;
        }
        
        // Extract filename from URL if not provided
        if (!filename) {
            filename = normalizedUrl.split('/').pop().split('?')[0];
        }
        
        filenameDisplay.textContent = decodeURIComponent(filename);
        downloadBtn.href = normalizedUrl;
        downloadBtn.download = filename;
        
        const extension = filename.split('.').pop().toLowerCase();
        let content = '';

        // Image files
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'].includes(extension)) {
            content = `
                <div style="max-width:100%; max-height:100%; display:flex; justify-content:center; align-items:center; width:100%;">
                    <img src="${url}" class="preview-image" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);" 
                         onerror="this.parentElement.innerHTML='<div style=\\'text-align:center; color:#ef4444; padding:20px;\\'><i class=\\'fas fa-exclamation-circle fa-3x\\'></i><p style=\\'margin-top:15px;\\'>Failed to load image</p></div>'">
                </div>`;
        } 
        // Video files
        else if (['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv'].includes(extension)) {
            content = `
                <video controls class="preview-video" style="max-width:100%; max-height:70vh; border-radius:8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width:100%;"
                       onerror="this.parentElement.innerHTML='<div style=\\'text-align:center; color:#ef4444; padding:20px;\\'><i class=\\'fas fa-exclamation-circle fa-3x\\'></i><p style=\\'margin-top:15px;\\'>Failed to load video</p></div>'">
                    <source src="${url}" type="video/${extension}">
                    Your browser does not support the video tag.
                </video>`;
        } 
        // Audio files
        else if (['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac'].includes(extension)) {
            content = `
                <div style="text-align:center; width:100%; max-width:500px; padding:20px;">
                    <i class="fas fa-music fa-5x" style="color: var(--primary-color); margin-bottom: 2rem;"></i>
                    <h3 style="margin-bottom:1rem; color:#334155;">${filename}</h3>
                    <audio controls style="width:100%; margin-top:1rem;"
                           onerror="this.parentElement.innerHTML='<div style=\\'text-align:center; color:#ef4444; padding:20px;\\'><i class=\\'fas fa-exclamation-circle fa-3x\\'></i><p style=\\'margin-top:15px;\\'>Failed to load audio</p></div>'">
                        <source src="${url}" type="audio/${extension}">
                        Your browser does not support the audio tag.
                    </audio>
                </div>`;
        } 
        // PDF files
        else if (extension === 'pdf') {
            content = `
                <iframe src="${url}#toolbar=1&navpanes=1&scrollbar=1" class="preview-iframe"
                        style="width:100%; height:100%; border:none; border-radius:8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);"
                        onerror="this.parentElement.innerHTML='<div style=\\'text-align:center; color:#ef4444;\\'><i class=\\'fas fa-exclamation-circle fa-3x\\'></i><p>Failed to load PDF. <a href=\\\"${url}\\\" download class=\\'btn\\'>Download instead</a></p></div>'">
                </iframe>`;
        } 
        // Microsoft Office files (Word, Excel, PowerPoint)
        else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension)) {
            const fileIcon = extension.includes('doc') ? 'fa-file-word' : 
                           extension.includes('xls') ? 'fa-file-excel' : 'fa-file-powerpoint';
            const fileColor = extension.includes('doc') ? '#2b579a' : 
                            extension.includes('xls') ? '#217346' : '#d24726';
            const fileType = extension.includes('doc') ? 'Word Document' : 
                           extension.includes('xls') ? 'Excel Spreadsheet' : 'PowerPoint Presentation';
            
            // Show loading state
            content = `
                <div id="office-content" style="width:100%; height:100%; overflow:auto; padding:20px;">
                    <div style="text-align:center; padding:60px 20px;">
                        <i class="fas fa-spinner fa-spin fa-3x" style="color: ${fileColor};"></i>
                        <p style="margin-top:20px; font-size:1.1rem; color:#64748b;">Loading ${fileType}...</p>
                    </div>
                </div>`;
            
            contentWrapper.innerHTML = content;
            
            // Fetch and process the file
            fetch(normalizedUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/octet-stream'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status} - File not found: ${normalizedUrl}`);
                    }
                    return response.arrayBuffer();
                })
                .then(arrayBuffer => {
                    const officeContent = document.getElementById('office-content');
                    
                    if (!officeContent) {
                        console.error('Office content container not found');
                        return;
                    }
                    
                    if (extension === 'docx' || extension === 'doc') {
                        // Word Document
                        if (typeof mammoth !== 'undefined' && extension === 'docx') {
                            // Verify the arrayBuffer is valid
                            if (arrayBuffer.byteLength === 0) {
                                throw new Error('File is empty');
                            }
                            
                            mammoth.convertToHtml({arrayBuffer: arrayBuffer})
                                .then(result => {
                                    if (result.value && result.value.trim().length > 0) {
                                        officeContent.innerHTML = `
                                            <div style="max-width:800px; margin:0 auto; padding:20px; background:white; line-height:1.8; color:#1e293b;">
                                                ${result.value}
                                            </div>`;
                                    } else {
                                        officeContent.innerHTML = `
                                            <div style="text-align:center; padding:40px;">
                                                <i class="fas fa-file-word fa-3x" style="color:#2b579a; margin-bottom:20px;"></i>
                                                <h3 style="color:#334155; margin-bottom:15px;">Document appears to be empty</h3>
                                                <p style="color:#64748b; margin-bottom:20px;">The document was loaded but contains no visible content.</p>
                                                <a href="${url}" download class="btn" style="text-decoration:none; display:inline-block;">
                                                    <i class="fas fa-download"></i> Download Document
                                                </a>
                                            </div>`;
                                    }
                                    
                                    // Log any warnings
                                    if (result.messages && result.messages.length > 0) {
                                        console.warn('Document conversion messages:', result.messages);
                                    }
                                })
                                .catch(err => {
                                    console.error('Mammoth error:', err);
                                    officeContent.innerHTML = `
                                        <div style="text-align:center; padding:40px;">
                                            <i class="fas fa-exclamation-circle fa-3x" style="color:#ef4444; margin-bottom:20px;"></i>
                                            <h3 style="color:#334155; margin-bottom:15px;">Unable to Display Document</h3>
                                            <p style="color:#64748b; margin-bottom:10px;">The document could not be converted for viewing.</p>
                                            <p style="color:#94a3b8; font-size:0.875rem; margin-bottom:20px;">Error: ${err.message}</p>
                                            <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-block;">
                                                <i class="fas fa-download"></i> Download Document
                                            </a>
                                        </div>`;
                                });
                        } else if (extension === 'doc') {
                            // Legacy .doc format - show download option
                            officeContent.innerHTML = `
                                <div style="text-align:center; padding:60px 40px;">
                                    <i class="fas fa-file-word fa-5x" style="color:#2b579a; margin-bottom:30px;"></i>
                                    <h2 style="color:#334155; margin-bottom:15px;">Legacy Word Document (.doc)</h2>
                                    <p style="color:#64748b; margin-bottom:10px; max-width:600px; margin-left:auto; margin-right:auto; line-height:1.6;">
                                        This is a legacy Word document format (.doc). Modern web browsers cannot display .doc files directly.
                                    </p>
                                    <p style="color:#64748b; margin-bottom:30px;">
                                        Please download the file to view it with Microsoft Word or compatible software.
                                    </p>
                                    <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:12px 24px; font-size:1rem;">
                                        <i class="fas fa-download"></i> Download Document
                                    </a>
                                </div>`;
                        } else {
                            officeContent.innerHTML = `
                                <div style="text-align:center; padding:40px;">
                                    <i class="fas ${fileIcon} fa-4x" style="color:${fileColor}; margin-bottom:20px;"></i>
                                    <h3>Word Document</h3>
                                    <p style="color:#64748b; margin:20px 0;">Mammoth.js library not loaded. Please download to view.</p>
                                    <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-block;">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>`;
                        }
                    } else if (extension === 'xlsx' || extension === 'xls') {
                        // Excel Spreadsheet
                        if (typeof XLSX !== 'undefined') {
                            try {
                                // Verify the arrayBuffer is valid
                                if (arrayBuffer.byteLength === 0) {
                                    throw new Error('File is empty');
                                }
                                
                                const workbook = XLSX.read(arrayBuffer, {type: 'array'});
                                let html = '<div style="padding:10px;">';
                                
                                if (!workbook.SheetNames || workbook.SheetNames.length === 0) {
                                    throw new Error('No sheets found in workbook');
                                }
                                
                                workbook.SheetNames.forEach((sheetName, index) => {
                                    const worksheet = workbook.Sheets[sheetName];
                                    html += `<h2 style="color:#217346; margin:30px 0 15px 0; padding:10px; background:#f0fdf4; border-left:4px solid #217346; font-size:1.2rem;">
                                        <i class="fas fa-table"></i> ${sheetName}
                                    </h2>`;
                                    html += '<div style="overflow-x:auto; margin-bottom:30px;">';
                                    const tableHtml = XLSX.utils.sheet_to_html(worksheet, {editable: false});
                                    if (tableHtml && tableHtml.trim().length > 0) {
                                        html += tableHtml;
                                    } else {
                                        html += '<p style="color:#64748b; padding:20px;">This sheet is empty</p>';
                                    }
                                    html += '</div>';
                                });
                                
                                html += '</div>';
                                officeContent.innerHTML = html;
                                
                                // Style the tables
                                const tables = officeContent.querySelectorAll('table');
                                tables.forEach(table => {
                                    table.style.width = '100%';
                                    table.style.borderCollapse = 'collapse';
                                    table.style.fontSize = '0.9rem';
                                    table.style.background = 'white';
                                    
                                    const cells = table.querySelectorAll('th, td');
                                    cells.forEach(cell => {
                                        cell.style.border = '1px solid #e2e8f0';
                                        cell.style.padding = '10px';
                                        cell.style.textAlign = 'left';
                                    });
                                    
                                    const headers = table.querySelectorAll('th');
                                    headers.forEach(th => {
                                        th.style.background = '#f8fafc';
                                        th.style.fontWeight = '600';
                                        th.style.color = '#334155';
                                    });
                                    
                                    const rows = table.querySelectorAll('tr');
                                    rows.forEach((row, idx) => {
                                        if (idx > 0) { // Skip header
                                            row.style.transition = 'background 0.2s';
                                            row.addEventListener('mouseenter', function() {
                                                this.style.background = '#f8fafc';
                                            });
                                            row.addEventListener('mouseleave', function() {
                                                this.style.background = 'white';
                                            });
                                        }
                                    });
                                });
                            } catch (err) {
                                console.error('XLSX error:', err);
                                officeContent.innerHTML = `
                                    <div style="text-align:center; padding:40px;">
                                        <i class="fas fa-exclamation-circle fa-3x" style="color:#ef4444; margin-bottom:20px;"></i>
                                        <h3>Unable to Display Spreadsheet</h3>
                                        <p style="color:#64748b; margin:10px 0;">The spreadsheet could not be processed.</p>
                                        <p style="color:#94a3b8; font-size:0.875rem; margin-bottom:20px;">Error: ${err.message}</p>
                                        <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-block;">
                                            <i class="fas fa-download"></i> Download Spreadsheet
                                        </a>
                                    </div>`;
                            }
                        } else {
                            officeContent.innerHTML = `
                                <div style="text-align:center; padding:40px;">
                                    <i class="fas ${fileIcon} fa-4x" style="color:${fileColor}; margin-bottom:20px;"></i>
                                    <h3>Excel Spreadsheet</h3>
                                    <p style="color:#64748b; margin:20px 0;">SheetJS library not loaded. Please download to view.</p>
                                    <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-block;">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>`;
                        }
                    } else if (extension === 'pptx' || extension === 'ppt') {
                        // PowerPoint - show download option with format-specific messaging
                        const formatMessage = extension === 'ppt' ? 
                            'This is a legacy PowerPoint format (.ppt). ' : 
                            '';
                        
                        officeContent.innerHTML = `
                            <div style="text-align:center; padding:60px 40px;">
                                <i class="fas fa-file-powerpoint fa-5x" style="color:#d24726; margin-bottom:30px;"></i>
                                <h2 style="color:#334155; margin-bottom:15px;">PowerPoint Presentation ${extension === 'ppt' ? '(.ppt)' : '(.pptx)'}</h2>
                                <p style="color:#64748b; margin-bottom:10px; max-width:600px; margin-left:auto; margin-right:auto; line-height:1.6;">
                                    ${formatMessage}PowerPoint presentations contain complex layouts, animations, transitions, and multimedia elements 
                                    that require Microsoft PowerPoint or compatible software to view properly.
                                </p>
                                <p style="color:#64748b; margin-bottom:30px;">
                                    Please download the file to view the full presentation with all its features.
                                </p>
                                <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:12px 24px; font-size:1rem;">
                                    <i class="fas fa-download"></i> Download Presentation
                                </a>
                            </div>`;
                    } else {
                        // Should not reach here, but just in case
                        officeContent.innerHTML = `
                            <div style="text-align:center; padding:40px;">
                                <i class="fas fa-file-alt fa-3x" style="color:#64748b; margin-bottom:20px;"></i>
                                <h3>Office Document</h3>
                                <p style="color:#64748b; margin:20px 0;">Please download the file to view its contents.</p>
                                <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-block;">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>`;
                    }
                })
                .catch(err => {
                    console.error('File fetch error:', err);
                    const officeContent = document.getElementById('office-content');
                    if (officeContent) {
                        officeContent.innerHTML = `
                            <div style="text-align:center; padding:40px;">
                                <i class="fas fa-exclamation-circle fa-3x" style="color:#ef4444; margin-bottom:20px;"></i>
                                <h3>Error Loading File</h3>
                                <p style="color:#64748b; margin:10px 0;">Unable to fetch the file for viewing.</p>
                                <p style="color:#94a3b8; font-size:0.875rem; margin-bottom:20px;">Error: ${err.message}</p>
                                <p style="color:#64748b; margin-bottom:20px;">This might be due to:</p>
                                <ul style="color:#64748b; text-align:left; max-width:400px; margin:0 auto 30px auto; line-height:1.8;">
                                    <li>File permissions</li>
                                    <li>Network connectivity</li>
                                    <li>File path issues</li>
                                    <li>Browser security restrictions</li>
                                </ul>
                                <a href="${normalizedUrl}" download class="btn" style="text-decoration:none; display:inline-block;">
                                    <i class="fas fa-download"></i> Try Downloading Instead
                                </a>
                            </div>`;
                    }
                });
            
            // Don't set contentWrapper.innerHTML again, we already did it above
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            return; // Exit early to prevent the default contentWrapper.innerHTML below
        } 
        // Text files
        else if (['txt', 'log', 'csv', 'json', 'xml', 'md', 'html', 'css', 'js', 'php', 'py', 'java', 'cpp', 'c', 'h'].includes(extension)) {
            // Fetch and display text content
            fetch(url)
                .then(response => response.text())
                .then(text => {
                    const previewText = text.length > 50000 ? text.substring(0, 50000) + '\\n\\n... (File truncated, download to view full content)' : text;
                    contentWrapper.innerHTML = `
                        <div style="width:100%; height:100%; overflow:auto; background:white; padding:20px; border-radius:8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                            <pre style="margin:0; white-space:pre-wrap; word-wrap:break-word; font-family:monospace; font-size:0.875rem; line-height:1.5;">${escapeHtml(previewText)}</pre>
                        </div>`;
                })
                .catch(err => {
                    contentWrapper.innerHTML = `<div style="text-align:center; color:#ef4444;"><i class="fas fa-exclamation-circle fa-3x"></i><p>Failed to load text file</p></div>`;
                });
            content = '<div style="text-align:center;"><i class="fas fa-spinner fa-spin fa-3x" style="color: var(--primary-color);"></i><p>Loading...</p></div>';
        }
        // ZIP/Archive files
        else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(extension)) {
            content = `
                <div style="text-align:center; padding:40px;">
                    <i class="fas fa-file-archive fa-5x" style="color: var(--primary-color); margin-bottom:2rem;"></i>
                    <h3 style="color: var(--text-color); margin-bottom:1rem;">Archive File</h3>
                    <p style="color:#64748b; margin-bottom:2rem;">This is a compressed archive. Download to extract and view contents.</p>
                    <a href="${url}" download class="btn" style="text-decoration:none; display:inline-block; width:auto; padding:0.75rem 2rem;">
                        <i class="fas fa-download"></i> Download Archive
                    </a>
                </div>`;
        }
        // Other/Unknown file types
        else {
            content = `
                <div style="text-align:center; padding:40px;">
                    <i class="fas fa-file fa-5x" style="color: var(--primary-color); margin-bottom:2rem;"></i>
                    <h3 style="color: var(--text-color); margin-bottom:1rem;">File Preview Not Available</h3>
                    <p style="color:#64748b; margin-bottom:1rem;">File type: .${extension}</p>
                    <p style="color:#64748b; margin-bottom:2rem;">This file type cannot be previewed in the browser. Download to view.</p>
                    <a href="${url}" download class="btn" style="text-decoration:none; display:inline-block; width:auto; padding:0.75rem 2rem;">
                        <i class="fas fa-download"></i> Download File
                    </a>
                </div>`;
        }

        contentWrapper.innerHTML = content;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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

    // --- View Profile Picture Function ---
    window.viewProfilePicture = function(imageUrl) {
        const modal = document.getElementById('file-preview-modal');
        const contentWrapper = document.getElementById('preview-content-wrapper');
        const filenameDisplay = document.getElementById('preview-filename');
        const downloadBtn = document.getElementById('download-file');
        
        filenameDisplay.textContent = 'Profile Picture';
        downloadBtn.href = imageUrl;
        downloadBtn.download = 'profile_picture.jpg';
        
        const content = `
            <div style="max-width:100%; max-height:100%; display:flex; justify-content:center; align-items:center;">
                <img src="${imageUrl}" style="max-width:90%; max-height:90%; object-fit:contain; border-radius:12px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);" 
                     onerror="this.parentElement.innerHTML='<div style=\\'text-align:center; color:#ef4444;\\'><i class=\\'fas fa-exclamation-circle fa-3x\\'></i><p>Failed to load profile picture</p></div>'">
            </div>`;
        
        contentWrapper.innerHTML = content;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };
});
