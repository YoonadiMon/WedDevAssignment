
        // Priority indicator text
        const priorityTexts = {
            'low': '🟢 Low - General questions and minor issues',
            'medium': '🟡 Medium - Feature requests and moderate issues', 
            'high': '🟠 High - Major functionality issues',
            'urgent': '🔴 Urgent - Critical system failures'
        };

        document.addEventListener('DOMContentLoaded', function() {
            const prioritySelect = document.getElementById('ticketPriority');
            const priorityIndicator = document.getElementById('priorityIndicator');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('fileInput');
            const attachmentsPreview = document.getElementById('attachmentsPreview');
            const form = document.getElementById('supportTicketForm');
            const submitBtn = document.getElementById('submitBtn');

            let attachments = [];

            // Priority indicator
            prioritySelect.addEventListener('change', function() {
                const priority = this.value;
                if (priority && priorityTexts[priority]) {
                    priorityIndicator.innerHTML = `<span class="priority-${priority}">${priorityTexts[priority]}</span>`;
                } else {
                    priorityIndicator.innerHTML = '';
                }
            });

            // File upload handling
            fileUploadArea.addEventListener('click', function() {
                fileInput.click();
            });

            fileUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileUploadArea.style.borderColor = 'var(--MainGreen)';
                fileUploadArea.style.backgroundColor = 'var(--sec-bg-color)';
            });

            fileUploadArea.addEventListener('dragleave', function() {
                fileUploadArea.style.borderColor = 'var(--border-color)';
                fileUploadArea.style.backgroundColor = 'transparent';
            });

            fileUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                fileUploadArea.style.borderColor = 'var(--border-color)';
                fileUploadArea.style.backgroundColor = 'transparent';
                
                if (e.dataTransfer.files.length > 0) {
                    handleFiles(e.dataTransfer.files);
                }
            });

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    handleFiles(this.files);
                }
            });

            function handleFiles(files) {
                for (let file of files) {
                    // Check file size (10MB limit)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size too large. Maximum size is 10MB.');
                        continue;
                    }

                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('File type not supported. Please upload PDF, JPG, PNG, or DOC files.');
                        continue;
                    }

                    attachments.push(file);
                    updateAttachmentsPreview();
                }
                
                // Reset file input
                fileInput.value = '';
            }

            function updateAttachmentsPreview() {
                attachmentsPreview.innerHTML = '';
                
                attachments.forEach((file, index) => {
                    const attachmentItem = document.createElement('div');
                    attachmentItem.className = 'attachment-item';
                    
                    attachmentItem.innerHTML = `
                        <span class="attachment-name">${file.name}</span>
                        <button type="button" class="remove-attachment" data-index="${index}">×</button>
                    `;
                    
                    attachmentsPreview.appendChild(attachmentItem);
                });

                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-attachment').forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        attachments.splice(index, 1);
                        updateAttachmentsPreview();
                    });
                });
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                
                // Simulate form submission
                setTimeout(function() {
                    alert('Your support ticket has been submitted successfully! Our team will get back to you soon.');
                    window.location.href = '../../pages/MemberPages/mContactSupport.html';
                }, 1500);
            });
        });