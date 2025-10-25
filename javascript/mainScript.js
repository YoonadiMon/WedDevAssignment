const sidebarNav = document.getElementById('sidebarNav');
const cover = document.getElementById('cover');
const body = document.body;

// Get both theme toggle buttons by their unique IDs
const toggleBtn1 = document.getElementById('themeToggle1'); // Mobile
const toggleBtn2 = document.getElementById('themeToggle2'); // Desktop

const settingImg = document.getElementById('settingImg');
const profileImg = document.getElementById('profileImg');
const chatImg = document.getElementById('chatImg');
const menuBtn = document.getElementById('menuBtn');


const themeToggleLi = document.getElementById('themeToggleLi');

if (themeToggleLi) {
  themeToggleLi.addEventListener('click', toggleTheme);
}

// Unread message notification functionality
const chatboxMobile = document.getElementById('chatboxMobile');
const chatboxDesktop = document.getElementById('chatboxDesktop');

if (!isAdmin && (chatboxMobile || chatboxDesktop)) {
    // Get the unread count from PHP
    let unread = unreadCount || 0;

    const badges = [
        document.getElementById("chatBadgeDesktop"),
        document.getElementById("chatBadgeMobile")
    ];

    function updateBadge() {
        badges.forEach(badge => {
            if (badge) {
                if (unread > 0) {
                    badge.style.display = "flex";
                } else {
                    badge.style.display = "none"; 
                }
            }
        });
    }

    // Initial render
    updateBadge();

    // Periodically check for new messages (every 30 seconds)
    setInterval(() => {
        checkNewMessages();
    }, 30000);
}

// Function to check for new messages
async function checkNewMessages() {
    if (isAdmin) return;
    
    try {
        const response = await fetch('../../php/getUnread.php');
        const data = await response.json();
        
        if (data.success && data.unread_count !== unread) {
            unread = data.unread_count;
            updateBadge();
        }
    } catch (error) {
        console.error('Failed to check new messages:', error);
    }
}

function showMenu() {
    sidebarNav.style.transform = "translateX(0)";
    cover.classList.add('cover');
    document.body.classList.add('stopScroll');
}

function hideMenu() {
    sidebarNav.style.transform = "translateX(100%)";
    cover.classList.remove('cover');
    document.body.classList.remove('stopScroll');
}

// Function to toggle theme
function toggleTheme() {
    body.classList.toggle('dark-mode');
    
    // Save the current theme to localStorage
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Optional: Update button icons based on theme
    updateThemeIcons(isDark);
}

// Function to update theme-related icons for light and dark modes
function updateThemeIcons(isDark) {
    if (toggleBtn1) {
        const img1 = toggleBtn1.querySelector('img');
        if (img1) {
            img1.src = isDark ? '../../assets/images/dark-mode-icon-mobile.svg' : '../../assets/images/light-mode-icon.svg';
            img1.alt = isDark ? 'Dark Mode Icon' : 'Light Mode Icon';
        }
    }
    if (toggleBtn2) {
        const img2 = toggleBtn2.querySelector('img');
        if (img2) {
            img2.src = isDark ? '../../assets/images/dark-mode-icon.svg' : '../../assets/images/light-mode-icon.svg';
            img2.alt = isDark ? 'Dark Mode Icon' : 'Light Mode Icon';
        }
    }
    if (settingImg) {
        settingImg.src = isDark ? '../../assets/images/setting-dark.svg' : '../../assets/images/setting-light.svg';
    }
    if (profileImg) {
        profileImg.src = isDark ? '../../assets/images/profile-dark.svg' : '../../assets/images/profile-light.svg';
    }
    if (chatImg) {
        chatImg.src = isDark ? '../../assets/images/chat-dark.svg' : '../../assets/images/chat-light.svg';
    }
    if (menuBtn) {
        menuBtn.src = isDark ? '../../assets/images/icon-menu-dark.svg' : '../../assets/images/icon-menu.svg';
    }
}

// Add event listeners to both theme toggle buttons
if (toggleBtn1) {
    toggleBtn1.addEventListener('click', toggleTheme);
}

if (toggleBtn2) {
    toggleBtn2.addEventListener('click', toggleTheme);
}

// Load saved theme on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        updateThemeIcons(true);
    }
});


// Search Functionality
let currentType = 'all';
let currentQuery = '';
let currentPage = 1;
let isLoading = false;
let hasMore = true;

const searchBars = document.querySelectorAll('input[placeholder="Search..."]');
const searchContainer = document.getElementById("searchContainer");
const resultsContainer = document.getElementById("results");
const tabs = document.querySelectorAll(".tab");
const tabsContainer = document.querySelector(".tabs");
const content = document.getElementById("content");

const SEARCH_API = '../../php/globalSearch.php'; 

// Fetch search results from API
async function fetchSearchResults(type, query, page = 1) {
    if (!query.trim()) return null;

    try {
        const params = new URLSearchParams({
            type: type,
            query: query,
            page: page
        });

        const response = await fetch(`${SEARCH_API}?${params}`);
        const data = await response.json();

        if (data.success) {
            return data;
        } else {
            throw new Error(data.message || 'Search failed');
        }
    } catch (error) {
        console.error('Search error:', error);
        showError('Failed to fetch search results');
        return null;
    }
}

function renderResults(data, append = false) {
    if (!resultsContainer || !data) return;
    
    // Remove existing load more button before rendering
    const existingLoadMoreBtn = resultsContainer.querySelector('.load-more-btn');
    if (existingLoadMoreBtn) {
        existingLoadMoreBtn.remove();
    }
    
    if (!append) {
        resultsContainer.innerHTML = "";
    }

    const { results, type, query } = data;
    let items = [];

    // profiles
    if ((type === 'all' || type === 'profiles') && results.profiles && results.profiles.length > 0) {
        results.profiles.forEach(profile => {
            items.push(`
                <div class="result-item clickable" data-url="../../pages/CommonPages/viewProfile.php?userID=${profile.id}">
                    <div class="avatar-initials">${profile.initials}</div>
                    <div>
                        <h4>${profile.title}</h4>
                        <p>${profile.subtitle} • ${profile.description}</p>
                        <p>${profile.bio}</p>
                    </div>
                </div>
            `);
        });
    }

    // blogs
    if ((type === 'all' || type === 'blogs') && results.blogs && results.blogs.length > 0) {
        results.blogs.forEach(blog => {
            items.push(`
                <div class="result-item clickable" data-url="../../pages/CommonPages/readBlog.php?id=${blog.id}">
                    <div>
                        <h4>${blog.title}</h4>
                        <p>${blog.subtitle} • ${blog.category}</p>
                        <p class="description">${blog.description}</p>
                    </div>
                </div>
            `);
        });
    }

    // events
    if ((type === 'all' || type === 'events') && results.events && results.events.length > 0) {
        results.events.forEach(event => {
            items.push(`
                <div class="result-item clickable" data-url="../../pages/CommonPages/joinEvent.php?id=${event.id}">
                    <div>
                        <h4>${event.title}</h4>
                        <p>${event.subtitle} • ${event.eventType}</p>
                        <p class="description">${event.location} • ${event.date}</p>
                    </div>
                </div>
            `);
        });
    }

    // trades
    if ((type === 'all' || type === 'trades') && results.trades && results.trades.length > 0) {
        results.trades.forEach(trade => {
            items.push(`
                <div class="result-item clickable" data-url="../../pages/CommonPages/mainTrade.php?id=${trade.id}">
                    <div>
                        <h4>${trade.title}</h4>
                        <p>${trade.subtitle} • ${trade.category}</p>
                        <p class="description">${trade.description}</p>
                    </div>
                </div>
            `);
        });
    }

    if (items.length === 0 && !append) {
        resultsContainer.innerHTML = '<p class="no-results">No results found.</p>';
    } else {
        // Append or replace content
        resultsContainer.innerHTML += items.join("");
        
        // Add load more button if there are more results
        if (data.hasMore) {
            const loadMoreBtn = document.createElement('button');
            loadMoreBtn.className = 'load-more-btn';
            loadMoreBtn.textContent = 'Load More Results';
            loadMoreBtn.onclick = loadMoreResults;
            resultsContainer.appendChild(loadMoreBtn);
        }
    }
}

// Show error message
function showError(message) {
    if (resultsContainer) {
        resultsContainer.innerHTML = `<p class="error-message">${message}</p>`;
    }
}

// Handle search input with debouncing
let searchTimeout;
function handleSearchInput(e) {
    const query = e.target.value.trim();

    clearTimeout(searchTimeout);

    // Show content, hide search if query is empty
    if (query.length === 0) {
        if (content) content.style.display = "block";
        if (searchContainer) searchContainer.style.display = "none";
        if (tabsContainer) tabsContainer.style.display = "none";
        if (resultsContainer) resultsContainer.style.display = "none";
        return;
    }

    // Set timeout for debouncing (300ms)
    searchTimeout = setTimeout(async () => {
        currentQuery = query;
        currentPage = 1;
        hasMore = true;

        // Hide content, show search
        if (content) content.style.display = "none";
        if (searchContainer) searchContainer.style.display = "block";
        if (tabsContainer) tabsContainer.style.display = "flex";
        if (resultsContainer) resultsContainer.style.display = "flex";

        // Reset to "all" tab
        const activeTab = document.querySelector(".tab.active");
        if (activeTab) activeTab.classList.remove("active");
        const allTab = document.querySelector('.tab[data-type="all"]');
        if (allTab) allTab.classList.add("active");

        // Show loading state
        resultsContainer.innerHTML = '<p class="loading">Searching...</p>';

        // Fetch and render results
        const data = await fetchSearchResults('all', query);
        if (data) {
            renderResults(data);
            updateTabCounts(data.counts);
        }
    }, 300);
}

// Load more results
async function loadMoreResults() {
    if (isLoading || !hasMore) return;

    isLoading = true;
    currentPage++;

    const loadMoreBtn = document.querySelector('.load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;
    }

    const data = await fetchSearchResults(currentType, currentQuery, currentPage);
    
    if (data) {
        hasMore = data.hasMore;
        renderResults(data, true);
    } else {
        currentPage--;
    }

    isLoading = false;
    
    if (loadMoreBtn && hasMore) {
        loadMoreBtn.textContent = 'Load More Results';
        loadMoreBtn.disabled = false;
    }
}

// Update tab counts
function updateTabCounts(counts) {
    tabs.forEach(tab => {
        const type = tab.getAttribute('data-type');
        const count = counts[type] || 0;
        
        // Remove existing count badge
        const existingBadge = tab.querySelector('.count-badge');
        if (existingBadge) {
            existingBadge.remove();
        }
        
        // Add new count badge if count > 0
        if (count > 0) {
            const badge = document.createElement('span');
            badge.className = 'count-badge';
            badge.textContent = count;
            tab.appendChild(badge);
        }
    });
}

// Tab switching
tabs.forEach((tab) => {
    tab.addEventListener("click", async () => {
        if (!currentQuery) return;

        const activeTab = document.querySelector(".tab.active");
        if (activeTab) activeTab.classList.remove("active");
        tab.classList.add("active");

        const type = tab.getAttribute("data-type");
        currentType = type;
        currentPage = 1;
        hasMore = true;

        // Show loading state
        resultsContainer.innerHTML = '<p class="loading">Searching...</p>';

        // Fetch and render results for specific type
        const data = await fetchSearchResults(type, currentQuery);
        if (data) {
            renderResults(data);
        }
    });
});

// Sync search bars
function syncSearchBars(value) {
    searchBars.forEach(searchBar => {
        if (searchBar.value !== value) {
            searchBar.value = value;
        }
    });
}

// Attach listeners to all search bars
searchBars.forEach(searchBar => {
    if (searchBar) {
        searchBar.addEventListener("input", (e) => {
            const currentValue = e.target.value;
            syncSearchBars(currentValue);
            handleSearchInput(e);
        });
        
        // Handle Enter key press
        searchBar.addEventListener("keypress", (e) => {
            if (e.key === 'Enter') {
                handleSearchInput(e);
            }
        });
    }
});

// Initialize - hide search container by default
if (searchContainer) {
    searchContainer.style.display = "none";
}
if (tabsContainer) {
    tabsContainer.style.display = "none";
}
if (resultsContainer) {
    resultsContainer.style.display = "none";
}

if (resultsContainer) {
    resultsContainer.addEventListener('click', function(e) {
        const resultItem = e.target.closest('.result-item.clickable');
        if (resultItem) {
            const url = resultItem.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        }
    });
}