// Debug helper
function debugViews() {
    const views = document.querySelectorAll('.view');
    console.log('All views:', views.length);
    views.forEach(view => {
        console.log('View ID:', view.id, 'Active:', view.classList.contains('active'));
    });
}

// View management
function showView(viewId) {
    console.log('Showing view:', viewId);
    document.querySelectorAll('.view').forEach(view => {
        view.classList.remove('active');
    });
    const targetView = document.getElementById(viewId + '-view');
    if (targetView) {
        targetView.classList.add('active');
        
        // Scroll to top of the page
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        
        // If showing my-items view, load the content
        if (viewId === 'my-items') {
            showMyItems();
        }
        
        // If showing home view, load announcements and donation gallery
        if (viewId === 'home') {
            // Load announcements first, which will then trigger loading donation gallery
            loadHomeAnnouncements();
            
            // In case loadHomeAnnouncements fails to call loadDonationGallery
            setTimeout(() => {
                const galleryContainer = document.getElementById('donation-categories');
                if (galleryContainer && galleryContainer.children.length === 0) {
                    loadDonationGallery();
                }
            }, 1000);
        }
    } else {
        console.error('View not found:', viewId + '-view');
    }
}

// Modal management
function openDonateModal() {
    const modal = document.getElementById('donate-modal');
    modal.style.display = 'block';
    
    // Reset scroll position when opening the modal
    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.scrollTop = 0;
    }
    
    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('donate-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
}

function closeSuccessModal() {
    document.getElementById('success-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
}

function openTermsModal() {
    document.getElementById('terms-modal').style.display = 'block';
    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

function closeTermsModal() {
    document.getElementById('terms-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
}

function closeLoginSuccessModal() {
    document.getElementById('login-success-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
}

function closeLoginErrorModal() {
    document.getElementById('login-error-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
}

function closeRegisterErrorModal() {
    document.getElementById('register-error-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
}

function closeRegisterSuccessModal() {
    document.getElementById('register-success-modal').style.display = 'none';
    // Re-enable body scrolling when modal is closed
    document.body.style.overflow = '';
    // Redirect to sign in view
    showView('signin');
}

// FAQ toggle function
function toggleFAQ(id) {
    const faqContent = document.getElementById(`faq-${id}`);
    const faqIcon = document.getElementById(`faq-icon-${id}`);
    
    // Toggle the visibility of the FAQ content
    if (faqContent.classList.contains('hidden')) {
        faqContent.classList.remove('hidden');
        faqIcon.classList.add('rotate-180');
        
        // Ensure proper display after showing content
        setTimeout(() => {
            faqContent.style.maxHeight = faqContent.scrollHeight + "px";
        }, 10);
    } else {
        faqContent.style.maxHeight = null;
        faqContent.classList.add('hidden');
        faqIcon.classList.remove('rotate-180');
    }
}

// Navigation functions
function login() {
    showView('signin');
}

function showRegistrationForm() {
    console.log('Showing registration form');
    showView('register');
}

function showSignInForm() {
    showView('signin');
}

// Form handling
async function handleSignIn(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    const result = await loginUser(email, password);
    
    if (result.success) {
        // Store user info in session
        sessionStorage.setItem('user', JSON.stringify(result.user));
        
        // Show success modal instead of alert
        const username = result.user.username || email.split('@')[0];
        document.getElementById('login-success-message').textContent = `Welcome back, ${username}!`;
        document.getElementById('login-success-modal').style.display = 'block';
        
        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
        
        // Update navigation for logged-in user
        updateNavigation(true);
        
        // After a short delay, redirect to home view
        setTimeout(() => {
            showView('home');
        }, 500);
    } else {
        // Show error modal instead of alert
        document.getElementById('login-error-message').textContent = result.message || 'Unable to sign in. Please check your credentials.';
        document.getElementById('login-error-modal').style.display = 'block';
        
        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
    }
}

async function handleRegistration(event) {
    event.preventDefault();
    
    const username = document.getElementById('reg-username').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;

    // Validate email domain
    if (!email.endsWith('@ua.edu.ph')) {
        // Show error modal instead of alert
        document.getElementById('register-error-message').textContent = 'Please use a UA.edu.ph email address';
        document.getElementById('register-error-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
        return;
    }
    
    const result = await registerUser(username, email, password);
    
    if (result.success) {
        // Show success modal instead of alert
        document.getElementById('register-success-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    } else {
        // Show error modal instead of alert
        document.getElementById('register-error-message').textContent = result.message || 'Registration failed';
        document.getElementById('register-error-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function updateNavigation(isLoggedIn) {
    const user = isLoggedIn ? JSON.parse(sessionStorage.getItem('user')) : null;
    const signInButton = document.querySelector('button[onclick="login()"]');
    const adminDashboardBtn = document.getElementById('admin-dashboard-btn');
    const myItemsBtn = document.querySelector('button[onclick="location.href=\'my_donations.html\'"]');
    
    // Update hero section button
    const heroButton = document.getElementById('hero-button');
    
    if (isLoggedIn && user) {
        signInButton.textContent = 'Sign Out';
        signInButton.onclick = logout;
        
        // Update hero button when logged in
        if (heroButton) {
            heroButton.textContent = 'Donate Now';
            heroButton.onclick = function() { openDonateModal(); };
        }
        
        // Show My Items button only for regular logged-in users (not admins)
        if (myItemsBtn) {
            if (user.is_admin && user.email === 'admin@ua.edu.ph') {
                // Hide My Items button for admin users
                myItemsBtn.classList.add('hidden');
            } else {
                // Show My Items button for regular users
                myItemsBtn.classList.remove('hidden');
            }
        }
        
        // Show admin dashboard button only for admin users
        if (user.is_admin && user.email === 'admin@ua.edu.ph') {
            adminDashboardBtn.classList.remove('hidden');
        } else {
            adminDashboardBtn.classList.add('hidden');
        }
    } else {
        signInButton.textContent = 'Sign In';
        signInButton.onclick = login;
        
        // Reset hero button when logged out
        if (heroButton) {
            heroButton.textContent = 'Sign In with UA Email';
            heroButton.onclick = login;
        }
        
        // Hide My Items button for logged out users
        if (myItemsBtn) {
            myItemsBtn.classList.add('hidden');
        }
        adminDashboardBtn.classList.add('hidden');
    }
}

function logout() {
    // Show confirmation popup
    const confirmModal = document.createElement('div');
    confirmModal.className = 'modal';
    confirmModal.style.display = 'block';
    confirmModal.style.zIndex = '1000';
    
    confirmModal.innerHTML = `
        <div class="modal-content" style="max-width: 400px; padding: 20px; text-align: center;">
            <h3 class="text-xl font-bold mb-4">Sign Out Confirmation</h3>
            <p class="mb-6">Are you sure you want to sign out?</p>
            <div class="flex justify-center space-x-4">
                <button id="confirm-logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Sign Out</button>
                <button id="cancel-logout" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancel</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmModal);
    
    // Handle confirmation
    document.getElementById('confirm-logout').addEventListener('click', function() {
        sessionStorage.removeItem('user');
        updateNavigation(false);
        showView('home');
        confirmModal.remove();
    });
    
    // Handle cancellation
    document.getElementById('cancel-logout').addEventListener('click', function() {
        confirmModal.remove();
    });
    
    // Close when clicking outside
    confirmModal.addEventListener('click', function(event) {
        if (event.target === confirmModal) {
            confirmModal.remove();
        }
    });
}

// Check if user is already logged in on page load
window.onload = function() {
    const user = sessionStorage.getItem('user');
    if (user) {
        updateNavigation(true);
    } else {
        updateNavigation(false);
    }
    
    // Ensure home content loads on initial page load
    const homeView = document.getElementById('home-view');
    if (homeView && homeView.classList.contains('active')) {
        // Load announcements and donation gallery
        loadHomeAnnouncements();
        loadDonationGallery();
    }
    
    // Debug initial state
    debugViews();
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}

// Category mapping
const categoryMap = {
    'micro': 1,        // Microcontrollers and Boards
    'sensors': 2,      // Sensors and Modules
    'wires': 3,        // Wires and Connectors
    'power': 4,        // Power Supply
    'display': 5,      // Display Screens
    'prototype': 6,    // Prototyping Materials
    'components': 7,   // Electronic Components
    'cables': 8,       // Cables and Adapters
    'projects': 9,     // Past Projects
    'tools': 10        // Tools and Equipment
};

async function submitDonation(event) {
    event.preventDefault();
    
    // Check if user is logged in
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user) {
        alert('Please sign in to submit a donation');
        showView('signin');
        return;
    }
    
    // Get form data
    const title = document.getElementById('item-name').value;
    const type = document.getElementById('item-type').value;
    const condition = document.getElementById('item-condition').value;
    const description = document.getElementById('item-description').value;
    const fileInput = document.getElementById('file-upload');
    const videoInput = document.getElementById('video-upload');
    const files = fileInput.files;
    const videoFile = videoInput.files.length > 0 ? videoInput.files[0] : null;

    // Get category ID from mapping
    const categoryId = categoryMap[type];
    if (!categoryId) {
        alert('Invalid item type selected');
        return;
    }

    // First create the donation
    const donationResult = await createDonation(
        user.id,
        categoryId,
        title,
        description,
        condition
    );

    if (!donationResult.success) {
        alert(donationResult.message || 'Failed to create donation');
        return;
    }

    let uploadSuccessful = true;

    // If there are image files to upload
    if (files.length > 0) {
        const formData = new FormData();
        formData.append('donation_id', donationResult.donation_id);
        
        // Append each image file
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        try {
            const uploadResponse = await fetch('api/upload.php', {
                method: 'POST',
                body: formData
            });
            
            const uploadResult = await uploadResponse.json();
            
            if (!uploadResult.success) {
                console.error('Image upload errors:', uploadResult.errors);
                uploadSuccessful = false;
            }
        } catch (error) {
            console.error('Image upload error:', error);
            uploadSuccessful = false;
        }
    }

    // If there's a video file to upload
    if (videoFile) {
        const videoFormData = new FormData();
        videoFormData.append('donation_id', donationResult.donation_id);
        videoFormData.append('video', videoFile);

        try {
            const uploadResponse = await fetch('api/upload_video.php', {
                method: 'POST',
                body: videoFormData
            });
            
            const uploadResult = await uploadResponse.json();
            
            if (!uploadResult.success) {
                console.error('Video upload errors:', uploadResult.errors);
                uploadSuccessful = false;
            }
        } catch (error) {
            console.error('Video upload error:', error);
            uploadSuccessful = false;
        }
    }

    // Show appropriate message based on upload success
    if (!uploadSuccessful) {
        alert('Donation created but some files failed to upload.');
    }

    // Show success message
    closeModal();
    document.getElementById('success-modal').style.display = 'block';
    
    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

// Add drag and drop functionality
function setupFileUpload() {
    setupImageUpload();
    setupVideoUpload();
}

// Set up image upload with drag and drop
function setupImageUpload() {
    const dropZones = document.querySelectorAll('.border-dashed');
    const imageDropZone = dropZones[0]; // First drop zone for images
    const fileInput = document.getElementById('file-upload');

    if (!imageDropZone || !fileInput) return;

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        imageDropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        imageDropZone.addEventListener(eventName, highlight.bind(null, imageDropZone), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        imageDropZone.addEventListener(eventName, unhighlight.bind(null, imageDropZone), false);
    });

    // Handle dropped files
    imageDropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        // Filter only image files
        const imageFiles = Array.from(files).filter(file => 
            file.type.match('image/jpeg') || 
            file.type.match('image/png') || 
            file.type.match('image/jpg')
        );
        
        if (imageFiles.length > 0) {
            // Create a new FileList-like object with only image files
            const dataTransfer = new DataTransfer();
            imageFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
            updateFileList(fileInput.files, imageDropZone);
        }
    });

    // Handle file input change
    fileInput.addEventListener('change', function(e) {
        updateFileList(this.files, imageDropZone);
    });
}

// Set up video upload with drag and drop
function setupVideoUpload() {
    const dropZones = document.querySelectorAll('.border-dashed');
    if (dropZones.length < 2) return; // Ensure we have at least 2 drop zones
    
    const videoDropZone = dropZones[1]; // Second drop zone for video
    const videoInput = document.getElementById('video-upload');

    if (!videoDropZone || !videoInput) return;

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        videoDropZone.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        videoDropZone.addEventListener(eventName, highlight.bind(null, videoDropZone), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        videoDropZone.addEventListener(eventName, unhighlight.bind(null, videoDropZone), false);
    });

    // Handle dropped files
    videoDropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        // Filter only video files
        const videoFiles = Array.from(files).filter(file => 
            file.type.match('video/mp4') || 
            file.type.match('video/webm') || 
            file.type.match('video/ogg')
        );
        
        if (videoFiles.length > 0) {
            // Take only the first video file
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(videoFiles[0]);
            videoInput.files = dataTransfer.files;
            updateVideoFile(videoInput.files[0], videoDropZone);
        }
    });

    // Handle video input change
    videoInput.addEventListener('change', function(e) {
        if (this.files && this.files.length > 0) {
            updateVideoFile(this.files[0], videoDropZone);
        }
    });
}

// Helper functions for both upload types
function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(dropZone, e) {
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
}

function unhighlight(dropZone, e) {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
}

function updateFileList(files, dropZone) {
    const fileList = document.createElement('div');
    fileList.className = 'mt-2 space-y-2 file-list';
    
    Array.from(files).forEach(file => {
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center text-sm text-gray-600';
        fileItem.innerHTML = `
            <i class="fas fa-file-image mr-2"></i>
            <span>${file.name}</span>
            <span class="ml-2">(${(file.size / 1024).toFixed(1)} KB)</span>
        `;
        fileList.appendChild(fileItem);
    });

    // Replace any existing file list in this dropzone
    const existingList = dropZone.querySelector('.file-list');
    if (existingList) {
        existingList.remove();
    }
    
    dropZone.appendChild(fileList);
}

// Update UI to show selected video file
function updateVideoFile(file, dropZone) {
    const videoInfo = document.createElement('div');
    videoInfo.className = 'mt-2 space-y-2 video-info';
    
    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
    
    videoInfo.innerHTML = `
        <div class="flex items-center text-sm text-gray-600">
            <i class="fas fa-file-video mr-2"></i>
            <span>${file.name}</span>
            <span class="ml-2">(${sizeInMB} MB)</span>
        </div>
    `;
    
    // Replace any existing video info
    const existingInfo = dropZone.querySelector('.video-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    dropZone.appendChild(videoInfo);
}

// Initialize file upload when page loads
window.addEventListener('load', function() {
    setupFileUpload();
});

async function showMyItems() {
    console.log('showMyItems called');
    const user = JSON.parse(sessionStorage.getItem('user'));
    console.log('User from session:', user);
    
    const container = document.getElementById('my-items-content');
    if (!container) {
        console.error('Could not find my-items-content element');
        return;
    }
    
    container.innerHTML = '<p class="text-center">Loading your donations...</p>';
    
    if (!user) {
        console.log('No user found in session');
        container.innerHTML = `
            <div class="text-center">
                <i class="fas fa-user-lock text-5xl text-gray-300 mb-4"></i>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Please Sign In</h2>
                <p class="text-gray-600 mb-4">You need to be logged in to view your donated items.</p>
                <button onclick="login()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                    Sign In Now
                </button>
            </div>
        `;
        return;
    }

    try {
        console.log(`Fetching donations for user ID: ${user.id}`);
        const url = `api/get_user_donations.php?user_id=${user.id}`;
        console.log('API URL:', url);
        
        const response = await fetch(url);
        console.log('API response status:', response.status);
        const data = await response.json();
        console.log('API response data:', data);
        
        if (data.success && data.donations.length > 0) {
            let html = '<div class="grid grid-cols-1 gap-6">';
            
            data.donations.forEach(donation => {
                const statusColor = {
                    'pending': 'text-yellow-600',
                    'accepted': 'text-green-600',
                    'declined': 'text-red-600'
                }[donation.status] || 'text-gray-600';
                
                html += `
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-grow">
                                <h3 class="text-xl font-bold mb-2">${donation.title}</h3>
                                <p class="text-gray-600">Category: ${donation.category}</p>
                                <p class="text-gray-600">Condition: ${donation.condition}</p>
                                <p class="mt-2">${donation.description}</p>
                                
                                <div class="mt-4">
                                    <p class="font-semibold">Status: <span class="${statusColor} capitalize">${donation.status}</span></p>
                                    ${donation.reviewed_at ? `
                                        <p class="text-sm text-gray-500">Reviewed on: ${new Date(donation.reviewed_at).toLocaleDateString()}</p>
                                    ` : ''}
                                    ${donation.admin_notes ? `
                                        <div class="mt-2 p-3 bg-gray-50 rounded">
                                            <p class="font-semibold">Admin Notes:</p>
                                            <p class="text-gray-700">${donation.admin_notes}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                            ${donation.image_path ? `
                                <div class="ml-4">
                                    <img src="${donation.image_path}" alt="Donation Image" class="w-32 h-32 object-cover rounded">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            console.log('Rendered donations:', data.donations.length);
        } else {
            console.log('No donations found or API error');
            container.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">No Donations Yet</h2>
                    <p class="text-gray-600 mb-4">You haven't made any donations yet.</p>
                    <button onclick="showView('donate')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                        Make Your First Donation
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading donations:', error);
        container.innerHTML = `
            <div class="text-center text-red-600">
                <p>Error loading donations. Please try again later.</p>
                <p class="text-sm mt-2">${error.message}</p>
            </div>
        `;
    }
    console.log('showMyItems completed');
}

// Load announcements for homepage
async function loadHomeAnnouncements() {
    try {
        const announcementsContainer = document.getElementById('homepage-announcements');
        if (!announcementsContainer) {
            console.error('Announcements container not found');
            return;
        }
        
        // Show loading indicator
        announcementsContainer.innerHTML = `
            <div class="col-span-full text-center p-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading announcements...</p>
            </div>
        `;
        
        // Get the base URL
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        
        // Fetch only active announcements
        const response = await fetch(`${baseUrl}/api/get_announcements.php?active=1`);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.announcements && data.announcements.length > 0) {
            // Clear the container
            announcementsContainer.innerHTML = '';
            
            // Add each announcement
            data.announcements.forEach(announcement => {
                const formattedDate = new Date(announcement.created_at).toLocaleDateString();
                
                announcementsContainer.innerHTML += `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">${announcement.title}</h3>
                                    <p class="text-sm text-gray-500 mt-1">Posted on ${formattedDate}</p>
                                    <p class="mt-3">${announcement.content}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            // No announcements or error
            announcementsContainer.innerHTML = `
                <div class="col-span-full bg-white rounded-lg shadow-md p-6 text-center">
                    <p class="text-gray-500">No announcements available at this time.</p>
                </div>
            `;
        }
        
        // Load the donation gallery after announcements are loaded
        loadDonationGallery();
    } catch (error) {
        console.error('Error loading announcements:', error);
        document.getElementById('homepage-announcements').innerHTML = `
            <div class="col-span-full bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-red-500">Failed to load announcements. Please try again later.</p>
            </div>
        `;
        
        // Still try to load the donation gallery even if announcements fail
        loadDonationGallery();
    }
}

// Load and display donations in the gallery
async function loadDonationGallery() {
    try {
        const galleryContainer = document.getElementById('donation-categories');
        if (!galleryContainer) {
            console.error('Donation gallery container not found');
            return;
        }
        
        // Get the base URL
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        
        // Fetch only accepted donations
        const response = await fetch(`${baseUrl}/api/get_all_donations.php?status=accepted`);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.donations && data.donations.length > 0) {
            // Group donations by category
            const categorizedDonations = {};
            
            data.donations.forEach(donation => {
                // Double check that we only include accepted donations
                if (donation.status.toLowerCase() === 'accepted') {
                    if (!categorizedDonations[donation.category]) {
                        categorizedDonations[donation.category] = [];
                    }
                    categorizedDonations[donation.category].push(donation);
                }
            });
            
            // Clear the container
            galleryContainer.innerHTML = '';
            
            // Check if we have any categories with donations after filtering
            const hasAcceptedDonations = Object.keys(categorizedDonations).length > 0;
            
            if (!hasAcceptedDonations) {
                galleryContainer.innerHTML = `
                    <div class="bg-white rounded-lg shadow-md p-8 text-center">
                        <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No accepted donations available at this time.</p>
                    </div>
                `;
                return;
            }
            
            // Add each category and its items
            for (const category in categorizedDonations) {
                // Skip empty categories
                if (categorizedDonations[category].length === 0) continue;
                
                let categoryHtml = `
                    <div class="category-section" data-category="${category.toLowerCase().replace(/\s+/g, '-')}">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">${category}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                `;
                
                // Add items in this category
                categorizedDonations[category].forEach(item => {
                    // Use the donation's image if available, otherwise use a placeholder
                    let imageUrl = 'img/placeholder.png';
                    let hasVideo = false;
                    let videoThumbnail = false;
                    
                    // Check all possible image path locations
                    if (item.image_path) {
                        imageUrl = item.image_path;
                    } 
                    else if (item.images && item.images.length > 0) {
                        // Check for different possible field names for the image path
                        const firstImage = item.images[0];
                        if (firstImage.file_path) {
                            imageUrl = firstImage.file_path;
                        } 
                        else if (firstImage.filepath) {
                            imageUrl = firstImage.filepath;
                        }
                        else if (firstImage.path) {
                            imageUrl = firstImage.path;
                        }
                        else if (firstImage.url) {
                            imageUrl = firstImage.url;
                        }
                        // If we still don't have a path but have a filename, construct a path
                        else if (firstImage.filename || firstImage.file_name) {
                            const filename = firstImage.filename || firstImage.file_name;
                            imageUrl = `uploads/${filename}`;
                        }
                    }
                    // If no images, check for videos
                    else if (item.videos && item.videos.length > 0) {
                        hasVideo = true;
                        videoThumbnail = true;
                        // Keep imageUrl as placeholder, but we'll show a video icon
                    }
                    else if (item.video_path) {
                        hasVideo = true;
                        videoThumbnail = true;
                    }
                    
                    // Debug log to see what we're working with
                    console.log('Donation ID:', item.id, 'Image URL:', imageUrl, 'Has Video:', hasVideo, 'Status:', item.status, 'Item data:', item);
                    
                    // Create a truncated description (max 50 characters)
                    const shortDescription = item.description ? 
                        (item.description.length > 50 ? item.description.substring(0, 50) + '...' : item.description) : 
                        'No description available';
                    
                    // Get status color and badge
                    const statusBadge = getStatusBadge(item.status);
                    
                    // Map category to component filter value
                    let componentValue = '';
                    if (category.toLowerCase().includes('microcontroller')) componentValue = 'microcontrollers';
                    else if (category.toLowerCase().includes('sensor')) componentValue = 'sensors';
                    else if (category.toLowerCase().includes('wire') || category.toLowerCase().includes('connector')) componentValue = 'wires';
                    else if (category.toLowerCase().includes('power')) componentValue = 'power';
                    else if (category.toLowerCase().includes('display')) componentValue = 'display';
                    else if (category.toLowerCase().includes('prototyping')) componentValue = 'prototyping';
                    else if (category.toLowerCase().includes('electronic component')) componentValue = 'components';
                    else if (category.toLowerCase().includes('cable') || category.toLowerCase().includes('adapter')) componentValue = 'cables';
                    else if (category.toLowerCase().includes('project')) componentValue = 'projects';
                    else if (category.toLowerCase().includes('tool') || category.toLowerCase().includes('equipment')) componentValue = 'tools';
                    else componentValue = category.toLowerCase().replace(/\s+/g, '-');
                    
                    categoryHtml += `
                        <div class="donation-card" data-id="${item.id}" data-title="${item.title.toLowerCase()}" data-donor="${item.donor_name.toLowerCase()}" data-category="${componentValue}" data-status="${item.status.toLowerCase()}">
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer bg-white">
                                <div class="h-48 overflow-hidden relative">
                                    <img src="${imageUrl}" alt="${item.title}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                    ${item.images && item.images.length > 1 ? 
                                        `<span class="absolute bottom-2 right-2 bg-gray-800 bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                                            +${item.images.length - 1} more
                                        </span>` : ''}
                                    ${videoThumbnail ? 
                                        `<div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40">
                                            <div class="rounded-full bg-white bg-opacity-80 p-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>` : ''}
                                    ${statusBadge}
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-800 mb-1 truncate">${item.title}</h4>
                                    <p class="text-sm text-gray-500 mb-2 line-clamp-2">${shortDescription}</p>
                                    <div class="flex justify-between items-center">
                                        <p class="text-xs text-gray-600">Donor: ${item.donor_name}</p>
                                        <span class="text-xs text-blue-600 hover:underline">View Details</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                categoryHtml += `
                        </div>
                    </div>
                `;
                
                galleryContainer.innerHTML += categoryHtml;
            }
            
            // Add click event to donation cards to show details
            document.querySelectorAll('.donation-card').forEach(card => {
                card.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    showDonationDetails(itemId);
                });
            });
            
        } else {
            // No donations or error
            galleryContainer.innerHTML = `
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No accepted donations available at this time.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading donation gallery:', error);
        const galleryContainer = document.getElementById('donation-categories');
        if (galleryContainer) {
            galleryContainer.innerHTML = `
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <p class="text-red-500">Failed to load donation gallery. Please try again later.</p>
                </div>
            `;
        }
    }
}

// Search donated items function
function searchDonatedItems() {
    const searchTerm = document.getElementById('donation-search').value.toLowerCase();
    const allCards = document.querySelectorAll('.donation-card');
    const categorySections = document.querySelectorAll('.category-section');
    
    // If no search term, show all
    if (!searchTerm) {
        allCards.forEach(card => {
            card.style.display = 'block';
        });
        
        categorySections.forEach(section => {
            section.style.display = 'block';
        });
        return;
    }
    
    // Count visible items per category
    const visibleInCategory = {};
    
    // Filter cards
    allCards.forEach(card => {
        const title = card.getAttribute('data-title');
        const donor = card.getAttribute('data-donor');
        const category = card.getAttribute('data-category');
        
        // Check if the search term matches any of our component filter values
        const isComponentMatch = category.includes(searchTerm);
        
        // Check for matches in title, donor, or category
        if (title.includes(searchTerm) || 
            donor.includes(searchTerm) || 
            isComponentMatch) {
            card.style.display = 'block';
            
            // Track which category this card belongs to
            const categorySection = card.closest('.category-section');
            const categoryName = categorySection.getAttribute('data-category');
            
            if (!visibleInCategory[categoryName]) {
                visibleInCategory[categoryName] = 1;
            } else {
                visibleInCategory[categoryName]++;
            }
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide category sections based on whether they have visible items
    categorySections.forEach(section => {
        const categoryName = section.getAttribute('data-category');
        section.style.display = visibleInCategory[categoryName] ? 'block' : 'none';
    });
}

// Show donation details in a modal
async function showDonationDetails(itemId) {
    console.log('Showing details for donation ID:', itemId);
    
    // Show the modal
    const modal = document.getElementById('donation-details-modal');
    if (!modal) {
        console.error('Donation details modal not found');
        return;
    }
    
    modal.style.display = 'block';
    
    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
    
    // Store donor email globally for the message donor button
    window.currentDonorEmail = '';
    
    // Set loading state
    document.getElementById('modal-donation-content').innerHTML = `
        <div class="text-center col-span-full p-6">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading donation details...</p>
        </div>
    `;
    
    // Set loading state for status history
    document.getElementById('status-timeline').innerHTML = `
        <div class="text-center p-4">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading status history...</p>
        </div>
    `;
    
    try {
        // Get the base URL
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        
        // Fetch the donation details
        const response = await fetch(`${baseUrl}/api/get_donation_details.php?id=${itemId}`);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.donation) {
            const donation = data.donation;
            
            // Store donor email for messaging
            window.currentDonorEmail = donation.donor_email || '';
            
            // Set title
            document.getElementById('modal-donation-title').textContent = donation.title;
            
            // Build media gallery - prioritize images, but show videos if no images
            let mediaGallery = '';
            
            if (donation.images && donation.images.length > 0) {
                // If we have images, show them
                const firstImage = donation.images[0].file_path;
                
                mediaGallery = `
                    <div class="md:col-span-1">
                        <div class="rounded-lg overflow-hidden border border-gray-200 mb-4">
                            <img src="${firstImage}" alt="${donation.title}" class="w-full h-64 object-cover">
                        </div>
                        
                        ${donation.images.length > 1 ? `
                            <div class="grid grid-cols-4 gap-2">
                                ${donation.images.slice(0, 4).map(img => `
                                    <div class="h-16 rounded overflow-hidden border border-gray-200">
                                        <img src="${img.file_path}" alt="${img.file_name}" class="w-full h-full object-cover cursor-pointer hover:opacity-80"
                                            onclick="openImageViewer('${img.file_path}')">
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                `;
            } 
            else if (donation.videos && donation.videos.length > 0) {
                // If no images but we have videos, show the first video
                const firstVideo = donation.videos[0].file_path;
                
                mediaGallery = `
                    <div class="md:col-span-1">
                        <div class="rounded-lg overflow-hidden border border-gray-200 mb-4">
                            <video controls class="w-full h-64 object-cover">
                                <source src="${firstVideo}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        
                        ${donation.videos.length > 1 ? `
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-1">Additional videos available</p>
                            </div>
                        ` : ''}
                    </div>
                `;
            }
            else if (donation.video_path) {
                // If we have a single video path
                mediaGallery = `
                    <div class="md:col-span-1">
                        <div class="rounded-lg overflow-hidden border border-gray-200 mb-4">
                            <video controls class="w-full h-64 object-cover">
                                <source src="${donation.video_path}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                `;
            }
            else {
                // No images or videos, show placeholder
                mediaGallery = `
                    <div class="md:col-span-1">
                        <div class="rounded-lg overflow-hidden border border-gray-200">
                            <img src="img/placeholder.png" alt="No image available" class="w-full h-64 object-cover">
                        </div>
                    </div>
                `;
            }
            
            // Build info panel
            const infoPanel = `
                <div class="md:col-span-1">
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold">Description</h4>
                            <p class="text-gray-700">${donation.description}</p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold">Item Details</h4>
                            <p class="text-gray-700">Category: ${donation.category}</p>
                            <p class="text-gray-700">Condition: ${donation.condition}</p>
                            <p class="text-gray-700">Current Status: <span class="font-medium ${getStatusColorClass(donation.status)}">${donation.status}</span></p>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="font-semibold">Donor Information</h4>
                            <p class="text-gray-700">Name: ${donation.donor_name}</p>
                            <p class="text-gray-700">Email: <a href="mailto:${donation.donor_email}" class="text-blue-600 hover:underline">${donation.donor_email}</a></p>
                            <div class="mt-4">
                                <button onclick="messageDonor()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center">
                                    <i class="fas fa-envelope mr-2"></i> Message Donor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Combine all sections
            document.getElementById('modal-donation-content').innerHTML = mediaGallery + infoPanel;
            
            // Fetch and display status history
            fetchStatusHistory(itemId);
            
        } else {
            document.getElementById('modal-donation-content').innerHTML = `
                <div class="col-span-full text-center p-6">
                    <div class="text-red-500">
                        <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                        <p>Failed to load donation details.</p>
                    </div>
                </div>
            `;
            
            // Show error for status history as well
            document.getElementById('status-timeline').innerHTML = `
                <div class="text-center p-4">
                    <div class="text-red-500">
                        <p>Failed to load status history.</p>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading donation details:', error);
        document.getElementById('modal-donation-content').innerHTML = `
            <div class="col-span-full text-center p-6">
                <div class="text-red-500">
                    <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                    <p>Error loading donation details: ${error.message}</p>
                </div>
            </div>
        `;
        
        // Show error for status history as well
        document.getElementById('status-timeline').innerHTML = `
            <div class="text-center p-4">
                <div class="text-red-500">
                    <p>Error loading status history.</p>
                </div>
            </div>
        `;
    }
}

// Helper function to get color class based on status
function getStatusColorClass(status) {
    switch(status.toLowerCase()) {
        case 'pending':
            return 'text-yellow-600';
        case 'accepted':
            return 'text-green-600';
        case 'declined':
            return 'text-red-600';
        case 'delivered':
            return 'text-blue-600';
        default:
            return 'text-gray-600';
    }
}

// Fetch and display status history for a donation
async function fetchStatusHistory(donationId) {
    try {
        // Get the base URL
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        
        // Fetch status history
        const response = await fetch(`${baseUrl}/api/get_status_history.php?donation_id=${donationId}`);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            const history = data.history;
            
            if (history.length === 0) {
                document.getElementById('status-timeline').innerHTML = `
                    <p class="text-gray-500 text-center py-4">No status history available.</p>
                `;
                return;
            }
            
            // Build the timeline HTML
            let timelineHTML = `
                <div class="border-l-2 border-gray-200 ml-3 pl-8 space-y-6">
            `;
            
            history.forEach((item, index) => {
                const date = new Date(item.created_at);
                const formattedDate = date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                
                const formattedTime = date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const statusColorClass = getStatusColorClass(item.status);
                
                timelineHTML += `
                    <div class="relative">
                        <div class="absolute -left-10 mt-1.5 w-4 h-4 rounded-full ${getStatusDotColor(item.status)} border-2 border-white"></div>
                        <div>
                            <p class="font-medium ${statusColorClass}">${item.status}</p>
                            <p class="text-sm text-gray-500">${formattedDate} at ${formattedTime}</p>
                            ${item.comment ? `<p class="text-gray-700 mt-1">${item.comment}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-1">Updated by: ${item.created_by_name || 'System'}</p>
                        </div>
                    </div>
                `;
            });
            
            timelineHTML += `</div>`;
            
            document.getElementById('status-timeline').innerHTML = timelineHTML;
            
        } else {
            document.getElementById('status-timeline').innerHTML = `
                <p class="text-gray-500 text-center py-4">Failed to load status history.</p>
            `;
        }
    } catch (error) {
        console.error('Error loading status history:', error);
        document.getElementById('status-timeline').innerHTML = `
            <p class="text-red-500 text-center py-4">Error loading status history: ${error.message}</p>
        `;
    }
}

// Helper function to get dot color based on status
function getStatusDotColor(status) {
    switch(status.toLowerCase()) {
        case 'pending':
            return 'bg-yellow-500';
        case 'accepted':
            return 'bg-green-500';
        case 'declined':
            return 'bg-red-500';
        case 'delivered':
            return 'bg-blue-500';
        default:
            return 'bg-gray-500';
    }
}

// Close the donation details modal
function closeDonationDetailsModal() {
    const modal = document.getElementById('donation-details-modal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scrolling when modal is closed
        document.body.style.overflow = '';
    }
}

// Image viewer for donation images
function openImageViewer(imagePath) {
    // Remove any existing image viewer
    const existingViewer = document.getElementById('image-viewer-modal');
    if (existingViewer) {
        document.body.removeChild(existingViewer);
    }
    
    // Create new viewer
    const viewerModal = document.createElement('div');
    viewerModal.id = 'image-viewer-modal';
    viewerModal.className = 'fixed inset-0 bg-black bg-opacity-80 z-50 flex items-center justify-center';
    
    viewerModal.innerHTML = `
        <div class="relative max-w-4xl max-h-[90vh] mx-4">
            <img src="${imagePath}" class="max-h-[90vh] max-w-full object-contain" alt="Donation image">
            <button class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(viewerModal);
    
    // Close button functionality
    viewerModal.querySelector('button').addEventListener('click', () => {
        document.body.removeChild(viewerModal);
    });
    
    // Close on background click
    viewerModal.addEventListener('click', (e) => {
        if (e.target === viewerModal) {
            document.body.removeChild(viewerModal);
        }
    });
}

// Helper function to get status badge based on status
function getStatusBadge(status) {
    let badgeClass = '';
    let badgeText = status;
    
    switch(status.toLowerCase()) {
        case 'pending':
            badgeClass = 'bg-yellow-100 text-yellow-800';
            break;
        case 'accepted':
            badgeClass = 'bg-green-100 text-green-800';
            break;
        case 'declined':
            badgeClass = 'bg-red-100 text-red-800';
            break;
        case 'delivered':
            badgeClass = 'bg-blue-100 text-blue-800';
            break;
        default:
            badgeClass = 'bg-gray-100 text-gray-800';
    }
    
    return `<span class="absolute top-2 left-2 ${badgeClass} text-xs font-medium px-2.5 py-0.5 rounded-full">${badgeText}</span>`;
}

// Filter donations by component
function filterByComponent() {
    const componentFilter = document.getElementById('component-filter').value;
    const searchInput = document.getElementById('donation-search');
    
    // If "all" is selected, just clear the search input and show all donations
    if (componentFilter === 'all') {
        searchInput.value = '';
        searchDonatedItems();
        return;
    }
    
    // Set the search input to the selected component to trigger filtering
    searchInput.value = componentFilter;
    searchDonatedItems();
}

// Open Gmail compose window to message the donor
function messageDonor(email) {
    // Use passed email or fallback to stored email
    email = email || window.currentDonorEmail;
    
    if (!email) {
        alert('Donor email is not available.');
        return;
    }
    
    const subject = 'Regarding your donation on E-Donate';
    const body = 'Hello,\n\nI am interested in your donation on E-Donate. Could you please provide more information?\n\nThank you.';
    
    // Create Gmail compose URL with pre-filled fields
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    
    // Open in a new window
    window.open(gmailUrl, '_blank');
}