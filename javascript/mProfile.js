// const defaults = {
//     fullName: "Alice Clark",
//     username: "alice_is_me",
//     phone: "+6 011-555-1928",
//     email: "alicealice@gmail.com",
//     gender: "Female",
//     bio: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend lacus quam, et lacinia turpis fringilla a.",
//     location: "Malaysia",
//     avatarData: ""
// };

// const elements = {
//     fullName: document.getElementById('fullName'),
//     username: document.getElementById('username'),
//     phone: document.getElementById('phone'),
//     email: document.getElementById('email'),
//     gender: document.getElementById('gender'),
//     bio: document.getElementById('bio'),
//     location: document.getElementById('location'),
//     avatarImg: document.getElementById('avatarImg'),
//     avatarWrap: document.getElementById('avatar'),
//     fileInput: document.getElementById('fileInput'),
//     changeBtn: document.getElementById('changeBtn'),
//     fileHint: document.getElementById('fileHint'),
//     saveBtn: document.getElementById('saveBtn'),
//     toast: document.getElementById('toast')
// };

// let state = {};

// // Load profile from localStorage or defaults
// function loadProfile() {
//     const stored = localStorage.getItem('demoProfile_v1');
//     const profile = stored ? JSON.parse(stored) : defaults;

//     elements.fullName.value = profile.fullName || "";
//     elements.username.value = profile.username || "";
//     elements.phone.value = profile.phone || "";
//     elements.email.value = profile.email || "";
//     elements.gender.value = profile.gender || "";
//     elements.bio.value = profile.bio || "";
//     elements.location.value = profile.location || "";

//     if (profile.avatarData) {
//         setAvatarFromDataURL(profile.avatarData);
//         elements.fileHint.textContent = "Using saved avatar";
//     } else {
//         elements.avatarImg.style.display = "none";
//         elements.avatarWrap.style.background = "#e6e6e6";
//         elements.fileHint.textContent = "No file selected";
//     }
// }

// function setAvatarFromDataURL(dataURL) {
//     elements.avatarImg.src = dataURL;
//     elements.avatarImg.style.display = "block";
//     elements.avatarWrap.style.background = "transparent";
// }

// // Handle change button
// elements.changeBtn.addEventListener('click', () => {
//     elements.fileInput.value = "";
//     elements.fileInput.click();
// });

// // Validate image input
// elements.fileInput.addEventListener('change', (e) => {
//     const file = e.target.files && e.target.files[0];
//     if (!file) {
//         elements.fileHint.textContent = "No file selected";
//         return;
//     }
//     if (!file.type || !file.type.startsWith('image/')) {
//         showToast("Selected file is not an image. Please pick an image file.", true);
//         elements.fileInput.value = "";
//         elements.fileHint.textContent = "No file selected";
//         return;
//     }
//     if (file.size > 5 * 1024 * 1024) {
//         showToast("Image too large. Please choose <5MB.", true);
//         elements.fileInput.value = "";
//         elements.fileHint.textContent = "No file selected";
//         return;
//     }
//     const reader = new FileReader();
//     reader.onload = function (evt) {
//         const dataURL = evt.target.result;
//         setAvatarFromDataURL(dataURL);
//         elements.fileHint.textContent = file.name;
//         state.pendingAvatarDataURL = dataURL;
//     };
//     reader.readAsDataURL(file);
// });

// // Save changes
// elements.saveBtn.addEventListener('click', () => {
//     const fullName = elements.fullName.value.trim();
//     const username = elements.username.value.trim();
//     const email = elements.email.value.trim();

//     if (!fullName || !username) {
//         showToast("Full name and username are required.", true);
//         return;
//     }
//     if (email && !validateEmail(email)) {
//         showToast("Invalid email.", true);
//         return;
//     }

//     const profile = {
//         fullName,
//         username,
//         phone: elements.phone.value.trim(),
//         email,
//         gender: elements.gender.value,
//         bio: elements.bio.value.trim(),
//         location: elements.location.value,
//         avatarData: state.pendingAvatarDataURL ||
//             ((localStorage.getItem('demoProfile_v1') && JSON.parse(localStorage.getItem('demoProfile_v1')).avatarData) || "")
//     };

//     localStorage.setItem('demoProfile_v1', JSON.stringify(profile));
//     showToast("Profile saved ✓");
// });

// function validateEmail(email) {
//     return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
// }

// // Toast
// let toastTimer = null;
// function showToast(msg, isError) {
//     clearTimeout(toastTimer);
//     elements.toast.textContent = msg;
//     elements.toast.style.background = isError ? "#b00020" : "#222";
//     elements.toast.classList.add('show');
//     toastTimer = setTimeout(() => elements.toast.classList.remove('show'), 3000);
// }

// // Placeholder avatar initials
// function setPlaceholderWithInitials(name) {
//     const initials = name.split(/\s+/).map(s => s[0]).slice(0, 2).join('').toUpperCase();
//     const canvas = document.createElement('canvas');
//     canvas.width = canvas.height = 500;
//     const ctx = canvas.getContext('2d');
//     ctx.fillStyle = '#d8d8d8';
//     ctx.fillRect(0, 0, 500, 500);
//     ctx.fillStyle = '#7a7a7a';
//     ctx.font = 'bold 200px sans-serif';
//     ctx.textAlign = 'center';
//     ctx.textBaseline = 'middle';
//     ctx.fillText(initials, 250, 270);
//     const url = canvas.toDataURL('image/png');
//     setAvatarFromDataURL(url);
// }

// window.addEventListener('load', () => {
//     loadProfile();
//     const stored = localStorage.getItem('demoProfile_v1');
//     if (!stored) {
//         setPlaceholderWithInitials(defaults.fullName);
//     } else {
//         const profile = JSON.parse(stored);
//         if (!profile.avatarData) {
//             setPlaceholderWithInitials(profile.fullName || defaults.fullName);
//         }
//     }
// });



// let cropper;
// const cropperModal = document.getElementById('cropperModal');
// const cropperImage = document.getElementById('cropperImage');
// const cropConfirmBtn = document.getElementById('cropConfirmBtn');
// const cropCancelBtn = document.getElementById('cropCancelBtn');

// elements.fileInput.addEventListener('change', (e) => {
//     const file = e.target.files && e.target.files[0];
//     if (!file) {
//         elements.fileHint.textContent = "No file selected";
//         return;
//     }
//     if (!file.type || !file.type.startsWith('image/')) {
//         showToast("Selected file is not an image. Please pick an image file.", true);
//         elements.fileInput.value = "";
//         elements.fileHint.textContent = "No file selected";
//         return;
//     }
//     if (file.size > 5 * 1024 * 1024) {
//         showToast("Image too large. Please choose <5MB.", true);
//         elements.fileInput.value = "";
//         elements.fileHint.textContent = "No file selected";
//         return;
//     }
//     const reader = new FileReader();
//     reader.onload = function (evt) {
//         cropperImage.src = evt.target.result;
//         cropperModal.style.display = 'flex';

//         // Destroy existing cropper if any
//         if (cropper) {
//             cropper.destroy();
//         }
//         cropper = new Cropper(cropperImage, {
//             aspectRatio: 1, // Square crop for avatar
//             viewMode: 1,
//             autoCropArea: 1,
//         });
//     };
//     reader.readAsDataURL(file);
// });

// cropConfirmBtn.addEventListener('click', () => {
//     if (cropper) {
//         cropper.getCroppedCanvas().toBlob(blob => {
//             const reader = new FileReader();
//             reader.onloadend = () => {
//                 const croppedDataUrl = reader.result;
//                 setAvatarFromDataURL(croppedDataUrl);
//                 elements.fileHint.textContent = "Cropped image ready";
//                 state.pendingAvatarDataURL = croppedDataUrl;
//                 cropperModal.style.display = 'none';
//                 cropper.destroy();
//                 cropper = null;
//             };
//             reader.readAsDataURL(blob);
//         }, 'image/png');
//     }
// });

// cropCancelBtn.addEventListener('click', () => {
//     cropperModal.style.display = 'none';
//     if (cropper) {
//         cropper.destroy();
//         cropper = null;
//     }
//     elements.fileInput.value = '';
//     elements.fileHint.textContent = "No file selected";
// });


const defaults = {
    fullName: "Alice Clark",
    username: "alice_is_me",
    phone: "+6 011-555-1928",
    email: "alicealice@gmail.com",
    gender: "Female",
    bio: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend lacus quam, et lacinia turpis fringilla a.",
    location: "Malaysia",
    avatarData: "",
    avatarColor: ""
};

const elements = {
    fullName: document.getElementById('fullName'),
    username: document.getElementById('username'),
    phone: document.getElementById('phone'),
    email: document.getElementById('email'),
    gender: document.getElementById('gender'),
    bio: document.getElementById('bio'),
    location: document.getElementById('location'),
    avatarImg: document.getElementById('avatarImg'),
    avatarWrap: document.getElementById('avatar'),
    changeBtn: document.getElementById('changeBtn'),
    fileHint: document.getElementById('fileHint'),
    saveBtn: document.getElementById('saveBtn'),
    toast: document.getElementById('toast')
};

let state = {};

// Color palette for avatars
const avatarColors = [
    '#FF6B6B', // Red
    '#4ECDC4', // Teal
    '#45B7D1', // Blue
    '#96CEB4', // Green
    '#FFEAA7', // Yellow
    '#DDA0DD', // Plum
    '#98D8C8', // Mint
    '#F7DC6F', // Light Yellow
    '#BB8FCE', // Light Purple
    '#85C1E9', // Light Blue
    '#F8C471', // Orange
    '#82E0AA', // Light Green
    '#F1948A', // Light Red
    '#AED6F1', // Sky Blue
    '#A9DFBF'  // Pale Green
];

// Load profile from localStorage or defaults
function loadProfile() {
    const stored = localStorage.getItem('demoProfile_v1');
    const profile = stored ? JSON.parse(stored) : defaults;

    elements.fullName.value = profile.fullName || "";
    elements.username.value = profile.username || "";
    elements.phone.value = profile.phone || "";
    elements.email.value = profile.email || "";
    elements.gender.value = profile.gender || "";
    elements.bio.value = profile.bio || "";
    elements.location.value = profile.location || "";

    if (profile.avatarData) {
        setAvatarFromDataURL(profile.avatarData);
        elements.fileHint.textContent = "Custom avatar";
    } else {
        generateInitialAvatar(profile.fullName || defaults.fullName, profile.avatarColor);
        elements.fileHint.textContent = "Generated avatar";
    }
}

function setAvatarFromDataURL(dataURL) {
    elements.avatarImg.src = dataURL;
    elements.avatarImg.style.display = "block";
    elements.avatarWrap.style.background = "transparent";
}

// Generate avatar with initials and random color
function generateInitialAvatar(name, savedColor = null) {
    const cleanName = name.trim() || 'User';
    const initials = cleanName.split(/\s+/)
        .map(s => s[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
    
    // Use saved color or generate one based on name
    let backgroundColor;
    if (savedColor) {
        backgroundColor = savedColor;
    } else {
        // Generate consistent color based on name hash
        const hash = cleanName.split('').reduce((a, b) => {
            a = ((a << 5) - a) + b.charCodeAt(0);
            return a & a;
        }, 0);
        backgroundColor = avatarColors[Math.abs(hash) % avatarColors.length];
    }
    
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 300;
    const ctx = canvas.getContext('2d');
    
    // Background with gradient
    const gradient = ctx.createRadialGradient(150, 150, 0, 150, 150, 150);
    gradient.addColorStop(0, backgroundColor);
    gradient.addColorStop(1, adjustBrightness(backgroundColor, -20));
    
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 300, 300);
    
    // Text shadow for better readability
    ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
    ctx.shadowBlur = 4;
    ctx.shadowOffsetY = 2;
    
    // Text
    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 120px Inter, Arial, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(initials, 150, 160);
    
    const dataURL = canvas.toDataURL('image/png');
    setAvatarFromDataURL(dataURL);
    
    // Store the color and avatar data
    state.currentAvatarColor = backgroundColor;
    state.pendingAvatarDataURL = dataURL;
    
    return { dataURL, color: backgroundColor };
}

// Adjust color brightness
function adjustBrightness(hex, percent) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    
    const newR = Math.max(0, Math.min(255, r + (r * percent / 100)));
    const newG = Math.max(0, Math.min(255, g + (g * percent / 100)));
    const newB = Math.max(0, Math.min(255, b + (b * percent / 100)));
    
    return `#${Math.round(newR).toString(16).padStart(2, '0')}${Math.round(newG).toString(16).padStart(2, '0')}${Math.round(newB).toString(16).padStart(2, '0')}`;
}

// Handle change button - now generates new avatar with random color
elements.changeBtn.addEventListener('click', () => {
    const name = elements.fullName.value.trim() || elements.username.value.trim() || 'User';
    // Pick a random color instead of hash-based
    const randomColor = avatarColors[Math.floor(Math.random() * avatarColors.length)];
    generateInitialAvatar(name, randomColor);
    elements.fileHint.textContent = "New avatar generated!";
    showToast("New avatar style generated!");
});

// Update avatar when name changes
elements.fullName.addEventListener('input', () => {
    const name = elements.fullName.value.trim();
    if (name.length > 0) {
        // Keep current color, just update initials
        const currentColor = state.currentAvatarColor || avatarColors[0];
        generateInitialAvatar(name, currentColor);
        elements.fileHint.textContent = "Avatar updated";
    }
});

elements.username.addEventListener('input', () => {
    // Only update if full name is empty
    if (!elements.fullName.value.trim()) {
        const username = elements.username.value.trim();
        if (username.length > 0) {
            const currentColor = state.currentAvatarColor || avatarColors[0];
            generateInitialAvatar(username, currentColor);
            elements.fileHint.textContent = "Avatar updated";
        }
    }
});

// Save changes
elements.saveBtn.addEventListener('click', () => {
    const fullName = elements.fullName.value.trim();
    const username = elements.username.value.trim();
    const email = elements.email.value.trim();

    if (!fullName || !username) {
        showToast("Full name and username are required.", true);
        return;
    }
    if (email && !validateEmail(email)) {
        showToast("Invalid email format.", true);
        return;
    }

    const profile = {
        fullName,
        username,
        phone: elements.phone.value.trim(),
        email,
        gender: elements.gender.value,
        bio: elements.bio.value.trim(),
        location: elements.location.value,
        avatarData: state.pendingAvatarDataURL || '',
        avatarColor: state.currentAvatarColor || ''
    };

    localStorage.setItem('demoProfile_v1', JSON.stringify(profile));
    showToast("Profile saved successfully! ✓");
    
    // Clear pending data after saving
    delete state.pendingAvatarDataURL;
});

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Enhanced toast function
let toastTimer = null;
function showToast(msg, isError = false) {
    clearTimeout(toastTimer);
    elements.toast.textContent = msg;
    elements.toast.style.background = isError ? "#dc2626" : "#16a34a";
    elements.toast.style.color = "#ffffff";
    elements.toast.classList.add('show');
    
    toastTimer = setTimeout(() => {
        elements.toast.classList.remove('show');
    }, 3000);
}

// Color picker modal functions (optional enhancement)
function showColorPicker() {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: var(--bg-color, white);
        padding: 24px;
        border-radius: 12px;
        max-width: 400px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    `;
    
    content.innerHTML = `
        <h3 style="margin: 0 0 20px 0; color: var(--text-heading);">Choose Avatar Color</h3>
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px;">
            ${avatarColors.map(color => `
                <button style="width: 50px; height: 50px; border-radius: 50%; border: 3px solid #ddd; cursor: pointer; transition: transform 0.2s;" 
                        data-color="${color}" 
                        onmouseover="this.style.transform='scale(1.1)'" 
                        onmouseout="this.style.transform='scale(1)'"
                        style="background-color: ${color}"></button>
            `).join('')}
        </div>
        <div style="text-align: center;">
            <button style="padding: 8px 16px; margin: 0 8px; border: none; border-radius: 6px; cursor: pointer; background: #ddd;">Cancel</button>
        </div>
    `;
    
    // Add color selection logic
    content.querySelectorAll('[data-color]').forEach(btn => {
        btn.style.backgroundColor = btn.dataset.color;
        btn.addEventListener('click', () => {
            const name = elements.fullName.value.trim() || elements.username.value.trim() || 'User';
            generateInitialAvatar(name, btn.dataset.color);
            elements.fileHint.textContent = "Color updated!";
            showToast("Avatar color changed!");
            document.body.removeChild(modal);
        });
    });
    
    // Cancel button
    content.querySelector('button:last-child').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    
    // Close on background click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

// Initialize on page load
window.addEventListener('load', () => {
    loadProfile();
});

// Reset avatar with random color
function resetAvatar() {
    const name = elements.fullName.value.trim() || elements.username.value.trim() || 'User';
    const randomColor = avatarColors[Math.floor(Math.random() * avatarColors.length)];
    generateInitialAvatar(name, randomColor);
    elements.fileHint.textContent = "Avatar reset with new color";
    showToast("Avatar refreshed!");
}