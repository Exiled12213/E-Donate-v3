// Check if user is logged in as admin
document.addEventListener('DOMContentLoaded', function() {
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.is_admin || user.email !== 'admin@ua.edu.ph') {
        console.log('Access denied: Not an admin user');
        window.location.href = 'index.html';
        return;
    }
    
    document.getElementById('admin-email').textContent = user.email;
    
    // Load categories for the filter dropdowns
    loadCategories();
    
    // Load data for each tab
    loadPendingDonations();
    loadAllDonations();
    loadActiveUsers();
    loadBlockedUsers();
    loadAnnouncements();
});

// Tab Management
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    document.getElementById(tabId).classList.remove('hidden');
    
    // Refresh scrollable areas in the newly shown tab
    setTimeout(() => {
        const scrollableAreas = document.querySelectorAll(`#${tabId} .scrollable-content`);
        scrollableAreas.forEach(area => {
            // Force recalculation of scroll area
            area.style.display = 'none';
            setTimeout(() => {
                area.style.display = 'block';
            }, 0);
        });
    }, 100);
    
    // Refresh data when switching to a tab
    if (tabId === 'all-donations') {
        loadAllDonations();
    } else if (tabId === 'pending-donations') {
        loadPendingDonations();
    } else if (tabId === 'user-management') {
        loadActiveUsers();
    } else if (tabId === 'blocked-users') {
        loadBlockedUsers();
    }
}

// Global variables for donation status handling
let currentDonationId = null;
let currentDonationStatus = null;

// Show donation status modal
function showDonationStatusModal(donationId, status) {
    currentDonationId = donationId;
    currentDonationStatus = status;
    
    const modal = document.getElementById('donation-status-modal');
    const title = document.getElementById('donation-status-title');
    const notesLabel = document.getElementById('status-notes-label');
    const notesInput = document.getElementById('status-notes');
    
    // Update modal content based on status
    if (status === 'accepted') {
        title.textContent = 'Accept Donation';
        notesLabel.textContent = 'Add any notes for the donor (optional):';
    } else if (status === 'declined') {
        title.textContent = 'Decline Donation';
        notesLabel.textContent = 'Please provide a reason for declining:';
    }
    
    // Clear previous notes
    notesInput.value = '';
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Set up confirm button
    const confirmButton = document.getElementById('confirm-status-action');
    confirmButton.onclick = () => submitDonationStatus();
}

// Close donation status modal
function closeDonationStatusModal() {
    const modal = document.getElementById('donation-status-modal');
    modal.classList.add('hidden');
    currentDonationId = null;
    currentDonationStatus = null;
}

// Submit donation status update
async function submitDonationStatus() {
    const notes = document.getElementById('status-notes').value;
    
    if (currentDonationStatus === 'declined' && !notes) {
        alert('Please provide a reason for declining the donation.');
        return;
    }

    // Get current admin user
    const user = JSON.parse(sessionStorage.getItem('user'));
    const adminId = user ? user.id : null;

    try {
        const response = await fetch('api/update_donation_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                donation_id: currentDonationId,
                status: currentDonationStatus,
                admin_notes: notes || '',
                admin_id: adminId
            })
        });

        const data = await response.json();
        if (data.success) {
            // Show success message in modal
            const modal = document.getElementById('donation-status-modal');
            const modalContent = modal.querySelector('.bg-white');
            modalContent.innerHTML = `
                <div class="text-center p-6">
                    <div class="text-green-500 text-5xl mb-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Success!</h3>
                    <p class="text-gray-600">Donation ${currentDonationStatus} successfully!</p>
                    <button onclick="closeDonationStatusModal(); loadPendingDonations(); loadAllDonations();" 
                            class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Close
                    </button>
                </div>
            `;
        } else {
            alert('Failed to update donation status: ' + data.message);
            closeDonationStatusModal();
        }
    } catch (error) {
        console.error('Error updating donation:', error);
        alert('Failed to update donation status. Please try again.');
        closeDonationStatusModal();
    }
}

// Handle donation approval/rejection
async function handleDonation(donationId, status) {
    showDonationStatusModal(donationId, status);
}

// Pending Donations
async function loadPendingDonations() {
    try {
        const response = await fetch('api/get_pending_donations.php');
        const data = await response.json();
        
        const container = document.getElementById('pending-donations-list');
        container.innerHTML = '';
        
        if (data.success && data.donations.length > 0) {
            data.donations.forEach(donation => {
                container.innerHTML += `
                    <div class="border rounded p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg">${donation.title}</h3>
                                <p class="text-gray-600">Category: ${donation.category}</p>
                                <p class="text-gray-600">Condition: ${donation.condition}</p>
                                <p class="text-gray-600">From: ${donation.donor_name}</p>
                                <p class="text-sm text-gray-500">Submitted: ${new Date(donation.created_at).toLocaleDateString()}</p>
                                ${donation.image_path ? `
                                    <div class="mt-2">
                                        <img src="${donation.image_path}" alt="Donation Image" class="w-32 h-32 object-cover rounded">
                                    </div>
                                ` : ''}
                                <p class="mt-2">${donation.description}</p>
                            </div>
                            <div class="flex flex-col space-y-2">
                                <button onclick="viewDonationDetails(${donation.id})" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    View Details
                                </button>
                                <button onclick="handleDonation(${donation.id}, 'accepted')" 
                                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Accept
                                </button>
                                <button onclick="handleDonation(${donation.id}, 'declined')" 
                                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            container.innerHTML = '<p class="text-gray-500">No pending donations found.</p>';
        }
    } catch (error) {
        console.error('Error loading donations:', error);
    }
}

// User Management - Active Users
async function loadActiveUsers() {
    try {
        const response = await fetch('api/get_users.php?is_blocked=0');
        const data = await response.json();
        
        const container = document.getElementById('active-users-list');
        container.innerHTML = '';
        
        if (data.success && data.users.length > 0) {
            data.users.forEach(user => {
                if (user.email !== 'admin@ua.edu.ph') {
                    container.innerHTML += `
                        <div class="border rounded p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold">${user.username}</h3>
                                    <p class="text-gray-600">${user.email}</p>
                                    <p class="text-sm text-green-500">Active</p>
                                </div>
                                <div>
                                    <button onclick="showBlockUserModal(${user.id}, '${user.username}')" 
                                            class="bg-red-500 text-white px-4 py-2 rounded hover:opacity-90">
                                        <i class="fas fa-ban mr-1"></i> Block
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
        } else {
            container.innerHTML = '<p class="text-gray-500">No active users found.</p>';
        }
    } catch (error) {
        console.error('Error loading active users:', error);
    }
}

// User Management - Blocked Users
async function loadBlockedUsers() {
    try {
        const response = await fetch('api/get_users.php?is_blocked=1');
        const data = await response.json();
        
        const container = document.getElementById('blocked-users-list');
        container.innerHTML = '';
        
        if (data.success && data.users.length > 0) {
            data.users.forEach(user => {
                container.innerHTML += `
                    <div class="border rounded p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold">${user.username}</h3>
                                <p class="text-gray-600">${user.email}</p>
                                <p class="text-sm text-red-500">Blocked</p>
                                ${user.block_reason ? `
                                <div class="mt-2 bg-gray-50 p-2 rounded">
                                    <p class="text-xs text-gray-500">Block Reason:</p>
                                    <p class="text-sm text-gray-700">${user.block_reason}</p>
                                </div>
                                ` : ''}
                            </div>
                            <div>
                                <button onclick="unblockUser(${user.id}, '${user.username}')" 
                                        class="bg-green-500 text-white px-4 py-2 rounded hover:opacity-90">
                                    <i class="fas fa-user-check mr-1"></i> Unblock
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            container.innerHTML = '<p class="text-gray-500">No blocked users found.</p>';
        }
    } catch (error) {
        console.error('Error loading blocked users:', error);
    }
}

// Search Functions
function searchUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const userElements = document.querySelectorAll('#active-users-list > div');
    
    userElements.forEach(userEl => {
        const userName = userEl.querySelector('h3').textContent.toLowerCase();
        const userEmail = userEl.querySelectorAll('p')[0].textContent.toLowerCase();
        
        if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
            userEl.style.display = '';
        } else {
            userEl.style.display = 'none';
        }
    });
}

function searchBlockedUsers() {
    const searchTerm = document.getElementById('blocked-user-search').value.toLowerCase();
    const userElements = document.querySelectorAll('#blocked-users-list > div');
    
    userElements.forEach(userEl => {
        const userName = userEl.querySelector('h3').textContent.toLowerCase();
        const userEmail = userEl.querySelectorAll('p')[0].textContent.toLowerCase();
        
        if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
            userEl.style.display = '';
        } else {
            userEl.style.display = 'none';
        }
    });
}

// Block User Modal
let userToBlockId = null;
let userToBlockName = null;
let userToUnblockId = null;
let userToUnblockName = null;

function showBlockUserModal(userId, username) {
    userToBlockId = userId;
    userToBlockName = username;
    
    document.getElementById('user-to-block-id').value = userId;
    document.getElementById('block-user-modal').style.display = 'flex';
    document.getElementById('block-reason-container').classList.remove('hidden');
}

function closeBlockUserModal() {
    document.getElementById('block-user-modal').style.display = 'none';
    document.getElementById('block-reason').value = '';
    userToBlockId = null;
    userToBlockName = null;
}

async function confirmBlockUser() {
    if (!userToBlockId) {
        closeBlockUserModal();
        return;
    }
    
    const blockReason = document.getElementById('block-reason').value;
    
    try {
        const response = await fetch('api/toggle_user_block.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userToBlockId,
                block: true,
                block_reason: blockReason
            })
        });
        
        const data = await response.json();
        if (data.success) {
            closeBlockUserModal();
            // Show success notification instead of alert
            showSuccessNotification('User Blocked', `User ${userToBlockName} has been blocked successfully.`);
            loadActiveUsers();
            loadBlockedUsers();
        } else {
            showErrorNotification('Block User Error', data.message || 'Failed to block user.');
        }
    } catch (error) {
        console.error('Error blocking user:', error);
        showErrorNotification('Block User Error', 'Failed to block user. Please try again.');
    }
}

// Variables for user management
function showUnblockUserModal(userId, username) {
    userToUnblockId = userId;
    userToUnblockName = username;
    
    document.getElementById('user-to-unblock-id').value = userId;
    document.getElementById('unblock-user-modal').style.display = 'flex';
}

function closeUnblockUserModal() {
    document.getElementById('unblock-user-modal').style.display = 'none';
    userToUnblockId = null;
    userToUnblockName = null;
}

async function confirmUnblockUser() {
    if (!userToUnblockId) {
        closeUnblockUserModal();
        return;
    }
    
    try {
        const response = await fetch('api/toggle_user_block.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userToUnblockId,
                block: false
            })
        });
        
        const data = await response.json();
        if (data.success) {
            closeUnblockUserModal();
            // Show success notification
            const message = userToUnblockName 
                ? `User ${userToUnblockName} has been unblocked successfully.` 
                : 'User has been unblocked successfully.';
            showSuccessNotification('User Unblocked', message);
            loadActiveUsers();
            loadBlockedUsers();
        } else {
            showErrorNotification('Unblock User Error', data.message || 'Failed to unblock user.');
        }
    } catch (error) {
        console.error('Error unblocking user:', error);
        showErrorNotification('Unblock User Error', 'Failed to unblock user. Please try again.');
    }
}

// Update the unblockUser function to use the modal
function unblockUser(userId, username) {
    showUnblockUserModal(userId, username);
}

// Image viewer for donation images
function openImageViewer(imagePath) {
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

async function viewDonationDetails(donationId) {
    console.log('Viewing details for donation ID:', donationId);
    
    try {
        // First test if the API is accessible
        await fetch('api/test_admin_api.php')
            .then(res => res.json())
            .then(data => console.log('API test response:', data))
            .catch(err => console.error('API test failed:', err));
        
        // Now try the actual donation details endpoint
        console.log('Fetching details from:', `api/get_donation_details.php?id=${donationId}`);
        const response = await fetch(`api/get_donation_details.php?id=${donationId}`);
        console.log('Response status:', response.status);
        
        // Handle non-success HTTP status
        if (!response.ok) {
            throw new Error(`API returned status code ${response.status}`);
        }
        
        // Debug: Log the raw response text
        const rawText = await response.text();
        console.log('Raw API response:', rawText);
        
        // Try to parse the JSON
        let data;
        try {
            data = JSON.parse(rawText);
            console.log('Parsed data:', data);
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            throw new Error(`Failed to parse JSON: ${jsonError.message}`);
        }
        
        if (data.success) {
            currentDonationId = donationId;
            const donation = data.donation;
            
            // Build image gallery if images exist
            let imageGallery = '';
            if (donation.images && donation.images.length > 0) {
                console.log(`Found ${donation.images.length} images for this donation`);
                imageGallery = `
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Images</h4>
                        <div class="flex flex-wrap gap-2">
                            ${donation.images.map(img => `
                                <div class="w-32 h-32 overflow-hidden rounded border border-gray-200">
                                    <img src="${img.file_path}" alt="${img.file_name}" 
                                        class="w-full h-full object-cover cursor-pointer hover:opacity-90"
                                        onclick="openImageViewer('${img.file_path}')">
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                console.log('No images found for this donation');
            }
            
            // Build video player if videos exist
            let videoGallery = '';
            if (donation.videos && donation.videos.length > 0) {
                console.log(`Found ${donation.videos.length} videos for this donation`);
                videoGallery = `
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Video</h4>
                        <div class="video-container">
                            ${donation.videos.map(video => `
                                <div class="rounded border border-gray-200 p-2">
                                    <video controls class="max-w-full">
                                        <source src="${video.file_path}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <p class="mt-1 text-sm text-gray-600">${video.file_name} (${(video.file_size / (1024*1024)).toFixed(1)} MB)</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                console.log('No videos found for this donation');
            }

            // Fetch status history
            const historyResponse = await fetch(`api/get_status_history.php?donation_id=${donationId}`);
            const historyData = await historyResponse.json();
            
            let statusHistory = '';
            if (historyData.success && historyData.history.length > 0) {
                statusHistory = `
                    <div class="border-t pt-4 mt-4">
                        <h4 class="font-bold text-blue-600">Status History</h4>
                        <div class="status-timeline mt-3">
                            ${historyData.history.map(item => {
                                const statusClass = item.status.toLowerCase();
                                const date = new Date(item.created_at).toLocaleString();
                                
                                return `
                                    <div class="status-timeline-item ${statusClass}">
                                        <p class="font-semibold">${item.status.charAt(0).toUpperCase() + item.status.slice(1)}</p>
                                        <p class="text-sm text-gray-500">${date}</p>
                                        ${item.comment ? `<p class="mt-1">${item.comment}</p>` : ''}
                                        ${item.created_by_name ? `<p class="text-xs text-gray-500 mt-1">By: ${item.created_by_name}</p>` : ''}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('donation-details').innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold">Title</h4>
                        <p>${donation.title}</p>
                    </div>
                    <div>
                        <h4 class="font-bold">Description</h4>
                        <p>${donation.description}</p>
                    </div>
                    
                    ${imageGallery}
                    
                    ${videoGallery}
                    
                    <div class="border-t pt-4">
                        <h4 class="font-bold text-blue-600">Donor Information</h4>
                        <div class="bg-blue-50 p-3 rounded mt-2">
                            <p><span class="font-semibold">Name:</span> ${donation.donor_name}</p>
                            <p><span class="font-semibold">Email:</span> ${donation.donor_email}</p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-bold">Item Details</h4>
                        <p><span class="font-semibold">Category:</span> ${donation.category}</p>
                        <p><span class="font-semibold">Condition:</span> ${donation.condition}</p>
                        <p><span class="font-semibold">Date Submitted:</span> ${new Date(donation.created_at).toLocaleString()}</p>
                        <p><span class="font-semibold">Current Status:</span> 
                            <span class="status-badge ${donation.status.toLowerCase() === 'pending' ? 'status-badge-pending' : 
                                      donation.status.toLowerCase() === 'accepted' ? 'status-badge-accepted' : 
                                      donation.status.toLowerCase() === 'declined' ? 'status-badge-declined' : 
                                      donation.status.toLowerCase() === 'delivered' ? 'status-badge-delivered' : ''}">
                                ${donation.status.charAt(0).toUpperCase() + donation.status.slice(1)}
                            </span>
                        </p>
                    </div>
                    
                    ${statusHistory}
                    
                    <div class="border-t pt-4 mt-4">
                        <h4 class="font-bold text-blue-600">Actions</h4>
                        <div class="flex gap-2 mt-2">
                            ${donation.status === 'pending' ? `
                                <button onclick="handleDonation(${donationId}, 'accepted')" 
                                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Accept
                                </button>
                                <button onclick="handleDonation(${donationId}, 'declined')" 
                                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Decline
                                </button>
                            ` : donation.status === 'accepted' ? `
                                <button onclick="handleDonation(${donationId}, 'delivered')" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Mark as Delivered
                                </button>
                            ` : ''}
                            <button onclick="deleteDonation(${donationId}); closeModal();" 
                                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            const modal = document.getElementById('donation-modal');
            console.log('Modal element:', modal);
            modal.classList.remove('hidden');
            
            // Force display of modal with inline style if needed
            modal.style.display = 'flex';
            console.log('Modal should now be visible');
            
            // Disable scrolling on the main document to prevent background scrolling
            document.body.style.overflow = 'hidden';
            
            // Ensure the modal content is scrollable
            const donationDetails = document.getElementById('donation-details');
            donationDetails.scrollTop = 0;
            
            // Force refresh of scrollable area
            setTimeout(() => {
                donationDetails.style.display = 'none';
                setTimeout(() => {
                    donationDetails.style.display = 'block';
                }, 0);
            }, 50);
        } else {
            console.error('API returned error:', data.message);
            alert(`Error loading donation details: ${data.message}`);
        }
    } catch (error) {
        console.error('Error in viewDonationDetails():', error);
        alert(`Failed to load donation details: ${error.message}`);
    }
}

function closeModal() {
    const modal = document.getElementById('donation-modal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    currentDonationId = null;
    
    // Reset the donation details content to prevent overflow issues on next open
    document.getElementById('donation-details').innerHTML = '';
    
    // Re-enable scrolling on the main document if needed
    document.body.style.overflow = '';
    
    // Refresh scrollable areas after modal is closed
    setTimeout(() => {
        const scrollableAreas = document.querySelectorAll('.scrollable-content');
        scrollableAreas.forEach(area => {
            if (area.id !== 'donation-details') {
                // Force recalculation of scroll area
                area.style.display = 'none';
                setTimeout(() => {
                    area.style.display = 'block';
                }, 0);
            }
        });
    }, 100);
}

function logout() {
    // Show confirmation popup
    const confirmModal = document.createElement('div');
    confirmModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    confirmModal.style.display = 'flex';
    
    confirmModal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 text-center">
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
        window.location.href = 'index.html';
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

// Handle donation deletion
async function deleteDonation(donationId) {
    if (!confirm('Are you sure you want to delete this donation? This action cannot be undone.')) {
        return;
    }

    // Get current admin user
    const user = JSON.parse(sessionStorage.getItem('user'));
    const adminId = user ? user.id : null;

    try {
        const response = await fetch('api/delete_donation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                donation_id: donationId,
                admin_id: adminId
            })
        });

        const data = await response.json();
        if (data.success) {
            alert('Donation deleted successfully!');
            loadAllDonations(); // Reload the list
        } else {
            alert('Failed to delete donation: ' + data.message);
        }
    } catch (error) {
        console.error('Error deleting donation:', error);
        alert('Failed to delete donation. Please try again.');
    }
}

// Load all donations
async function loadAllDonations() {
    try {
        console.log("Loading all donations...");
        
        // Build the URL with any filters
        const statusFilter = document.getElementById('status-filter')?.value || '';
        const categoryFilter = document.getElementById('all-category-filter')?.value || '';
        const sortBy = document.getElementById('all-sort-by')?.value || 'newest';
        
        // Fix the URL path - ensure it includes E-Donate-v3
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        const url = new URL(`${baseUrl}/api/get_all_donations.php`);
        
        if (statusFilter) url.searchParams.append('status', statusFilter);
        if (categoryFilter) url.searchParams.append('category_id', categoryFilter);
        if (sortBy) url.searchParams.append('sort', sortBy);
        
        console.log("Fetching from URL:", url.toString());
        
        const response = await fetch(url);
        console.log("Response status:", response.status);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        // Get the raw text response for debugging
        const rawText = await response.text();
        console.log("Raw response sample:", rawText.substring(0, 100) + "...");
        
        // Try to parse the JSON
        let data;
        try {
            data = JSON.parse(rawText);
            console.log("Parsed data count:", data.count || 0);
        } catch (jsonError) {
            console.error("JSON parse error:", jsonError);
            throw new Error(`Failed to parse response as JSON: ${jsonError.message}`);
        }
        
        const container = document.getElementById('all-donations-list');
        container.innerHTML = '';
        
        if (data.success && data.donations && data.donations.length > 0) {
            console.log(`Found ${data.donations.length} donations`);
            
            data.donations.forEach((donation, index) => {
                // Define status badge color based on status
                let statusBadgeClass = '';
                let statusText = donation.status_text || 'Unknown';
                
                switch ((statusText || '').toLowerCase()) {
                    case 'accepted':
                        statusBadgeClass = 'bg-green-100 text-green-800';
                        break;
                    case 'declined':
                        statusBadgeClass = 'bg-red-100 text-red-800';
                        break;
                    default:
                        statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                }
                
                container.innerHTML += `
                    <div class="border rounded p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center mb-2">
                                    <h3 class="font-bold text-lg mr-2">${donation.title}</h3>
                                    <span class="px-2 py-1 rounded text-xs ${statusBadgeClass}">
                                        ${statusText}
                                    </span>
                                </div>
                                <p class="text-gray-600">Category: ${donation.category}</p>
                                <p class="text-gray-600">Condition: ${donation.condition || 'Not specified'}</p>
                                <p class="text-gray-600">From: ${donation.donor_name}</p>
                                <p class="text-sm text-gray-500">Submitted: ${donation.created_at ? new Date(donation.created_at).toLocaleDateString() : 'Unknown'}</p>
                                <p class="mt-2">${donation.description || 'No description available'}</p>
                            </div>
                            <div class="flex flex-col space-y-2">
                                <button onclick="viewDonationDetails(${donation.id})" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    View Details
                                </button>
                                <button onclick="deleteDonation(${donation.id})" 
                                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            console.log("No donations found or success=false");
            container.innerHTML = '<p class="text-gray-500">No donations found.</p>';
        }
    } catch (error) {
        console.error('Error loading all donations:', error);
        document.getElementById('all-donations-list').innerHTML = 
            `<p class="text-red-500">Error loading donations: ${error.message}. Please try again later.</p>`;
    }
}

// Filter and sort all donations
function filterAllDonations() {
    loadAllDonations();
}

function sortAllDonations() {
    loadAllDonations();
}

// Load categories for filter dropdowns
async function loadCategories() {
    try {
        // Fix the URL path - ensure it includes E-Donate-v3
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        const response = await fetch(`${baseUrl}/api/get_categories.php`);
        
        const data = await response.json();
        
        if (data.success && data.categories.length > 0) {
            // Build category options HTML
            const categoryOptions = data.categories.map(category => 
                `<option value="${category.id}">${category.name}</option>`
            ).join('');
            
            // Add to both filter dropdowns
            const categoryFilters = document.querySelectorAll('#category-filter, #all-category-filter');
            categoryFilters.forEach(select => {
                // Keep the first "All Categories" option
                select.innerHTML = `<option value="">All Categories</option>${categoryOptions}`;
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Announcements Management
let currentlyEditingAnnouncementId = null;

async function loadAnnouncements() {
    try {
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        const response = await fetch(`${baseUrl}/api/get_announcements.php`);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        const container = document.getElementById('announcements-list');
        const noAnnouncementsMessage = document.getElementById('no-announcements-message');
        
        if (data.success && data.announcements && data.announcements.length > 0) {
            // Hide the "no announcements" message if it exists
            if (noAnnouncementsMessage) {
                noAnnouncementsMessage.classList.add('hidden');
            }
            
            container.innerHTML = '';
            
            data.announcements.forEach(announcement => {
                const statusClass = announcement.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                const statusText = announcement.is_active ? 'Active' : 'Inactive';
                
                container.innerHTML += `
                    <div class="border rounded p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center mb-1">
                                    <h3 class="font-bold text-lg mr-2">${announcement.title}</h3>
                                    <span class="px-2 py-1 rounded text-xs ${statusClass}">
                                        ${statusText}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mb-2">
                                    Created: ${new Date(announcement.created_at).toLocaleDateString()}
                                </p>
                                <p class="text-gray-700">${announcement.content}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="editAnnouncement(${announcement.id})" 
                                        class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button onclick="showDeleteAnnouncementModal(${announcement.id})" 
                                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            // Show the "no announcements" message if it exists
            if (noAnnouncementsMessage) {
                noAnnouncementsMessage.classList.remove('hidden');
            } else {
                // If the message element doesn't exist, create a fallback message
                container.innerHTML = '<p class="text-gray-500">No announcements found.</p>';
            }
        }
    } catch (error) {
        console.error('Error loading announcements:', error);
        document.getElementById('announcements-list').innerHTML = 
            `<p class="text-red-500">Error loading announcements: ${error.message}</p>`;
    }
}

function showAnnouncementForm(isEdit = false) {
    const form = document.getElementById('announcement-form');
    const formTitle = document.getElementById('form-title');
    const formElement = document.getElementById('announcement-form-element');
    const idField = document.getElementById('announcement-id');
    
    // Check if all elements exist
    if (!form || !formTitle || !formElement) {
        console.error('Error: Could not find announcement form elements');
        return;
    }
    
    // Reset form
    if (formElement) {
        formElement.reset();
    }
    
    if (idField) {
        idField.value = '';
    }
    
    if (formTitle) {
        if (isEdit) {
            formTitle.textContent = 'Edit Announcement';
        } else {
            formTitle.textContent = 'Create New Announcement';
            currentlyEditingAnnouncementId = null;
        }
    }
    
    form.classList.remove('hidden');
}

function cancelAnnouncementForm() {
    const form = document.getElementById('announcement-form');
    if (form) {
        form.classList.add('hidden');
    }
    currentlyEditingAnnouncementId = null;
}

async function editAnnouncement(announcementId) {
    try {
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        const response = await fetch(`${baseUrl}/api/get_announcements.php?id=${announcementId}`);
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.announcement) {
            const announcement = data.announcement;
            
            // Show the form with edit title FIRST
            showAnnouncementForm(true);

            // Get form elements
            const idField = document.getElementById('announcement-id');
            const titleField = document.getElementById('announcement-title');
            const contentField = document.getElementById('announcement-content');
            const activeField = document.getElementById('announcement-active');
            
            // Now set the values
            if (idField && titleField && contentField && activeField) {
                idField.value = announcement.id;
                currentlyEditingAnnouncementId = announcement.id;
                titleField.value = announcement.title;
                contentField.value = announcement.content;
                activeField.checked = announcement.is_active == 1;
            } else {
                showErrorNotification('Error', 'Could not find all form fields.');
            }
        } else {
            showErrorNotification('Error', 'Could not find the announcement to edit.');
        }
    } catch (error) {
        console.error('Error loading announcement for edit:', error);
        showErrorNotification('Error', `Failed to load announcement: ${error.message}`);
    }
}

async function saveAnnouncement(event) {
    event.preventDefault();
    
    const id = document.getElementById('announcement-id').value;
    const title = document.getElementById('announcement-title').value;
    const content = document.getElementById('announcement-content').value;
    const isActive = document.getElementById('announcement-active').checked;
    
    const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
    
    try {
        let url = `${baseUrl}/api/manage_announcement.php`;
        let method = id ? 'PUT' : 'POST';
        
        const data = {
            title,
            content,
            is_active: isActive
        };
        
        if (id) {
            data.id = parseInt(id);
        }
        
        console.log('Sending request:', { method, url, data }); // Debug log
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const responseData = await response.json();
        console.log('Server response:', responseData); // Debug log
        
        if (responseData.success) {
            // Show success notification
            const successMessage = id ? 'Announcement updated successfully!' : 'Announcement created successfully!';
            showSuccessNotification('Success', successMessage);
            
            // Hide the form
            const form = document.getElementById('announcement-form');
            if (form) {
                form.classList.add('hidden');
            }
            
            // Reset the editing state
            currentlyEditingAnnouncementId = null;
            
            // Reload announcements
            loadAnnouncements();
        } else {
            showErrorNotification('Error', responseData.message || 'Failed to save announcement.');
        }
    } catch (error) {
        console.error('Error saving announcement:', error);
        showErrorNotification('Error', `Failed to save announcement: ${error.message}`);
    }
}

// Variables for announcement deletion
let announcementToDeleteId = null;

function showDeleteAnnouncementModal(announcementId) {
    announcementToDeleteId = announcementId;
    document.getElementById('announcement-to-delete-id').value = announcementId;
    document.getElementById('delete-announcement-modal').style.display = 'flex';
}

function closeDeleteAnnouncementModal() {
    document.getElementById('delete-announcement-modal').style.display = 'none';
    announcementToDeleteId = null;
}

// Success notification functions
function showSuccessNotification(title, message) {
    const titleElement = document.getElementById('success-message-title');
    const messageElement = document.getElementById('success-message-text');
    
    if (titleElement) titleElement.textContent = title;
    if (messageElement) messageElement.textContent = message;
    
    document.getElementById('success-notification').style.display = 'flex';
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        closeSuccessNotification();
    }, 3000);
}

function closeSuccessNotification() {
    document.getElementById('success-notification').style.display = 'none';
}

// Error notification function
function showErrorNotification(title, message) {
    const titleElement = document.getElementById('error-message-title');
    const messageElement = document.getElementById('error-message-text');
    
    if (titleElement) titleElement.textContent = title;
    if (messageElement) messageElement.textContent = message;
    
    document.getElementById('error-notification').style.display = 'flex';
    
    // Auto-hide after 5 seconds (errors may need more time to read)
    setTimeout(() => {
        closeErrorNotification();
    }, 5000);
}

function closeErrorNotification() {
    document.getElementById('error-notification').style.display = 'none';
}

async function confirmDeleteAnnouncement() {
    if (!announcementToDeleteId) {
        closeDeleteAnnouncementModal();
        return;
    }
    
    try {
        const baseUrl = window.location.href.split('/').slice(0, -1).join('/');
        const response = await fetch(`${baseUrl}/api/manage_announcement.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: announcementToDeleteId
            })
        });
        
        if (!response.ok) {
            throw new Error(`Server responded with status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Close the delete confirmation modal
            closeDeleteAnnouncementModal();
            
            // Show success notification instead of alert
            showSuccessNotification('Success', 'Announcement deleted successfully!');
            
            // Reload announcements list
            loadAnnouncements();
        } else {
            showErrorNotification('Delete Announcement Error', data.message || 'Failed to delete announcement.');
        }
    } catch (error) {
        console.error('Error deleting announcement:', error);
        showErrorNotification('Delete Announcement Error', error.message || 'Failed to delete announcement. Please try again.');
    }
}

async function deleteAnnouncement(announcementId) {
    // Show the delete confirmation modal instead of using browser confirm
    showDeleteAnnouncementModal(announcementId);
} 