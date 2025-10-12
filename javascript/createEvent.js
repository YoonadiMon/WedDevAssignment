document.getElementById('eventDate').addEventListener('click', function() {
    this.showPicker();
});
document.getElementById('eventTime').addEventListener('click', function() {
    this.showPicker();
});

 // Custom tags functionality
document.addEventListener('DOMContentLoaded', function() {
    const customTagInput = document.getElementById('customTagInput');
    const addTagBtn = document.getElementById('addTagBtn');
    const themeTags = document.getElementById('themeTags');
    const tagLimitWarning = document.getElementById('tagLimitWarning');
    const themeCheckboxes = document.querySelectorAll('input[name="theme"]');
    
    const maxTotalTags = 3;
    let customTags = [];

    // Add tag button click
    addTagBtn.addEventListener('click', addCustomTag);

    // Enter key to add tag
    customTagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addCustomTag();
        }
    });

    // Check tag limit on checkbox change
    themeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', checkTagLimit);
    });

    function addCustomTag() {
        const tagText = customTagInput.value.trim();
        
        if (!tagText) {
            return;
        }

        if (tagText.length > 20) {
            alert('Tag must be 20 characters or less');
            return;
        }

        if (getTotalSelectedTags() >= maxTotalTags) {
            showTagLimitWarning();
            return;
        }

        // Check if tag already exists (case insensitive)
        const allTags = Array.from(themeCheckboxes).map(cb => cb.value.toLowerCase());
        if (allTags.includes(tagText.toLowerCase()) || customTags.includes(tagText.toLowerCase())) {
            alert('This tag already exists');
            return;
        }

        // Add custom tag to the checkbox group
        addCustomTagToGroup(tagText);
        customTags.push(tagText.toLowerCase());
        customTagInput.value = '';
        checkTagLimit();
        updateAddButtonState();
    }

    function addCustomTagToGroup(tagText) {
        const label = document.createElement('label');
        label.className = 'custom-tag';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'theme';
        checkbox.value = tagText.toLowerCase();
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-custom-tag';
        removeBtn.innerHTML = '×';
        removeBtn.title = 'Remove this tag';
        removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            removeCustomTag(tagText, label);
        });
        
        label.appendChild(checkbox);
        label.appendChild(document.createTextNode(' ' + tagText));
        label.appendChild(removeBtn);
        
        // Add change event listener for the new checkbox
        checkbox.addEventListener('change', checkTagLimit);
        
        themeTags.appendChild(label);
    }

    function removeCustomTag(tagText, labelElement) {
        // Remove from custom tags array
        customTags = customTags.filter(tag => tag !== tagText.toLowerCase());
        
        // Remove from DOM
        if (labelElement && labelElement.parentNode) {
            labelElement.parentNode.removeChild(labelElement);
        }
        
        checkTagLimit();
        updateAddButtonState();
    }

    function getTotalSelectedTags() {
        const allCheckboxes = document.querySelectorAll('input[name="theme"]');
        const selectedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
        return selectedCount;
    }

    function checkTagLimit() {
        const totalSelected = getTotalSelectedTags();
        
        if (totalSelected >= maxTotalTags) {
            showTagLimitWarning();
            // Disable unchecked checkboxes
            const allCheckboxes = document.querySelectorAll('input[name="theme"]');
            allCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.disabled = true;
                }
            });
        } else {
            hideTagLimitWarning();
            // Enable all checkboxes
            const allCheckboxes = document.querySelectorAll('input[name="theme"]');
            allCheckboxes.forEach(checkbox => {
                checkbox.disabled = false;
            });
        }
        
        updateAddButtonState();
    }

    function showTagLimitWarning() {
        tagLimitWarning.classList.add('show');
    }

    function hideTagLimitWarning() {
        tagLimitWarning.classList.remove('show');
    }

    function updateAddButtonState() {
        const totalSelected = getTotalSelectedTags();
        const hasText = customTagInput.value.trim().length > 0;
        addTagBtn.disabled = totalSelected >= maxTotalTags || !hasText;
    }

    // Update add button state on input
    customTagInput.addEventListener('input', updateAddButtonState);

    // Make removeCustomTag function available globally
    window.removeCustomTag = removeCustomTag;

    // File upload and form submission functions
    setupFileUpload();
    setupFormSubmit();
    
    // Set minimum date to today
    const dateInput = document.getElementById('eventDate');
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
});

// File upload handling
let uploadedFiles = [];
const maxFileSize = 5 * 1024 * 1024; // 5MB

function setupFileUpload() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    
    if (!fileUploadArea || !fileInput) return;
    
    // Click to upload
    fileUploadArea.addEventListener('click', () => {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    // Drag and drop
    fileUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });
    
    fileUploadArea.addEventListener('dragleave', () => {
        fileUploadArea.classList.remove('dragover');
    });
    
    fileUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
}

function handleFiles(files) {
    const fileArray = Array.from(files);
    
    fileArray.forEach(file => {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image file.`);
            return;
        }
        
        // Validate file size
        if (file.size > maxFileSize) {
            alert(`${file.name} exceeds the 5MB size limit.`);
            return;
        }
        
        // Add to uploaded files
        uploadedFiles.push(file);
    });
    
    displayUploadedFiles();
}

function displayUploadedFiles() {
    const container = document.getElementById('uploadedFiles');
    if (!container) return;
    
    container.innerHTML = '';
    
    uploadedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        const fileSize = formatFileSize(file.size);
        
        fileItem.innerHTML = `
            <div class="file-item-info">
                <span class="file-item-name">📷 ${file.name}</span>
                <span class="file-item-size">(${fileSize})</span>
            </div>
            <button type="button" class="file-item-remove" onclick="removeFile(${index})">✕</button>
        `;
        
        container.appendChild(fileItem);
    });
}

function removeFile(index) {
    uploadedFiles.splice(index, 1);
    displayUploadedFiles();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function setupFormSubmit() {
    const form = document.getElementById('createEventForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const maxPaxInput = document.getElementById('maxPax');
        const maxPaxValue = maxPaxInput.value.trim();

        // Validate maxPax is a positive integer
        if (!maxPaxValue || !/^\d+$/.test(maxPaxValue)) {
            alert('Please enter a valid number for maximum participants (positive whole numbers only).');
            maxPaxInput.focus();
            return;
        }

        const maxPax = parseInt(maxPaxValue, 10);
        
        // Validate it's at least 1
        if (maxPax < 1) {
            alert('Maximum participants must be at least 1.');
            maxPaxInput.focus();
            return;
        }
        
        // Get all selected tags (predefined + custom)
        const predefinedTags = Array.from(document.querySelectorAll('input[name="theme"]:checked')).map(cb => cb.value);
        const allTags = [...predefinedTags, ...customTags];
        
        // Validate tags
        if (allTags.length === 0) {
            alert('Please select at least one theme tag or add a custom tag.');
            return;
        }
        
        if (allTags.length > 3) {
            alert('Please select no more than 3 tags total.');
            return;
        }
        
        // Validate event type
        const selectedType = document.querySelector('input[name="type"]:checked');
        if (!selectedType) {
            alert('Please select an event type.');
            return;
        }
        
        // Collect form data
        const formData = {
            eventName: document.getElementById('eventName').value,
            eventURL: document.getElementById('eventURL').value,
            description: document.getElementById('eventDescription').value,
            location: document.getElementById('eventLocation').value,
            date: document.getElementById('eventDate').value,
            time: document.getElementById('eventTime').value,
            contactEmail: document.getElementById('contactEmail').value,
            host: document.getElementById('hostOrg').value,
            themes: allTags,
            eventType: selectedType.value,
            timezone: document.getElementById('timezone').value,
            files: uploadedFiles.map(f => f.name)
        };
        
        // Log form data
        console.log('Event Data:', formData);
        console.log('Uploaded Files:', uploadedFiles);
        
        // Show success message
        showSuccessMessage('Event created successfully! Redirecting to event page...');
        
        // Simulate redirect after 2 seconds
        setTimeout(() => {
            window.location.href = 'mainEvent.html';
        }, 2000);
    });
}

function showSuccessMessage(message) {
    const successMsg = document.getElementById('successMessage');
    if (!successMsg) return;
    
    successMsg.textContent = message;
    successMsg.classList.add('show');
    
    // Scroll to top to show message
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Make removeFile available globally
window.removeFile = removeFile;