<?php
include("../../php/dbConn.php");

// Initialize variables
$success_message = "";
$error_message = "";
$currentUserID = 4; // Assuming current user ID is 6

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $subject = mysqli_real_escape_string($connection, $_POST['subject']);
    $category = mysqli_real_escape_string($connection, $_POST['category']);
    $priority = mysqli_real_escape_string($connection, $_POST['priority']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    
    // Get username (you might want to get this from session or database)
    $username = "User" . $currentUserID; // Default username
    
    // Set default values
    $status = "Open";
    $isUnread = 1;
    $current_time = date("Y-m-d H:i:s");
    
    // Insert ticket into database
    $query = "INSERT INTO tbltickets (subject, category, priority, status, description, userID, username, userEmail, isUnread, createdAt, updatedAt) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssisssss", 
            $subject, $category, $priority, $status, $description, 
            $currentUserID, $username, $email, $isUnread, $current_time, $current_time);
        
        if (mysqli_stmt_execute($stmt)) {
            $ticketId = mysqli_insert_id($connection); // Get the newly created ticket ID
            $success_message = "Ticket submitted successfully! We'll get back to you soon.";
            
            // Handle file uploads if any files were uploaded
            if (!empty($_FILES['attachments']['name'][0])) {
                $uploadSuccess = handleFileUploads($connection, $ticketId, $currentUserID);
                
                if (!$uploadSuccess) {
                    $success_message .= " Note: Some attachments failed to upload.";
                }
            }
            
            // Clear form fields
            $_POST = array();
        } else {
            $error_message = "Error submitting ticket: " . mysqli_error($connection);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Database error: " . mysqli_error($connection);
    }
}

// Function to handle file uploads to database
function handleFileUploads($connection, $ticketId, $uploadedBy) {
    $attachments = $_FILES['attachments'];
    $successCount = 0;
    
    // Loop through each uploaded file
    for ($i = 0; $i < count($attachments['name']); $i++) {
        if ($attachments['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = mysqli_real_escape_string($connection, $attachments['name'][$i]);
            $fileSize = $attachments['size'][$i];
            $fileType = mysqli_real_escape_string($connection, $attachments['type'][$i]);
            
            // Read file content
            $fileContent = file_get_contents($attachments['tmp_name'][$i]);
            
            // Check file size (10MB limit)
            if ($fileSize > 10 * 1024 * 1024) {
                continue; // Skip files that are too large
            }
            
            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                           'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($fileType, $allowedTypes)) {
                continue; // Skip unsupported file types
            }
            
            // Insert file into database
            $query = "INSERT INTO tblticket_attachments (ticketId, fileName, fileData, fileSize, fileType, uploadedBy) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($connection, $query);
            
            if ($stmt) {
                $null = null; // Needed for binding BLOB parameter
                mysqli_stmt_bind_param($stmt, "isbisi", $ticketId, $fileName, $null, $fileSize, $fileType, $uploadedBy);
                mysqli_stmt_send_long_data($stmt, 2, $fileContent); // Bind BLOB data
                
                if (mysqli_stmt_execute($stmt)) {
                    $successCount++;
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    return $successCount > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <link rel="stylesheet" href="../../style/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        .support-ticket-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: var(--MainGreen);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-button:hover {
            color: var(--btn-color-hover);
        }

        .ticket-form-section {
            background-color: var(--bg-color);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .ticket-form-section h2 {
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: 600;
            color: var(--text-heading);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-heading);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--MainGreen);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .priority-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 5px;
            font-size: 12px;
        }

        .priority-low { color: var(--MainGreen); }
        .priority-medium { color: #f59e0b; }
        .priority-high { color: #ef4444; }

        .file-upload {
            border: 2px dashed var(--border-color);
            border-radius: 6px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .file-upload:hover {
            border-color: var(--MainGreen);
        }

        .file-upload input {
            display: none;
        }

        .upload-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--Gray);
        }

        .file-upload-text {
            color: var(--Gray);
            margin-bottom: 10px;
        }

        .file-upload-hint {
            font-size: 12px;
            color: var(--Gray);
        }

        .attachments-preview {
            margin-top: 15px;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background-color: var(--sec-bg-color);
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .attachment-name {
            flex: 1;
            font-size: 14px;
        }

        .remove-attachment {
            background: none;
            border: none;
            color: var(--Red);
            cursor: pointer;
            padding: 5px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .cancel-btn {
            background-color: transparent;
            color: var(--Gray);
            border: 1px solid var(--border-color);
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .cancel-btn:hover {
            background-color: var(--sec-bg-color);
        }

        .submit-btn {
            background-color: var(--MainGreen);
            color: var(--White);
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: var(--btn-color-hover);
        }

        .submit-btn:disabled {
            background-color: var(--Gray);
            cursor: not-allowed;
        }

        .info-section {
            background-color: var(--sec-bg-color);
            border-radius: 8px;
            padding: 25px;
            margin-top: 30px;
        }

        .info-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-heading);
        }

        .info-list {
            list-style: none;
            padding: 0;
        }

        .info-list li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }

        .info-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--MainGreen);
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .cancel-btn, .submit-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <!-- Logo + Name & Navbar -->
    <header>
        <!-- Logo + Name -->
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
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.html">About</a>
                </div>
            </div>

        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/MemberPages/memberIndex.html">Home</a>
            <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.html">Event</a>
            <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
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
    
    </header>

    <hr>

    <!-- Main Content -->
    <main>
        <div class="support-ticket-container">
            <!-- Back Button -->
            <a href="mContactSupport.php" class="back-button">
                ← Back to Support
            </a>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Ticket Form Section -->
            <div class="ticket-form-section">
                <h2>Create Support Ticket</h2>
                
                <!-- Add enctype="multipart/form-data" to form for file uploads -->
                <form id="supportTicketForm" method="POST" action="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ticketSubject">Subject *</label>
                            <input type="text" id="ticketSubject" name="subject" required 
                                   placeholder="Brief description of your issue"
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="ticketCategory">Category *</label>
                            <select id="ticketCategory" name="category" required>
                                <option value="">Select a category</option>
                                <option value="technical" <?php echo (isset($_POST['category']) && $_POST['category'] == 'technical') ? 'selected' : ''; ?>>Technical Issue</option>
                                <option value="account" <?php echo (isset($_POST['category']) && $_POST['category'] == 'account') ? 'selected' : ''; ?>>Account Problem</option>
                                <option value="billing" <?php echo (isset($_POST['category']) && $_POST['category'] == 'billing') ? 'selected' : ''; ?>>Billing & Payment</option>
                                <option value="feature" <?php echo (isset($_POST['category']) && $_POST['category'] == 'feature') ? 'selected' : ''; ?>>Feature Request</option>
                                <option value="bug" <?php echo (isset($_POST['category']) && $_POST['category'] == 'bug') ? 'selected' : ''; ?>>Bug Report</option>
                                <option value="general" <?php echo (isset($_POST['category']) && $_POST['category'] == 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="other" <?php echo (isset($_POST['category']) && $_POST['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ticketPriority">Priority *</label>
                            <select id="ticketPriority" name="priority" required>
                                <option value="">Select priority</option>
                                <option value="Low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                                <option value="Medium" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="High" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                                <option value="Urgent" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Urgent') ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                            <div class="priority-indicator" id="priorityIndicator">
                                <!-- Priority indicator will be shown here -->
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ticketEmail">Contact Email *</label>
                            <input type="email" id="ticketEmail" name="email" required 
                                   placeholder="your@email.com"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ticketDescription">Description *</label>
                        <textarea id="ticketDescription" name="description" required 
                                  placeholder="Please provide detailed information about your issue..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Attachments (Optional)</label>
                        <div class="file-upload" id="fileUploadArea">
                            <div class="upload-icon">📎</div>
                            <div class="file-upload-text">Click to upload files or drag and drop</div>
                            <div class="file-upload-hint">Maximum file size: 10MB. Supported formats: PDF, JPG, PNG, DOC</div>
                            <!-- Change to multiple file input with name as array -->
                            <input type="file" id="fileInput" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                        <div class="attachments-preview" id="attachmentsPreview">
                            <!-- Attachments will be listed here -->
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="window.location.href='mContactSupport.php'">Cancel</button>
                        <button type="submit" class="submit-btn" id="submitBtn">Submit Ticket</button>
                    </div>
                </form>
            </div>

            <!-- Information Section -->
            <div class="info-section">
                <h3>Before Submitting Your Ticket</h3>
                <ul class="info-list">
                    <li>Please check our <a href="../../pages/CommonPages/mainFAQ.html" style="color: var(--MainGreen);">FAQs</a> to see if your question has already been answered</li>
                    <li>Provide as much detail as possible to help us resolve your issue quickly</li>
                    <li>Include any error messages you're receiving</li>
                    <li>For technical issues, mention your device and browser information</li>
                    <li>We typically respond within 24-48 hours</li>
                </ul>
            </div>
        </div>

        <!-- Search & Results -->
        <section class="search-container" id="searchContainer" style="display: none;">
            <!-- Tabs -->
            <div class="tabs" id="tabs">
                <div class="tab active" data-type="all">All</div>
                <div class="tab" data-type="profiles">Profiles</div>
                <div class="tab" data-type="blogs">Blogs</div>
                <div class="tab" data-type="events">Events</div>
                <div class="tab" data-type="trades">Trades</div>
            </div>

            <!-- Results -->
            <div class="results" id="results"></div>
        </section>
    </main>

    <hr>

    <!-- Footer -->
    <footer>
        <!-- Column 1 -->
        <section class="c-footer-info-section">
            <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
            <div class="c-text">ReLeaf</div>
            <div class="c-text c-text-center">
                "Relief for the Planet, One Leaf at a Time."
                <br>
                "Together, We Can ReLeaf the Earth."
            </div>
            <div class="c-text c-text-label">
                +60 12 345 6789
            </div>
            <div class="c-text">
                abc@gmail.com
            </div>
        </section>
        
        <!-- Column 2 -->
        <section class="c-footer-links-section">
            <div>
                <b>My Account</b><br>
                <a href="../../pages/MemberPages/mProfile.html">My Account</a><br>
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.html">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Community</b><br>
                <a href="../../pages/CommonPages/mainEvent.html">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.html">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>
        const isAdmin = false;
        
        // Priority indicator functionality
        document.getElementById('ticketPriority').addEventListener('change', function() {
            const priority = this.value;
            const indicator = document.getElementById('priorityIndicator');
            
            if (priority) {
                let text = '';
                let className = '';
                
                switch(priority) {
                    case 'Low':
                        text = '🟢 Low priority - We\'ll respond within 3-5 business days';
                        className = 'priority-low';
                        break;
                    case 'Medium':
                        text = '🟡 Medium priority - We\'ll respond within 1-2 business days';
                        className = 'priority-medium';
                        break;
                    case 'High':
                        text = '🔴 High priority - We\'ll respond within 24 hours';
                        className = 'priority-high';
                        break;
                    case 'Urgent':
                        text = '🚨 Urgent priority - We\'ll respond as soon as possible';
                        className = 'priority-high';
                        break;
                }
                
                indicator.innerHTML = `<span class="${className}">${text}</span>`;
            } else {
                indicator.innerHTML = '';
            }
        });

        // File upload functionality
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('fileInput');
        const attachmentsPreview = document.getElementById('attachmentsPreview');

        fileUploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.style.borderColor = 'var(--MainGreen)';
            fileUploadArea.style.backgroundColor = 'var(--sec-bg-color)';
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.style.borderColor = 'var(--border-color)';
            fileUploadArea.style.backgroundColor = '';
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.style.borderColor = 'var(--border-color)';
            fileUploadArea.style.backgroundColor = '';
            
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            for (let file of files) {
                // Check file size (10MB limit)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size too large. Maximum size is 10MB.');
                    continue;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword'];
                if (!allowedTypes.includes(file.type)) {
                    alert('File type not supported. Please upload PDF, JPG, PNG, or DOC files.');
                    continue;
                }
                
                addAttachmentPreview(file);
            }
        }

        function addAttachmentPreview(file) {
            const attachmentItem = document.createElement('div');
            attachmentItem.className = 'attachment-item';
            
            attachmentItem.innerHTML = `
                <span class="attachment-name">${file.name}</span>
                <button type="button" class="remove-attachment" onclick="this.parentElement.remove()">×</button>
            `;
            
            attachmentsPreview.appendChild(attachmentItem);
        }

        // Form validation
        document.getElementById('supportTicketForm').addEventListener('submit', function(e) {
            const subject = document.getElementById('ticketSubject').value.trim();
            const description = document.getElementById('ticketDescription').value.trim();
            
            if (!subject || !description) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            // Disable submit button to prevent double submission
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Submitting...';
        });
    </script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>
<?php
// Close database connection
if (isset($connection)) {
    mysqli_close($connection);
}
?>