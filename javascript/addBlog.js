




let selectedTags = [];
let selectedCategory = '';
let isDraftLoaded = false;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDraftIfExists();
    setupAutoSave();
    initializeTagSelection();
    initializeCategorySelection();
    updateSelectedTags();
});

// Character counters
document.getElementById('blogTitle').addEventListener('input', function() {
    document.getElementById('titleCount').textContent = this.value.length;
});

document.getElementById('blogExcerpt').addEventListener('input', function() {
    document.getElementById('excerptCount').textContent = this.value.length;
});

document.getElementById('blogContent').addEventListener('input', function() {
    document.getElementById('contentCount').textContent = this.value.length;
});

// Initialize category selection
function initializeCategorySelection() {
    document.querySelectorAll('.category-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedCategory = this.getAttribute('data-category');
            document.getElementById('selectedCategory').value = selectedCategory;
        });
    });
}

// Initialize tag selection
function initializeTagSelection() {
    document.querySelectorAll('.tag-item').forEach(tag => {
        tag.addEventListener('click', function() {
            const tagName = this.getAttribute('data-tag');
            if (selectedTags.includes(tagName)) {
                selectedTags = selectedTags.filter(t => t !== tagName);
                this.classList.remove('selected');
            } else {
                if (selectedTags.length < 8) {
                    selectedTags.push(tagName);
                    this.classList.add('selected');
                } else {
                    showNotification('You can select up to 8 tags only', 'warning');
                    return;
                }
            }
            updateSelectedTags();
        });
    });
}

// // Add custom tag
// function addCustomTag() {
//     const customTagInput = document.getElementById('customTag');
//     const tagName = customTagInput.value.trim().toLowerCase();
    
//     if (tagName === '') {
//         showNotification('Please enter a tag name', 'warning');
//         return;
//     }

//     // Validate tag name (alphanumeric and spaces only)
//     if (!/^[a-z0-9\s-]+$/i.test(tagName)) {
//         showNotification('Tag can only contain letters, numbers, spaces and hyphens', 'warning');
//         return;
//     }

//     if (selectedTags.includes(tagName)) {
//         showNotification('This tag is already selected', 'warning');
//         return;
//     }

//     if (selectedTags.length >= 8) {
//         showNotification('You can select up to 8 tags only', 'warning');
//         return;
//     }

//     selectedTags.push(tagName);
//     customTagInput.value = '';
//     updateSelectedTags();
//     showNotification(`Tag "${tagName}" added successfully`, 'success');
// }

// Update selected tags display
function updateSelectedTags() {
    const container = document.getElementById('selectedTags');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (selectedTags.length === 0) {
        container.innerHTML = '<p style="color: var(--text-color-2); font-size: 0.875rem; margin: 0;">No tags selected yet. Select from popular tags or add your own.</p>';
        return;
    }
    
    selectedTags.forEach(tag => {
        const tagElement = document.createElement('div');
        tagElement.className = 'selected-tag';
        
        const tagText = document.createTextNode(tag);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = '&times;';
        removeBtn.title = 'Remove tag';
        removeBtn.onclick = function() {
            removeTag(tag);
        };
        
        tagElement.appendChild(tagText);
        tagElement.appendChild(removeBtn);
        container.appendChild(tagElement);
    });
}

// Remove tag
function removeTag(tagName) {
    selectedTags = selectedTags.filter(t => t !== tagName);
    
    // Remove selected class from popular tags
    document.querySelectorAll('.tag-item').forEach(tag => {
        if (tag.getAttribute('data-tag') === tagName) {
            tag.classList.remove('selected');
        }
    });
    
    updateSelectedTags();
    showNotification(`Tag "${tagName}" removed`, 'info');
}

// Validate form
function validateForm() {
    const title = document.getElementById('blogTitle').value.trim();
    const excerpt = document.getElementById('blogExcerpt').value.trim();
    const content = document.getElementById('blogContent').value.trim();

    if (!title) {
        showNotification('Please enter a blog title', 'error');
        document.getElementById('blogTitle').focus();
        return false;
    }

    if (!excerpt) {
        showNotification('Please enter a blog excerpt', 'error');
        document.getElementById('blogExcerpt').focus();
        return false;
    }

    if (!selectedCategory) {
        showNotification('Please select a category', 'error');
        document.querySelector('.category-grid').scrollIntoView({ behavior: 'smooth' });
        return false;
    }

    if (selectedTags.length === 0) {
        showNotification('Please add at least one tag', 'error');
        document.getElementById('customTag').focus();
        return false;
    }

    if (!content) {
        showNotification('Please enter blog content', 'error');
        document.getElementById('blogContent').focus();
        return false;
    }

    return true;
}

// Clear form
function clearForm() {
    if (confirm('Are you sure you want to clear all the content? This action cannot be undone.')) {
        // Clear all input fields
        document.getElementById('blogTitle').value = '';
        document.getElementById('blogExcerpt').value = '';
        document.getElementById('blogContent').value = '';
        document.getElementById('customTag').value = '';
        
        // Reset character counts
        document.getElementById('titleCount').textContent = '0';
        document.getElementById('excerptCount').textContent = '0';
        document.getElementById('contentCount').textContent = '0';
        
        // Clear category selection
        selectedCategory = '';
        document.getElementById('selectedCategory').value = '';
        document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
        
        // Clear tags
        selectedTags = [];
        document.querySelectorAll('.tag-item').forEach(tag => tag.classList.remove('selected'));
        updateSelectedTags();
        
        // Clear auto-save
        sessionStorage.removeItem('blogAutoSave');
        
        showNotification('Form cleared successfully!', 'info');
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Auto-save every 2 minutes
function setupAutoSave() {
    setInterval(() => {
        const formData = getFormData();
        if (formData.title || formData.content) {
            try {
                sessionStorage.setItem('blogAutoSave', JSON.stringify({
                    ...formData,
                    savedAt: new Date().toISOString()
                }));
                console.log('Auto-saved at', new Date().toLocaleTimeString());
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        }
    }, 120000); // 2 minutes
}

// Load draft if exists
function loadDraftIfExists() {
    try {
        const autoSave = sessionStorage.getItem('blogAutoSave');
        if (autoSave && !isDraftLoaded) {
            const data = JSON.parse(autoSave);
            const savedTime = new Date(data.savedAt);
            const now = new Date();
            const diffMinutes = (now - savedTime) / 60000;
            
            if (diffMinutes < 60) { // Only load if saved within last hour
                if (confirm(`Found an auto-saved draft from ${savedTime.toLocaleTimeString()}. Would you like to restore it?`)) {
                    loadFormData(data);
                    showNotification('Draft restored successfully!', 'success');
                    isDraftLoaded = true;
                } else {
                    sessionStorage.removeItem('blogAutoSave');
                }
            }
        }
    } catch (error) {
        console.error('Error loading draft:', error);
    }
}

// Load form data
function loadFormData(data) {
    if (data.title) {
        document.getElementById('blogTitle').value = data.title;
        document.getElementById('titleCount').textContent = data.title.length;
    }
    if (data.excerpt) {
        document.getElementById('blogExcerpt').value = data.excerpt;
        document.getElementById('excerptCount').textContent = data.excerpt.length;
    }
    if (data.content) {
        document.getElementById('blogContent').value = data.content;
        document.getElementById('contentCount').textContent = data.content.length;
    }
    if (data.category) {
        selectedCategory = data.category;
        document.getElementById('selectedCategory').value = data.category;
        document.querySelectorAll('.category-option').forEach(opt => {
            if (opt.getAttribute('data-category') === data.category) {
                opt.classList.add('selected');
            }
        });
    }
    if (data.tags && data.tags.length > 0) {
        selectedTags = [...data.tags];
        selectedTags.forEach(tag => {
            document.querySelectorAll('.tag-item').forEach(tagItem => {
                if (tagItem.getAttribute('data-tag') === tag) {
                    tagItem.classList.add('selected');
                }
            });
        });
        updateSelectedTags();
    }
}

// Get form data
function getFormData() {
    return {
        title: document.getElementById('blogTitle').value.trim(),
        excerpt: document.getElementById('blogExcerpt').value.trim(),
        category: selectedCategory,
        tags: [...selectedTags],
        content: document.getElementById('blogContent').value.trim()
    };
}

// Form submission - HARD-CODED to add blog to main page
document.getElementById('blogForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }

    const formData = getFormData();
    
    // Show confirmation
    if (!confirm('Are you sure you want to publish this blog? It will be visible to all users.')) {
        return;
    }

    try {
        // Create the blog data object
        const today = new Date();
        const dateNum = parseInt(today.toISOString().slice(0, 10).replace(/-/g, ''));
        const dateString = today.toLocaleDateString('en-US', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        });
        
        const blogData = {
            id: Date.now(), // Use timestamp as unique ID
            title: formData.title,
            excerpt: formData.excerpt,
            category: formData.category,
            date: dateString,
            dateNum: dateNum,
            image: `${formData.category} Blog`, // Simple image placeholder
            popularity: 0,
            tags: formData.tags,
            content: formData.content,
            author: 'ReLeaf Member',
            publishedAt: new Date().toISOString()
        };

        // Store in sessionStorage to pass to main blog page
        sessionStorage.setItem('newBlogPost', JSON.stringify(blogData));

        // Clear auto-save
        sessionStorage.removeItem('blogAutoSave');
        
        showNotification('Publishing your blog...', 'success');
        
        // Redirect to main blog page after 1.5 seconds
        setTimeout(() => {
            window.location.href = 'mainBlog.php';
        }, 1500);

    } catch (error) {
        console.error('Error publishing blog:', error);
        showNotification('Failed to publish blog. Please try again.', 'error');
    }
});

// Allow Enter key to add custom tag
document.getElementById('customTag').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addCustomTag();
    }
});

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotif = document.querySelector('.notification');
    if (existingNotif) {
        existingNotif.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-width: 400px;
    `;

    // Set colors based on type
    const colors = {
        success: { bg: '#10b981', color: '#ffffff' },
        error: { bg: '#ef4444', color: '#ffffff' },
        warning: { bg: '#f59e0b', color: '#ffffff' },
        info: { bg: '#3b82f6', color: '#ffffff' }
    };

    notification.style.backgroundColor = colors[type].bg;
    notification.style.color = colors[type].color;

    document.body.appendChild(notification);

    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Warn before leaving if there's unsaved content
window.addEventListener('beforeunload', function(e) {
    const formData = getFormData();
    if ((formData.title || formData.content) && !isDraftLoaded) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});