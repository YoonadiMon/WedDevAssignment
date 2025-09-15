const sidebarNav = document.getElementById('sidebarNav');
const cover = document.getElementById('cover');
const body = document.body;

// Get both theme toggle buttons by their unique IDs
const toggleBtn1 = document.getElementById('themeToggle1'); // Mobile
const toggleBtn2 = document.getElementById('themeToggle2'); // Desktop

const profileImg = document.getElementById('profileImg');
const chatImg = document.getElementById('chatImg');
const menuBtn = document.getElementById('menuBtn');


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

function updateThemeIcons(isDark) {
    if (toggleBtn2) {
        const img1 = toggleBtn2.querySelector('img');
        if (img1) {
            img1.src = isDark ? '/assets/images/dark-mode-icon.svg' : '/assets/images/light-mode-icon.svg';
            img1.alt = isDark ? 'Dark Mode Icon' : 'Light Mode Icon';
        }
    }
    if (profileImg) {
        profileImg.src = isDark ? '/assets/images/profile-dark.svg' : '/assets/images/profile-light.svg';
    }
    if (chatImg) {
        chatImg.src = isDark ? '/assets/images/chat-dark.svg' : '/assets/images/chat-light.svg';
    }
    if (menuBtn) {
        menuBtn.src = isDark ? '/assets/images/icon-menu-dark.svg' : '/assets/images/icon-menu.svg';
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