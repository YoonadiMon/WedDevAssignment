// Global variables
let currentCategory = 'all';
let currentSearch = '';
let currentAdminView = 'all';

// Global functions that need to be accessible
function openListingModal(listingId) {
    const listing = listingsData.find(l => l.listingID == listingId);
    if (!listing) {
        console.error('Listing not found:', listingId);
        return;
    }
    
    const modal = document.getElementById('listingModal');
    const modalContent = document.getElementById('modalContent');
    
    // Populate modal content based on user type
    modalContent.innerHTML = isAdmin ? 
        getAdminModalContent(listing) : 
        getMemberModalContent(listing);
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function getMemberModalContent(listing) {
    return `
        <div class="modal-header">
            <div class="modal-image">
                ${listing.imageUrl ? 
                    `<img src="${listing.imageUrl}" alt="${listing.title}">` :
                    `📷 Image Coming Soon`
                }
            </div>
            <div class="modal-info">
                <h2>${listing.title}</h2>
                <div class="modal-category ${listing.itemType === 'Plant' ? 'plant-special' : 'item-special'}">
                    ${listing.category}
                </div>
                <div class="modal-description">${listing.description}</div>
            </div>
        </div>
        
        <div class="modal-details-grid">
            <div class="detail-item">
                <div class="detail-label">Condition</div>
                <div class="detail-value">${listing.itemCondition}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Listed</div>
                <div class="detail-value">${formatDate(listing.dateListed)}</div>
            </div>
            ${listing.itemType === 'Plant' ? `
                ${listing.species ? `
                    <div class="detail-item">
                        <div class="detail-label">Species</div>
                        <div class="detail-value">${listing.species}</div>
                    </div>
                ` : ''}
                ${listing.growthStage ? `
                    <div class="detail-item">
                        <div class="detail-label">Growth Stage</div>
                        <div class="detail-value">${listing.growthStage}</div>
                    </div>
                ` : ''}
                ${listing.careInstructions ? `
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Care Instructions</div>
                        <div class="detail-value">${listing.careInstructions}</div>
                    </div>
                ` : ''}
            ` : `
                ${listing.brand ? `
                    <div class="detail-item">
                        <div class="detail-label">Brand</div>
                        <div class="detail-value">${listing.brand}</div>
                    </div>
                ` : ''}
                ${listing.dimensions ? `
                    <div class="detail-item">
                        <div class="detail-label">Dimensions</div>
                        <div class="detail-value">${listing.dimensions}</div>
                    </div>
                ` : ''}
                ${listing.usageHistory ? `
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Usage History</div>
                        <div class="detail-value">${listing.usageHistory}</div>
                    </div>
                ` : ''}
            `}
            ${listing.lookingFor ? `
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-label">Looking For</div>
                    <div class="detail-value">${listing.lookingFor}</div>
                </div>
            ` : ''}
        </div>
        
        <div class="modal-user-info">
            <div class="user-avatar-large">
                ${getUserInitials(listing.userName)}
            </div>
            <div class="user-details">
                <h4>${listing.userName}</h4>
                <p>${listing.location || 'Malaysia'}</p>
                <div class="user-rating">
                    <span class="stars">${getStarRating(listing.userRating)}</span>
                    <span>${listing.userRating} • ${listing.userTradeCount} trades</span>
                </div>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="trade-btn" onclick="startTrade(${listing.listingID})">
                Start Trade Conversation
            </button>
            <button class="report-btn" onclick="reportListing(${listing.listingID})">
                Report Listing
            </button>
        </div>
    `;
}

function getAdminModalContent(listing) {
    return `
        <div class="modal-header">
            <div class="modal-image">
                ${listing.imageUrl ? 
                    `<img src="${listing.imageUrl}" alt="${listing.title}">` :
                    `📷 Image Coming Soon`
                }
            </div>
            <div class="modal-info">
                <h2>${listing.title}</h2>
                <div class="modal-category">${listing.category}</div>
                <div class="modal-description">${listing.description}</div>
                ${listing.reported ? '<div class="admin-badge reported-badge">REPORTED</div>' : ''}
            </div>
        </div>
        
        <div class="modal-details-grid">
            <div class="detail-item">
                <div class="detail-label">Listing ID</div>
                <div class="detail-value">${listing.listingID}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Member ID</div>
                <div class="detail-value">${listing.userID}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Condition</div>
                <div class="detail-value">${listing.itemCondition}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Listed</div>
                <div class="detail-value">${formatDate(listing.dateListed)}</div>
            </div>
            ${listing.itemType === 'Plant' ? `
                ${listing.species ? `
                    <div class="detail-item">
                        <div class="detail-label">Species</div>
                        <div class="detail-value">${listing.species}</div>
                    </div>
                ` : ''}
                ${listing.growthStage ? `
                    <div class="detail-item">
                        <div class="detail-label">Growth Stage</div>
                        <div class="detail-value">${listing.growthStage}</div>
                    </div>
                ` : ''}
                ${listing.careInstructions ? `
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Care Instructions</div>
                        <div class="detail-value">${listing.careInstructions}</div>
                    </div>
                ` : ''}
            ` : `
                ${listing.brand ? `
                    <div class="detail-item">
                        <div class="detail-label">Brand</div>
                        <div class="detail-value">${listing.brand}</div>
                    </div>
                ` : ''}
                ${listing.dimensions ? `
                    <div class="detail-item">
                        <div class="detail-label">Dimensions</div>
                        <div class="detail-value">${listing.dimensions}</div>
                    </div>
                ` : ''}
                ${listing.usageHistory ? `
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Usage History</div>
                        <div class="detail-value">${listing.usageHistory}</div>
                    </div>
                ` : ''}
            `}
            ${listing.lookingFor ? `
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-label">Looking For</div>
                    <div class="detail-value">${listing.lookingFor}</div>
                </div>
            ` : ''}
        </div>
        
        <div class="modal-user-info">
            <div class="user-avatar-large">
                ${getUserInitials(listing.userName)}
            </div>
            <div class="user-details">
                <h4>${listing.userName}</h4>
                <p>${listing.location || 'Malaysia'}</p>
                <div class="user-rating">
                    <span class="stars">${getStarRating(listing.userRating)}</span>
                    <span>${listing.userRating} • ${listing.userTradeCount} trades</span>
                </div>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="delete-btn" onclick="deleteListing(${listing.listingID})">
                Delete Listing
            </button>
            ${listing.reported ? `
                <button class="save-btn" onclick="resolveReport(${listing.listingID})">
                    Resolve Report
                </button>
            ` : ''}
        </div>
    `;
}

function closeModal() {
    const modal = document.getElementById('listingModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function startTrade(listingId) {
    const listing = listingsData.find(l => l.listingID == listingId);
    if (listing) {
        // Close modal first
        closeModal();
        
        // Show confirmation and redirect to chat
        const confirmTrade = confirm(`Start a trade conversation with ${listing.userName} about "${listing.title}"?`);
        if (confirmTrade) {
            // Redirect to chat page with the lister's ID
            alert(`Redirecting to chat with ${listing.userName}...\n\nIn a real application, this would open the chat page with the lister.`);
            // window.location.href = `../../pages/MemberPages/mChat.html?userId=${listing.userID}`;
        }
    }
}

// Add this function to handle reporting listings
function reportListing(listingId) {
    const listing = listingsData.find(l => l.listingID == listingId);
    if (listing) {
        const reason = prompt(`Please provide a reason for reporting "${listing.title}":`);
        if (reason !== null && reason.trim() !== '') {
            // In real app, this would make an AJAX call to update the database
            listing.reported = true;
            closeModal();
            alert('Thank you for your report. Our admin team will review this listing.');
        } else if (reason !== null) {
            alert('Please provide a reason for reporting this listing.');
        }
    }
}

// Admin functions
function deleteListing(listingId) {
    if (confirm('Are you sure you want to delete this listing? This action cannot be undone.')) {
        // In real app, this would make an AJAX call to delete from database
        const index = listingsData.findIndex(l => l.listingID == listingId);
        if (index !== -1) {
            listingsData.splice(index, 1);
            closeModal();
            applyFilters();
            alert('Listing deleted successfully.');
        }
    }
}

function resolveReport(listingId) {
    const listing = listingsData.find(l => l.listingID == listingId);
    if (listing) {
        // In real app, this would make an AJAX call to update the database
        listing.reported = false;
        closeModal();
        applyFilters();
        alert('Report resolved successfully.');
    }
}

function toggleAdminView(viewType) {
    const allBtn = document.querySelector('[data-view="all"]');
    const reportedBtn = document.querySelector('[data-view="reported"]');
    const adminStatus = document.getElementById('adminStatus');
    
    // Update active button
    allBtn.classList.toggle('active', viewType === 'all');
    reportedBtn.classList.toggle('active', viewType === 'reported');
    
    // Update current admin view
    currentAdminView = viewType;
    
    // Update status text
    adminStatus.textContent = viewType === 'all' ? 'Viewing all listings' : 'Viewing reported listings';
    
    // Apply filters
    applyFilters();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    return date.toLocaleDateString();
}

function getUserInitials(userName) {
    return userName.split(' ').map(n => n[0]).join('').toUpperCase();
}

function getStarRating(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    
    return '★'.repeat(fullStars) + (halfStar ? '½' : '') + '☆'.repeat(emptyStars);
}

function resetFilters() {
    document.querySelectorAll('.category-tab').forEach(tab => {
        if (tab.getAttribute('data-category') === 'all') {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });
    
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('conditionFilter').value = 'all';
    document.getElementById('typeFilter').value = 'all';
    document.getElementById('sortFilter').value = 'newest';
    document.getElementById('searchInput').value = '';
    
    // Reset admin view if applicable
    if (isAdmin) {
        toggleAdminView('all');
    } else {
        // Re-render listings
        applyFilters();
    }
}

// Initialize admin controls
function initAdminControls() {
    if (isAdmin) {
        // Add event listeners for admin view toggle
        document.querySelectorAll('.view-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const viewType = this.getAttribute('data-view');
                toggleAdminView(viewType);
            });
        });
    }
}

function applyFilters() {
    let filteredListings = listingsData.filter(listing => {
        // Admin view filter
        if (isAdmin && currentAdminView === 'reported' && !listing.reported) {
            return false;
        }
        
        // Category filter
        const categoryMatch = currentCategory === 'all' || listing.category === currentCategory;
        
        // Search filter
        const searchMatch = currentSearch === '' || 
            listing.title.toLowerCase().includes(currentSearch) ||
            listing.description.toLowerCase().includes(currentSearch) ||
            (listing.tags && listing.tags.toLowerCase().includes(currentSearch));
        
        // Condition filter
        const conditionFilter = document.getElementById('conditionFilter');
        const conditionMatch = conditionFilter.value === 'all' || listing.itemCondition === conditionFilter.value;
        
        // Type filter
        const typeFilter = document.getElementById('typeFilter');
        const typeMatch = typeFilter.value === 'all' || listing.itemType === typeFilter.value;
        
        return categoryMatch && searchMatch && conditionMatch && typeMatch;
    });
    
    // Sort listings
    const sortFilter = document.getElementById('sortFilter');
    filteredListings.sort((a, b) => {
        switch(sortFilter.value) {
            case 'oldest':
                return new Date(a.dateListed) - new Date(b.dateListed);
            case 'title':
                return a.title.localeCompare(b.title);
            case 'newest':
            default:
                return new Date(b.dateListed) - new Date(a.dateListed);
        }
    });
    
    renderListings(filteredListings);
}

function renderListings(listings) {
    const listingsGrid = document.getElementById('listingsGrid');
    
    if (listings.length === 0) {
        listingsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <p>No listings found matching your criteria</p>
                <button class="c-btn c-btn-primary" onclick="resetFilters()">Reset Filters</button>
            </div>
        `;
        return;
    }
    
    listingsGrid.innerHTML = listings.map(listing => `
        <div class="listing-card ${listing.itemType === 'Plant' ? 'plant-special' : 'item-special'}" 
                onclick="openListingModal(${listing.listingID})">
            ${isAdmin && listing.reported ? '<div class="admin-badge">REPORTED</div>' : ''}
            <div class="listing-image">
                ${listing.imageUrl ? 
                    `<img src="${listing.imageUrl}" alt="${listing.title}" style="width: 100%; height: 100%; object-fit: cover;">` :
                    `📷 Image Coming Soon`
                }
            </div>
            <div class="listing-content">
                <div class="listing-header">
                    <div>
                        <div class="listing-title">${listing.title}</div>
                        <div class="listing-category c-text">${listing.category}</div>
                    </div>
                </div>
                <div class="listing-description">${listing.description}</div>
                
                <div class="listing-details">
                    <span class="detail-badge">${listing.itemCondition}</span>
                    ${listing.itemType === 'Plant' ? 
                        `${listing.species ? `<span class="detail-badge">${listing.species}</span>` : ''}
                         ${listing.growthStage ? `<span class="detail-badge">${listing.growthStage}</span>` : ''}` :
                        `<span class="detail-badge">${listing.category}</span>`
                    }
                </div>
                
                <div class="listing-meta">
                    <div class="listing-user">
                        <div class="user-avatar">
                            ${getUserInitials(listing.userName)}
                        </div>
                        <div class="c-text">${listing.userName}</div>
                    </div>
                    <div class="listing-date c-text">${formatDate(listing.dateListed)}</div>
                </div>
            </div>
        </div>
    `).join('');
}

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Trade Marketplace...');
    console.log('Available listings:', listingsData);
    
    // Initialize global variables
    currentCategory = 'all';
    currentSearch = '';
    currentAdminView = 'all';
    
    // Initialize admin controls
    initAdminControls();
    
    // Modal close functionality
    const modalClose = document.getElementById('modalClose');
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    
    const modal = document.getElementById('listingModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Category tabs event listeners
    const categoryTabs = document.querySelectorAll('.category-tab');
    if (categoryTabs.length > 0) {
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                categoryTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.getAttribute('data-category');
                applyFilters();
            });
        });
    }
    
    // Filter event listeners
    const categoryFilter = document.getElementById('categoryFilter');
    const conditionFilter = document.getElementById('conditionFilter');
    const typeFilter = document.getElementById('typeFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    [categoryFilter, conditionFilter, typeFilter, sortFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase();
            applyFilters();
        });
    }
    
    // Initial render
    applyFilters();
});