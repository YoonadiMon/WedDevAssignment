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

if (!isAdmin) {
    // Chatbox notification badge
    let unread = 3; // assume unread messages count

    const badges = [
        document.getElementById("chatBadgeDesktop"),
        document.getElementById("chatBadgeMobile")
    ];
    const chatboxes = [
        document.getElementById("chatboxDesktop"),
        document.getElementById("chatboxMobile")
    ];


    function updateBadge() {
        badges.forEach(badge => {
            if (unread > 0) {
                badge.textContent = unread > 99 ? "99+" : unread;
                badge.style.display = "flex";
            } else {
                badge.style.display = "none";
            }
        });
    }

    // initial render
    updateBadge();

    // reset on click (works for both desktop + mobile)
    chatboxes.forEach(chatbox => {
        chatbox.addEventListener("click", (e) => {
            // e.preventDefault();   // stop page reload, just too test when on click the badge resets
            unread = 0;
            updateBadge();
        });
    });
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


// Fake data
const profiles = [
    {UserID:"u1", AvatarURL:"https://i.pravatar.cc/50?img=1", Username:"jdoe", FirstName:"John", LastName:"Doe"},
    {UserID:"u2", AvatarURL:"https://i.pravatar.cc/50?img=2", Username:"asmith", FirstName:"Alice", LastName:"Smith"}
];

const blogs = [
    {BlogID:"b1", PostID:"p1", Title:"Intro to Web Dev", Preview:"Learn how to build modern websites", DateTime: "2025-07-29 16:20:00"},
    {BlogID:"b2", PostID:"p2", Title:"CSS Tricks", Preview:"Make your designs shine with these tips", DateTime: "2025-08-29 16:20:00"}
];

const events = [
    {EventID:"e1", PostID:"p3", Title:"Recycling Talk", Location:"KL", StartDate:"2025-09-21", ImageURL:"https://via.placeholder.com/50", DateTime: "2025-07-29 16:20:00"},
    {EventID:"e2", PostID:"p4", Title:"DIY Workshop", Location:"KL", StartDate:"2025-10-10", ImageURL:"https://via.placeholder.com/50", DateTime: "2025-08-29 16:20:00"}
];

const trades = [
    {ListingID:"li1", MemberID:"u1", Title:"Cute Hoodie", Tags:"High Quality Cloth, Cute Heart Pattern", ImageURL:"https://via.placeholder.com/50", DateTime: "2025-07-29 16:20:00"},
    {ListingID:"li2", MemberID:"u2", Title:"Used Iphone", Tags:"Still in good condition", ImageURL:"https://via.placeholder.com/50", DateTime: "2025-08-29 16:20:00"}
];

const tickets = [
    {TicketID: "t1", MemberID: "m1", Category: "Payment", Subject: "Payment not reflected", DateTime: "2025-08-29 16:20:00", Status: "Open"},
    {TicketID: "t2", MemberID: "m2", Category: "Event", Subject: "Registration Issue", DateTime: "2025-08-29 16:20:00", Status: "Close"}
];

const faqs = [
    {FaqID: "f1", AdminID: "a1", Category: "Account Issues", Question: "How to reset my password?", DateUpdated: "2025-06-29 14:32:00"}
];

// for all search bars
const searchBars = document.querySelectorAll('input[placeholder="Search..."]');

const searchContainer = document.getElementById("searchContainer");
const resultsContainer = document.getElementById("results");
const tabs = document.querySelectorAll(".tab");
const tabsContainer = document.querySelector(".tabs");
const content = document.getElementById("content");

// Render results
function renderResults(type, query) {
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = "";
    const lowerQuery = query.toLowerCase();

    function match(text) {
        return text && text.toLowerCase().includes(lowerQuery);
    }

    let items = [];

    if (isAdmin && (type === "all" || type === "tickets")) {
        items.push(
            ...tickets
                .filter((t) => match(t.TicketID)|| match(t.MemberID) || match(t.Subject))
                .map(
                    (t) => `
                    <div class="result-item clickable" onclick="window.location.href='../../pages/adminPages/aHelpTicket.html?ticketId=${t.TicketID}'">
                        <div>
                            <h4>${t.Subject} #${t.TicketID}</h4>
                            <p>Status: ${t.Status}</p>
                        </div>
                    </div>`
                )
        );
    }

    if (type === "all" || type === "profiles") {
        items.push(
            ...profiles
                .filter((p) => match(p.Username) || match(p.FirstName) || match(p.LastName))
                .map(
                    (p) => `
                    <div class="result-item clickable" onclick="window.location.href='../../pages/MemberPages/mProfile.html?id=${p.UserID}'">
                        <img src="${p.AvatarURL}" alt="${p.Username}">
                        <div>
                            <h4>${p.FirstName} ${p.LastName}</h4>
                            <p>@${p.Username}</p>
                        </div>
                    </div>`
                )
        );
    }

    if (type === "all" || type === "blogs") {
        items.push(
            ...blogs
                .filter((b) => match(b.Title) || match(b.Preview))
                .map(
                    (b) => `
                    <div class="result-item clickable" onclick="window.location.href='../../pages/CommonPages/readBlog.html?blogId=${b.BlogID}'">
                        <div>
                            <h4>${b.Title}</h4>
                            <p>${b.Preview}</p>
                        </div>
                    </div>`
                )
        );
    }

    if (type === "all" || type === "events") {
        items.push(
            ...events
                .filter((e) => match(e.Title) || match(e.Location))
                .map(
                    (e) => `
                    <div class="result-item clickable" onclick="window.location.href='../../pages/CommonPages/joinEvent.html?eventId=${e.EventID}'">
                    <img src="${e.ImageURL}" alt="${e.Title}">
                        <div>
                            <h4>${e.Title}</h4>
                            <p>>>> ${e.Location}</p>
                        </div>
                    </div>`
                )
        );
    }

    if (type === "all" || type === "trades") {
        items.push(
            ...trades
                .filter((tr) => match(tr.Title) || match(tr.Tags))
                .map(
                    (tr) => `
                    <div class="result-item clickable" onclick="window.location.href='../../pages/CommonPages/mainTrade.html?listingId=${tr.ListingID}'">
                        <img src="${tr.ImageURL}" alt="${tr.Title}">
                        <div>
                            <h4>${tr.Title}</h4>
                            <p># ${tr.Tags}</p>
                        </div>
                    </div>`
                )
        );
    }

    if (isAdmin && (type === "all" || type === "faqs")) {
        items.push(
            ...faqs
                .filter((f) => match(f.FaqID)|| match(f.Category) || match(f.Question))
                .map(
                    (f) => `
                    <div class="result-item clickable" onclick="window.location.href='../../pages/adminPages/FAQ.html?FaqId=${f.FaqID}'">
                        <div>
                            <h4>FAQ #${f.FaqID}</h4>
                            <p>Q: ${f.Question}</p>
                        </div>
                    </div>`
                )
        );
    }

    resultsContainer.innerHTML = items.join("") || "<p>No results found.</p>";
}

// Handle search input
function handleSearchInput(e) {
    const query = e.target.value.trim();

    // Show content, hide search
    if (query.length === 0) {
        if (content) content.style.display = "block";
        if (searchContainer) searchContainer.style.display = "none";
        if (tabsContainer) tabsContainer.style.display = "none";
        if (resultsContainer) resultsContainer.style.display = "none";
        return;
    }

    // Hide content, show search
    if (content) content.style.display = "none";
    if (searchContainer) searchContainer.style.display = "block";
    if (tabsContainer) tabsContainer.style.display = "flex";
    if (resultsContainer) resultsContainer.style.display = "flex";

    // Always start with "all"
    const activeTab = document.querySelector(".tab.active");
    if (activeTab) activeTab.classList.remove("active");
    const allTab = document.querySelector('.tab[data-type="all"]');
    if (allTab) allTab.classList.add("active");

    renderResults("all", query);
}

// Tab switching
tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        const activeTab = document.querySelector(".tab.active");
        if (activeTab) activeTab.classList.remove("active");
        tab.classList.add("active");

        const type = tab.getAttribute("data-type");
        // Get the current query from any search bar that has a value
        let query = "";
        searchBars.forEach(searchBar => {
            if (searchBar && searchBar.value.trim()) {
                query = searchBar.value.trim();
            }
        });
        
        if (query.length > 0) {
            renderResults(type, query);
        }
    });
});

// Attach listeners 
searchBars.forEach(searchBar => {
    if (searchBar) {
        searchBar.addEventListener("input", handleSearchInput);
        
        // Also sync the search bars - when you type in one, update the other
        searchBar.addEventListener("input", (e) => {
            const currentValue = e.target.value;
            searchBars.forEach(otherSearchBar => {
                if (otherSearchBar !== e.target) {
                    otherSearchBar.value = currentValue;
                }
            });
        });
    }
});
