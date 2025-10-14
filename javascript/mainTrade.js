// View configuration - change this to switch between admin and member views
const AdminView = false; // Set to true for admin view, false for member view

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
        location: "Kuala Lumpur",
        reported: false
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
        location: "Penang",
        reported: false
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
        location: "Johor Bahru",
        reported: true
    },
    {
        listingId: "L004",
        memberId: "M004",
        title: "Handmade Ceramic Plant Pots",
        description: "Set of 3 beautiful handmade ceramic pots in different sizes. Each piece is unique with natural glaze patterns.",
        tags: "pots, ceramic, handmade, decor",
        imageUrl: "https://placehold.co/600x400",
        category: "decor",
        dateListed: "2025-01-05",
        status: "active",
        itemType: "item",
        condition: "good",
        userName: "Pottery Artist",
        userRating: 4.6,
        userTradeCount: 5,
        location: "Selangor",
        reported: false
    }
];

function loadHeader() {
    const headerElement = document.getElementById('main-header');
    
    if (AdminView) {
        headerElement.innerHTML = `
            <!-- Admin Header -->
            <section class="c-logo-section">
                <a href="../../pages/adminPages/adminIndex.html" class="c-logo-link">
                    <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                    <div class="c-text">ReLeaf</div>
                </a>
            </section>

            <!-- Menu Links Mobile -->
            <nav class="c-navbar-side">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                <img src="../../assets/images/icon-menu.svg" alt="icon-menu" onclick="showMenu()" class="c-icon-btn" id="menuBtn">
                <div id="sidebarNav" class="c-navbar-side-menu">
                    
                    <img src="../../assets/images/icon-menu-close.svg" alt="icon-menu-close" onclick="hideMenu()" class="close-btn">
                    <div class="c-navbar-side-items">
                        <section class="c-navbar-side-more">
                            <button id="themeToggle1">
                                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon" >
                            </button>
                            <a href="../../pages/adminPages/aProfile.html">
                                <img src="../../assets/images/profile-light.svg" alt="Profile">
                            </a>
                        </section>

                        <a href="../../pages/adminPages/adminIndex.html">Dashboard</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                        <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                        <a href="../../pages/adminPages/aHelpTicket.html">Help</a>
                    </div>
                </div>
            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/adminPages/adminIndex.html">Dashboard</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                <a href="../../pages/adminPages/aHelpTicket.html">Help</a>
            </nav>          
            <section class="c-navbar-more">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                <button id="themeToggle2">
                    <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon" >
                </button>
                <a href="../../pages/adminPages/aProfile.html">
                    <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
                </a>
            </section>
        `;
    } else {
        headerElement.innerHTML = `
            <!-- Member Header -->
            <section class="c-logo-section">
                <a href="../../pages/MemberPages/memberIndex.html" class="c-logo-link">
                    <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                    <div class="c-text">ReLeaf</div>
                </a>
            </section>

            <!-- Menu Links Mobile -->
            <nav class="c-navbar-side">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                <img src="../../assets/images/icon-menu.svg" alt="icon-menu" onclick="showMenu()" class="c-icon-btn"
                    id="menuBtn">
                <div id="sidebarNav" class="c-navbar-side-menu">

                    <img src="../../assets/images/icon-menu-close.svg" alt="icon-menu-close" onclick="hideMenu()"
                        class="close-btn">
                    <div class="c-navbar-side-items">
                        <section class="c-navbar-side-more">
                            <button id="themeToggle1">
                                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                            </button>

                            <div class="c-chatbox" id="chatboxMobile">
                                <a href="../../pages/MemberPages/mChat.html">
                                    <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                                </a>
                                <span class="c-notification-badge" id="chatBadgeMobile"></span>
                            </div>

                            <a href="../../pages/MemberPages/mSetting.html">
                                <img src="../../assets/images/setting-light.svg" alt="Settings">
                            </a>
                        </section>

                        <a href="../../pages/MemberPages/memberIndex.html">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.html">About</a>
                    </div>
                </div>
            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/MemberPages/memberIndex.html">Home</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.html">Event</a>
                <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                <a href="../../pages/CommonPages/aboutUs.html">About</a>
            </nav>
            <section class="c-navbar-more">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                
                <button id="themeToggle2">
                    <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                </button>
                <a href="../../pages/MemberPages/mChat.html" class="c-chatbox" id="chatboxDesktop">
                    <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                    <span class="c-notification-badge" id="chatBadgeDesktop"></span>
                </a>

                <a href="../../pages/MemberPages/mSetting.html">
                    <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
                </a>
            </section>
        `;
    }
    
    // Initialize header functionality after loading
    initHeaderFunctionality();
}

// Function to initialize header functionality (mobile menu, theme toggle, etc.)
function initHeaderFunctionality() {
    // Mobile menu functionality
    function showMenu() {
        const sidebarNav = document.getElementById('sidebarNav');
        if (sidebarNav) {
            sidebarNav.style.right = "0";
        }
    }

    function hideMenu() {
        const sidebarNav = document.getElementById('sidebarNav');
        if (sidebarNav) {
            sidebarNav.style.right = "-300px";
        }
    }

    // Attach event listeners for mobile menu
    const menuBtn = document.getElementById('menuBtn');
    const closeBtn = document.querySelector('.close-btn');
    
    if (menuBtn) {
        menuBtn.addEventListener('click', showMenu);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', hideMenu);
    }

    // Theme toggle functionality
    function initializeThemeToggle() {
        const themeToggles = document.querySelectorAll('#themeToggle1, #themeToggle2');
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        // Apply current theme
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        themeToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Update toggle button icons if needed
                updateThemeIcons(newTheme);
            });
        });
    }

    function updateThemeIcons(theme) {
        const themeIcons = document.querySelectorAll('#themeToggle1 img, #themeToggle2 img');
        themeIcons.forEach(icon => {
            icon.src = theme === 'light' 
                ? '../../assets/images/light-mode-icon.svg'
                : '../../assets/images/dark-mode-icon.svg';
            icon.alt = theme === 'light' ? 'Light Mode Icon' : 'Dark Mode Icon';
        });
    }

    // Initialize theme toggle
    initializeThemeToggle();

    // Search functionality for header search bars
    function initializeHeaderSearch() {
        const searchBars = document.querySelectorAll('.search-bar');
        searchBars.forEach(searchBar => {
            searchBar.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm) {
                        // Redirect to search results page or perform search
                        alert(`Searching for: ${searchTerm}`);
                        // In real implementation: window.location.href = `search.html?q=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });
        });
    }

    initializeHeaderSearch();
}

// Global variables
let currentCategory = 'all';
let currentSearch = '';
let currentAdminView = 'all';

// Global functions that need to be accessible
function openListingModal(listingId) {
    const listing = mockListings.find(l => l.listingId === listingId);
    if (!listing) return;
    
    const modal = document.getElementById('listingModal');
    const modalContent = document.getElementById('modalContent');
    
    // Populate modal content based on user type
    modalContent.innerHTML = AdminView ? 
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
            <button class="report-btn" onclick="reportListing('${listing.listingId}')">
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
                <div class="modal-category">${formatCategory(listing.category)}</div>
                <div class="modal-description">${listing.description}</div>
                ${listing.reported ? '<div class="admin-badge reported-badge">REPORTED</div>' : ''}
            </div>
        </div>
        
        <div class="modal-details-grid">
            <div class="detail-item">
                <div class="detail-label">Listing ID</div>
                <div class="detail-value">${listing.listingId}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Member ID</div>
                <div class="detail-value">${listing.memberId}</div>
            </div>
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
            <button class="delete-btn" onclick="deleteListing('${listing.listingId}')">
                Delete Listing
            </button>
            ${listing.reported ? `
                <button class="save-btn" onclick="resolveReport('${listing.listingId}')">
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

// Add this function to handle reporting listings
function reportListing(listingId) {
    const listing = mockListings.find(l => l.listingId === listingId);
    if (listing) {
        const reason = prompt(`Please provide a reason for reporting "${listing.title}":`);
        if (reason !== null && reason.trim() !== '') {
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
        const index = mockListings.findIndex(l => l.listingId === listingId);
        if (index !== -1) {
            mockListings.splice(index, 1);
            closeModal();
            applyFilters();
            alert('Listing deleted successfully.');
        }
    }
}

function resolveReport(listingId) {
    const listing = mockListings.find(l => l.listingId === listingId);
    if (listing) {
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
    
    // Reset admin view if applicable
    if (AdminView) {
        toggleAdminView('all');
    } else {
        // Re-render listings
        applyFilters();
    }
}

// Initialize admin controls
function initAdminControls() {
    if (AdminView) {
        // Show admin controls
        const adminControls = document.getElementById('adminControls');
        if (adminControls) {
            adminControls.style.display = 'flex';
        }
        
        // Hide create listing button
        const createListingBtn = document.getElementById('createListingBtn');
        if (createListingBtn) {
            createListingBtn.style.display = 'none';
        }
        
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
    let filteredListings = mockListings.filter(listing => {
        // Admin view filter - FIXED VERSION
        if (AdminView && currentAdminView === 'reported' && !listing.reported) {
            return false;
        }
        
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
        <div class="listing-card ${listing.itemType === 'plant' ? 'plant-special' : 'item-special'}" 
                onclick="openListingModal('${listing.listingId}')">
            ${AdminView && listing.reported ? '<div class="admin-badge">REPORTED</div>' : ''}
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

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    // Load the appropriate header first
    loadHeader();
    
    // Then initialize the rest of the page functionality
    const listingsGrid = document.getElementById('listingsGrid');
    const modalClose = document.getElementById('modalClose');
    const categoryTabs = document.querySelectorAll('.category-tab');
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const conditionFilter = document.getElementById('conditionFilter');
    const typeFilter = document.getElementById('typeFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    // Initialize global variables
    currentCategory = 'all';
    currentSearch = '';
    currentAdminView = 'all';
    
    // Initialize admin controls
    initAdminControls();
    
    // Modal close functionality
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
    [categoryFilter, conditionFilter, typeFilter, sortFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase();
            applyFilters();
        });
    }
    
    // Initial render
    applyFilters();
});