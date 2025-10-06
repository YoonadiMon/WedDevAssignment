// Mock data for listings
const mockListings = [
    {
        listingId: "L001",
        memberId: "M001",
        title: "Monstera Deliciosa - Mature Plant",
        description: "Beautiful mature Monstera plant with fenestrated leaves. Well-established and healthy. Looking to trade for gardening tools or other interesting plants. This plant has been growing for over 2 years and is very resilient.",
        tags: "monstera, indoor plant, mature, fenestrated",
        imageUrl: "https://placehold.co/600x400",
        category: "plants",
        dateListed: "2025-01-10",
        status: "active",
        itemType: "plant",
        condition: "excellent",
        species: "Monstera Deliciosa",
        growthStage: "Mature",
        careInstructions: "Bright indirect light, water weekly, prefers humidity",
        userName: "Sarah Green",
        userRating: 4.8,
        userTradeCount: 12,
        location: "Kuala Lumpur"
    },
    {
        listingId: "L002",
        memberId: "M002",
        title: "Professional Gardening Tools Set",
        description: "Complete set of professional gardening tools including trowel, pruners, gloves, and weeding tools. Barely used, in excellent condition. Perfect for serious gardeners.",
        tags: "tools, gardening, professional, complete set",
        imageUrl: "https://placehold.co/600x400",
        category: "tools",
        dateListed: "2025-01-12",
        status: "active",
        itemType: "item",
        condition: "excellent",
        brand: "Fiskars",
        dimensions: "Tool kit: 12x8x4 inches",
        usageHistory: "Used only a few times, like new condition",
        userName: "Mike Garden",
        userRating: 4.9,
        userTradeCount: 8,
        location: "Penang"
    },
    {
        listingId: "L003",
        memberId: "M003",
        title: "Heirloom Tomato Seeds Collection",
        description: "Rare heirloom tomato seeds including Brandywine, Cherokee Purple, and Green Zebra varieties. Organic and non-GMO. Perfect for home gardeners.",
        tags: "seeds, tomato, heirloom, organic",
        imageUrl: "https://placehold.co/600x400",
        category: "seeds",
        dateListed: "2025-01-08",
        status: "active",
        itemType: "item",
        condition: "new",
        userName: "Emma Harvest",
        userRating: 4.7,
        userTradeCount: 15,
        location: "Johor Bahru"
    }
];

// Global functions that need to be accessible
function openListingModal(listingId) {
    const listing = mockListings.find(l => l.listingId === listingId);
    if (!listing) return;
    
    const modal = document.getElementById('listingModal');
    const modalContent = document.getElementById('modalContent');
    
    // Populate modal content
    modalContent.innerHTML = `
        <div class="modal-header">
            <div class="modal-image">
                ${listing.imageUrl ? 
                    `<img src="${listing.imageUrl}" alt="${listing.title}">` :
                    `📷 Image Coming Soon`
                }
            </div>
            <div class="modal-info">
                <h2>${listing.title}</h2>
                <div class="modal-category">${formatCategory(listing.category)}</div>
                <div class="modal-description">${listing.description}</div>
            </div>
        </div>
        
        <div class="modal-details-grid">
            <div class="detail-item">
                <div class="detail-label">Condition</div>
                <div class="detail-value">${formatCondition(listing.condition)}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Listed</div>
                <div class="detail-value">${formatDate(listing.dateListed)}</div>
            </div>
            ${listing.itemType === 'plant' ? `
                <div class="detail-item">
                    <div class="detail-label">Species</div>
                    <div class="detail-value">${listing.species}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Growth Stage</div>
                    <div class="detail-value">${listing.growthStage}</div>
                </div>
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
        </div>
        
        <div class="modal-user-info">
            <div class="user-avatar-large">
                ${listing.userName.split(' ').map(n => n[0]).join('')}
            </div>
            <div class="user-details">
                <h4>${listing.userName}</h4>
                <p>${listing.location || 'Malaysia'}</p>
                <div class="user-rating">
                    <span class="stars">★★★★★</span>
                    <span>${listing.userRating} • ${listing.userTradeCount} trades</span>
                </div>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="save-btn" onclick="saveListing('${listing.listingId}')">
                Save for Later
            </button>
            <button class="trade-btn" onclick="startTrade('${listing.listingId}')">
                Start Trade Conversation
            </button>
        </div>
    `;
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('listingModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function startTrade(listingId) {
    const listing = mockListings.find(l => l.listingId === listingId);
    if (listing) {
        // Close modal first
        closeModal();
        
        // Show confirmation and redirect to chat
        const confirmTrade = confirm(`Start a trade conversation with ${listing.userName} about "${listing.title}"?`);
        if (confirmTrade) {
            // Redirect to chat page with the lister's ID
            alert(`Redirecting to chat with ${listing.userName}...\n\nIn a real application, this would open the chat page with the lister.`);
            // window.location.href = `../../pages/MemberPages/mChat.html?userId=${listing.memberId}`;
        }
    }
}

function saveListing(listingId) {
    const listing = mockListings.find(l => l.listingId === listingId);
    if (listing) {
        alert(`"${listing.title}" has been saved to your favorites!`);
        // In real app, this would add to saved listings
    }
}

function formatCategory(category) {
    const categories = {
        'plants': 'Plants',
        'tools': 'Gardening Tools',
        'seeds': 'Seeds & Saplings',
        'decor': 'Garden Decor',
        'books': 'Gardening Books',
        'other': 'Other'
    };
    return categories[category] || category;
}

function formatCondition(condition) {
    const conditions = {
        'new': 'New',
        'excellent': 'Excellent',
        'good': 'Good',
        'fair': 'Fair'
    };
    return conditions[condition] || condition;
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
    
    // Re-render listings
    renderListings(mockListings);
}

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    const listingsGrid = document.getElementById('listingsGrid');
    const modalClose = document.getElementById('modalClose');
    const categoryTabs = document.querySelectorAll('.category-tab');
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const conditionFilter = document.getElementById('conditionFilter');
    const typeFilter = document.getElementById('typeFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    let currentCategory = 'all';
    let currentSearch = '';
    
    // Modal close functionality
    modalClose.addEventListener('click', closeModal);
    document.getElementById('listingModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Category tabs event listeners
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            categoryTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category');
            applyFilters();
        });
    });
    
    // Filter event listeners
    [categoryFilter, conditionFilter, typeFilter, sortFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.toLowerCase();
        applyFilters();
    });
    
    // Initial render
    renderListings(mockListings);
    
    function applyFilters() {
        let filteredListings = mockListings.filter(listing => {
            // Category filter
            const categoryMatch = currentCategory === 'all' || listing.category === currentCategory;
            
            // Search filter
            const searchMatch = currentSearch === '' || 
                listing.title.toLowerCase().includes(currentSearch) ||
                listing.description.toLowerCase().includes(currentSearch) ||
                listing.tags.toLowerCase().includes(currentSearch);
            
            // Condition filter
            const conditionMatch = conditionFilter.value === 'all' || listing.condition === conditionFilter.value;
            
            // Type filter
            const typeMatch = typeFilter.value === 'all' || listing.itemType === typeFilter.value;
            
            return categoryMatch && searchMatch && conditionMatch && typeMatch;
        });
        
        // Sort listings
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
            <div class="listing-card ${listing.itemType === 'plant' ? 'plant-special' : 'item-special'}" 
                    onclick="openListingModal('${listing.listingId}')">
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
                            <div class="listing-category c-text">${formatCategory(listing.category)}</div>
                        </div>
                    </div>
                    <div class="listing-description">${listing.description}</div>
                    
                    <div class="listing-details">
                        <span class="detail-badge">${formatCondition(listing.condition)}</span>
                        ${listing.itemType === 'plant' ? 
                            `<span class="detail-badge">${listing.species}</span>
                                <span class="detail-badge">${listing.growthStage}</span>` :
                            `<span class="detail-badge">${formatCategory(listing.category)}</span>`
                        }
                    </div>
                    
                    <div class="listing-meta">
                        <div class="listing-user">
                            <div class="user-avatar">
                                ${listing.userName.split(' ').map(n => n[0]).join('')}
                            </div>
                            <div class="c-text">${listing.userName}</div>
                        </div>
                        <div class="listing-date c-text">${formatDate(listing.dateListed)}</div>
                    </div>
                </div>
            </div>
        `).join('');
    }
});