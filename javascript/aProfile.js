



const defaults = {
    fullName: "Alice Clark",
    username: "alice_is_me",
    email: "alicealice@gmail.com",
    oldPassword: "oldpassword123", // current password
    password: "123456abc", // new password will overwrite this after successful change
    bio: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend lacus quam, et lacinia turpis fringilla a.",
    country: "Malaysia",
    gender: "Female", // Changed from "female" to "Female" to match HTML options
    avatarData: "",
    avatarColor: ""
};

const elements = {
    fullName: document.getElementById('fullName'),
    username: document.getElementById('username'),
    email: document.getElementById('email'),
    oldPassword: document.getElementById('old-password'),
    bio: document.getElementById('bio'),
    bioCounter: document.getElementById('bioCounter'),
    country: document.getElementById('country'),
    avatarImg: document.getElementById('avatarImg'),
    avatarWrap: document.getElementById('avatar'),
    changeBtn: document.getElementById('changeBtn'),
    fileHint: document.getElementById('fileHint'),
    saveBtn: document.getElementById('saveBtn'),
    toast: document.getElementById('toast'),
    password: document.getElementById('password'),
    confirmPassword: document.getElementById('confirmPassword'),
    gender: document.getElementById('gender')
};

let state = {};

// Avatar colors
const avatarColors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'];

// Bio word counter - FIXED: removed nested event listener
elements.bio.addEventListener('input', () => {
    const words = elements.bio.value.trim().split(/\s+/).filter(word => word.length > 0);
    let wordCount = words.length;

    if (wordCount > 500) {
        elements.bio.value = words.slice(0, 500).join(' ');
        wordCount = 500;
        showToast("Bio cannot exceed 500 words.", true);
    }

    elements.bioCounter.textContent = `${wordCount} / 500 words`;

    // Change color near limit
    if (wordCount >= 480) {
        elements.bioCounter.classList.add('limit');
    } else {
        elements.bioCounter.classList.remove('limit');
    }
});

// Load profile (defaults only)
function loadProfile() {
    const profile = defaults;

    elements.fullName.value = profile.fullName;
    elements.username.value = profile.username;
    elements.email.value = profile.email;
    elements.bio.value = profile.bio;
    elements.country.value = profile.country;
    elements.gender.value = profile.gender || "";

    // Password fields always blank on load
    elements.oldPassword.value = "";
    elements.password.value = "";
    elements.confirmPassword.value = "";

    generateInitialAvatar(profile.fullName, profile.avatarColor);
    
    // Update bio counter on load
    updateBioCounter();
}

// Update bio counter helper function
function updateBioCounter() {
    const words = elements.bio.value.trim().split(/\s+/).filter(word => word.length > 0);
    const wordCount = words.length;
    elements.bioCounter.textContent = `${wordCount} / 500 words`;
    
    if (wordCount >= 480) {
        elements.bioCounter.classList.add('limit');
    } else {
        elements.bioCounter.classList.remove('limit');
    }
}

function setAvatarFromDataURL(dataURL) {
    elements.avatarImg.src = dataURL;
    elements.avatarImg.style.display = "block";
    elements.avatarWrap.style.background = "transparent";
}

function generateInitialAvatar(name, savedColor = null) {
    const initials = name.split(/\s+/).map(s => s[0]).slice(0, 2).join('').toUpperCase();
    const color = savedColor || avatarColors[Math.floor(Math.random() * avatarColors.length)];

    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 300;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = color;
    ctx.fillRect(0, 0, 300, 300);

    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 120px Inter, Arial, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(initials, 150, 160);

    const dataURL = canvas.toDataURL('image/png');
    setAvatarFromDataURL(dataURL);

    state.currentAvatarColor = color;
    state.pendingAvatarDataURL = dataURL;
}

// Change avatar button - generates new random avatar
elements.changeBtn.addEventListener('click', () => {
    const fullName = elements.fullName.value.trim() || defaults.fullName;
    generateInitialAvatar(fullName);
    showToast("New avatar style generated!");
});

// Save button
elements.saveBtn.addEventListener('click', () => {
    const fullName = elements.fullName.value.trim();
    const username = elements.username.value.trim();
    const email = elements.email.value.trim();
    const currentPassword = elements.oldPassword.value.trim();
    const newPassword = elements.password.value.trim();
    const confirmPassword = elements.confirmPassword.value.trim();
    const bio = elements.bio.value.trim();
    const gender = elements.gender.value;

    // Validation
    if (!fullName || !username) {
        showToast("Full name and username are required.", true);
        return;
    }
    
    if (email && !validateEmail(email)) {
        showToast("Invalid email format.", true);
        return;
    }

    // Check bio word count - now checks if empty bio would result in 0 words
    const words = bio.split(/\s+/).filter(word => word.length > 0);
    if (bio.length > 0 && words.length === 0) {
        showToast("Bio cannot be only whitespace.", true);
        return;
    }
    if (words.length > 500) {
        showToast("Bio cannot exceed 500 words. Please shorten it before saving.", true);
        return;
    }

    // If user tries to set a new password, check current one first
    if (newPassword.length > 0 || confirmPassword.length > 0) {
        if (currentPassword !== defaults.oldPassword) {
            showToast("Current password is incorrect!", true);
            // Clear all password fields 
            elements.oldPassword.value = "";
            elements.password.value = "";
            elements.confirmPassword.value = "";
            return;
        }
        if (newPassword !== confirmPassword) {
            showToast("New passwords do not match.", true);
            elements.password.value = "";
            elements.confirmPassword.value = "";
            return;
        }
        
        // Validate password strength
        if (newPassword.length < 6) {
            showToast("Password must be at least 6 characters long.", true);
            return;
        }
        if (newPassword.length > 60) {
            showToast("Password cannot exceed 60 characters.", true);
            return;
        }
        
        // ✅ Password successfully updated
        defaults.password = newPassword;
        defaults.oldPassword = newPassword; // new password becomes the "current" one
        
        // Update other fields
        defaults.fullName = fullName;
        defaults.username = username;
        defaults.email = email;
        defaults.bio = bio;
        defaults.gender = gender;
        defaults.country = elements.country.value;
        defaults.avatarColor = state.currentAvatarColor;
        defaults.avatarData = state.pendingAvatarDataURL;
        
        showToast("Profile and password updated successfully ✓");
    } else {
        // Update profile without password change
        defaults.fullName = fullName;
        defaults.username = username;
        defaults.email = email;
        defaults.bio = bio;
        defaults.gender = gender;
        defaults.country = elements.country.value;
        defaults.avatarColor = state.currentAvatarColor;
        defaults.avatarData = state.pendingAvatarDataURL;
        
        showToast("Profile updated successfully ✓");
    }

    // Clear all password fields after save
    elements.oldPassword.value = "";
    elements.password.value = "";
    elements.confirmPassword.value = "";
});

// Email validation
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Toast
let toastTimer = null;
function showToast(msg, isError = false) {
    clearTimeout(toastTimer);
    elements.toast.textContent = msg;
    elements.toast.style.background = isError ? "#dc2626" : "#16a34a";
    elements.toast.classList.add('show');
    toastTimer = setTimeout(() => {
        elements.toast.classList.remove('show');
    }, 3000);
}

// Init
window.addEventListener('load', () => {
    loadProfile();
});