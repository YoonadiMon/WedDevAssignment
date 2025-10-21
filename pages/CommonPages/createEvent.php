<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Full list of countries
$countries = [
    'Malaysia',
    'Singapore',
    'China',
    'Hong Kong',
    'Taiwan',
    'Philippines',
    'Australia',
    'Japan',
    'South Korea',
    'Indonesia',
    'Thailand',
    'Vietnam',
    'India',
    'Sri Lanka',
    'Pakistan',
    'Bangladesh',
    'United States',
    'Canada',
    'United Kingdom',
    'Germany',
    'France',
    'Italy',
    'Spain',
    'Netherlands',
    'Brazil',
    'Mexico',
    'Russia',
    'South Africa',
    'Egypt',
    'Saudi Arabia',
    'United Arab Emirates',
    'New Zealand'
];

// Function to get timezone by country
function getTimezoneByCountry($country) {
    $countryTimezones = [
        'Malaysia' => 'UTC+08:00',
        'Singapore' => 'UTC+08:00',
        'China' => 'UTC+08:00',
        'Hong Kong' => 'UTC+08:00',
        'Taiwan' => 'UTC+08:00',
        'Philippines' => 'UTC+08:00',
        'Australia' => 'UTC+10:00',
        'Japan' => 'UTC+09:00',
        'South Korea' => 'UTC+09:00',
        'Indonesia' => 'UTC+07:00',
        'Thailand' => 'UTC+07:00',
        'Vietnam' => 'UTC+07:00',
        'India' => 'UTC+05:30',
        'Sri Lanka' => 'UTC+05:30',
        'Pakistan' => 'UTC+05:00',
        'Bangladesh' => 'UTC+06:00',
        'United States' => 'UTC-05:00', 
        'Canada' => 'UTC-05:00', 
        'United Kingdom' => 'UTC+00:00',
        'Germany' => 'UTC+01:00',
        'France' => 'UTC+01:00',
        'Italy' => 'UTC+01:00',
        'Spain' => 'UTC+01:00',
        'Netherlands' => 'UTC+01:00',
        'Brazil' => 'UTC-03:00', 
        'Mexico' => 'UTC-06:00',
        'Russia' => 'UTC+03:00', 
        'South Africa' => 'UTC+02:00',
        'Egypt' => 'UTC+02:00',
        'Saudi Arabia' => 'UTC+03:00',
        'United Arab Emirates' => 'UTC+04:00',
        'New Zealand' => 'UTC+12:00',
    ];
    
    return $countryTimezones[$country] ?? 'UTC+08:00'; // Default to Malaysia time
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    // Get form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $time = $_POST['time'];
    $day = $_POST['day'];
    $duration = $_POST['duration'];
    $location = $_POST['location'];
    $country = $_POST['country'];
    $maxPax = $_POST['maxPax'];
    $mode = $_POST['mode']; // online, physical, hybrid
    $type = $_POST['type']; // talk, workshop, etc.
    $status = 'open';
    
    $timeZone = getTimezoneByCountry($country); 

    // Validation
    // title
    if (empty($title) || strlen($title) < 5) {
        $errors['title'] = "Title must be at least 5 characters long";
    } elseif (strlen($title) > 60) {
        $errors['title'] = "Title cannot exceed 255 characters";
    }

    // description
    if (empty($description) || strlen($description) < 20) {
        $errors['description'] = "Description must be at least 20 characters long";
    } elseif (strlen($description) > 2000) {
        $errors['description'] = "Description cannot exceed 2000 characters";
    }

    // date
    $today = date('Y-m-d');
    if (empty($startDate) || $startDate < $today) {
        $errors['startDate'] = "Start date cannot be in the past";
    }
    
    if (empty($endDate) || $endDate < $startDate) {
        $errors['endDate'] = "End date cannot be before start date";
    }

    // time 
    if (empty($time)) {
        $errors['time'] = "Time is required";
    }
    
    // duration 
    if (empty($duration) || $duration < 1 || $duration > 24) {
        $errors['duration'] = "Duration must be between 1 and 24 hours";
    }
    
    // day 
    if (empty($day) || $day < 1 || $day > 60) {
        $errors['day'] = "Number of days must be between 1 and 60";
    }

    // location
    if (empty($location) || strlen($location) < 2) {
        $errors['location'] = "Location must be at least 2 characters long";
    }
    
    // country
    if (empty($country) || strlen($country) < 2) {
        $errors['country'] = "Please enter a valid country name";
    }
    
    // max participants
    if (empty($maxPax) || $maxPax < 20) {
        $errors['maxPax'] = "Maximum participants must be at least 20";
    } elseif ($maxPax > 10000) {
        $errors['maxPax'] = "Maximum participants cannot exceed 10,000";
    }

    // event mode & type
    if (empty($mode)) {
        $errors['mode'] = "Please select a event mode";
    }

    if (empty($type)) {
        $errors['type'] = "Please select a valid event type";
    }

    // Handle file upload 
    $bannerFilePath = null;
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
        
        if ($_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/eventBanner/'; 
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors['banner'] = "Failed to create upload directory";
                }
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            $fileType = $_FILES['banner']['type'];
            $fileName = $_FILES['banner']['name'];
            $fileSize = $_FILES['banner']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors['banner'] = "Only JPG, JPEG, PNG, and GIF files are allowed";
            } elseif (!in_array($fileType, $allowedTypes)) {
                $errors['banner'] = "Invalid file type. Please upload a valid image file";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $errors['banner'] = "File size must be less than 5MB";
            } elseif (!getimagesize($_FILES['banner']['tmp_name'])) {
                $errors['banner'] = "File is not a valid image";
            } else {
                $uniqueFileName = 'event_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $uniqueFileName;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $targetPath)) {
                    $bannerFilePath = $targetPath;
                    
                } else {
                    $errors['banner'] = "Failed to upload file. Please try again.";
                    
                    // Debug upload errors
                    $uploadErrors = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                    ];
                    
                    error_log("File upload error: " . ($uploadErrors[$_FILES['banner']['error']] ?? 'Unknown error'));
                }
            }
        } else {
            // Handle specific upload errors
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File size too large. Maximum size is 5MB.',
                UPLOAD_ERR_FORM_SIZE => 'File size too large.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to save file.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
            ];
            
            $errors['banner'] = $uploadErrors[$_FILES['banner']['error']] ?? 'File upload error occurred';
        }
    }
    
    // Insert into database 
    if (empty($errors)) {
        // Use NULL if no banner file was uploaded
        if ($bannerFilePath === null) {
            $query = "INSERT INTO tblevents (userID, title, duration, day, startDate, endDate, time, timeZone, location, country, description, mode, type, status, maxPax, datePosted) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $connection->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("isiissssssssssi", 
                    $userID, 
                    $title, 
                    $duration, 
                    $day, 
                    $startDate, 
                    $endDate, 
                    $time, 
                    $timeZone,
                    $location, 
                    $country, 
                    $description, 
                    $mode, 
                    $type, 
                    $status, 
                    $maxPax
                );
            }
        } else {
            $query = "INSERT INTO tblevents (userID, title, duration, day, startDate, endDate, time, timeZone, location, country, description, mode, type, status, maxPax, datePosted, bannerFilePath) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $connection->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("isiissssssssssis", 
                    $userID, 
                    $title, 
                    $duration, 
                    $day, 
                    $startDate, 
                    $endDate, 
                    $time, 
                    $timeZone,
                    $location, 
                    $country, 
                    $description, 
                    $mode, 
                    $type, 
                    $status, 
                    $maxPax,
                    $bannerFilePath
                );
            }
        }
        
        if ($stmt && $stmt->execute()) {
            $eventID = $stmt->insert_id;
            $_SESSION['success_message'] = "Event created successfully!";
            header("Location: mainEvent.php");
            exit();
        } else {
            $error_message = "Error creating event: " . ($stmt ? $stmt->error : $connection->error);
            
            // Clean up uploaded file if database insert failed
            if ($bannerFilePath && file_exists($bannerFilePath)) {
                unlink($bannerFilePath);
            }
        }
        
        if ($stmt) {
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - Host An Event</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
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
            color: var(--DarkerGray);
            text-decoration: underline;
        }

        .create-event-wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2.5rem;
        }

        .form-container {
            margin-bottom: 5rem;
            background: transparent;
            border: none;
        }

        .form-header {
            position: relative; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .form-header img {
            position: relative;
            width: 100%;
            max-width: 60px;
            height: auto;
            margin-bottom: 1rem;
            z-index: 1; 
        }

        .form-header::before {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            background: var(--sec-bg-color); 
            border-radius: 50%;
            filter: blur(50px);
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1; 
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            line-height: 1.4;
            margin-bottom: 1rem;
        }

        .form-header p {
            font-size: 1rem;
            color: var(--DarkerGray);
        }

        .dark-mode .form-header p {
            color: var(--Gray);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-container label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-container label .required {
            color: var(--Red);
        }

        .form-container label small {
            font-weight: 400;
            color: var(--Gray);
            font-size: 0.85rem;
        }

        .form-container textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container input[type="date"],
        .form-container input[type="time"],
        .form-container select,
        .form-container textarea {
            height: 3rem;
        }

        .radio-group {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            margin: 0;
            cursor: pointer;
            font-weight: 400;
        }

        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: var(--MainGreen);
        }

        .file-upload-area {
            margin-top: 0.5rem;
            border: 2px dashed var(--Gray);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-color);
            position: relative;
        }

        .file-upload-area:hover {
            border-color: var(--MainGreen);
            background: var(--LowGreen);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-text {
            color: var(--text-color);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .file-upload-hint {
            color: var(--DarkerGray);
            font-size: 0.8rem;
        }

        .dark-mode .file-upload-hint {
            color: var(--Gray);
        }

        .preview-container {
            display: none;
            text-align: center;
        }

        .preview-image {
            max-width: 100%;
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: var(--bg-color);
            border-radius: 8px;
            border: 2px solid var(--MainGreen);
            padding: 0.5rem;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .replace-btn {
            background: var(--Red);
            color: var(--White);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .replace-btn:hover {
            background: var(--DarkerGray);
            transform: translateY(-2px);
        }

        .save-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--LowGreen), var(--MainGreen), var(--LowGreen));
            color: var(--White);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px var(--Gray);
        }

        .error-message,
        .success-message {
            padding: 1rem;
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .error-message {
            color: var(--Red);
        }

        .success-message {
            color: var(--MainGreen);
        }

        .file-status {
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .file-status-hide {
            display: none;
        }

        .file-status.success {
            color: var(--White);
            background: var(--LowGreen);
            border: none;
        }

        .file-status.error {
            color: var(--text-color);
            background: var(--LowRed);
            border: none;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .radio-group {
                flex-direction: column;
                gap: 0.75rem;
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
            <a href="../../pages/<?php echo $isAdmin ? 'adminPages/adminIndex.php' : 'MemberPages/memberIndex.php'; ?>" class="c-logo-link">
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
                            <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                        </button>

                        <?php if ($isAdmin): ?>
                            <!-- Admin Navigation Icons -->
                            <a href="../../pages/adminPages/aProfile.html">
                                <img src="../../assets/images/profile-light.svg" alt="Profile">
                            </a>
                        <?php else: ?>
                            <!-- Member Navigation Icons -->
                            <div class="c-chatbox" id="chatboxMobile">
                                <a href="../../pages/MemberPages/mChat.html">
                                    <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                                </a>
                                <span class="c-notification-badge" id="chatBadgeMobile"></span>
                            </div>
                            <a href="../../pages/MemberPages/mSetting.html">
                                <img src="../../assets/images/setting-light.svg" alt="Settings">
                            </a>
                        <?php endif; ?>
                    </section>

                    <?php if ($isAdmin): ?>
                        <!-- Admin Menu Items -->
                        <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                        <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                        <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                    <?php else: ?>
                        <!-- Member Menu Items -->
                        <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.html">About</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <?php if ($isAdmin): ?>
                <!-- Admin Desktop Menu -->
                <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.html">Trade</a>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a>
                <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
            <?php else: ?>
                <!-- Member Desktop Menu -->
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/aboutUs.html">About</a>
            <?php endif; ?>
        </nav>

        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>

            <?php if ($isAdmin): ?>
                <!-- Admin Navbar More -->
                <a href="../../pages/adminPages/aProfile.html">
                    <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
                </a>
            <?php else: ?>
                <!-- Member Navbar More -->
                <a href="../../pages/MemberPages/mChat.html" class="c-chatbox" id="chatboxDesktop">
                    <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                    <span class="c-notification-badge" id="chatBadgeDesktop"></span>
                </a>
                <a href="../../pages/MemberPages/mSetting.html">
                    <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
                </a>
            <?php endif; ?>
        </section>
    </header>
    <hr>

    <!-- Main Content -->
    <main>
        <section class="content" id="content">
            <div class="create-event-wrapper">
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <a href="mainEvent.php" class="back-button">← Back to Events</a>
                
                <form class="form-container" method="POST" enctype="multipart/form-data" id="createEventForm">
                    <div class="form-header">
                        <img src="../../assets/images/Logo.png" alt="Logo">
                        <h2>Create Your Event</h2>
                        <p>Start by filling up the form below.</p>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Event Title <span class="required">*</span></label>
                        <input class="c-input" type="text" name="title" placeholder="Enter event title" required />
                    </div>

                    <div class="form-group full-width">
                        <label>Description <span class="required">*</span></label>
                        <textarea class="c-input" name="description" placeholder="Describe your event" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date <span class="required">*</span> <small>(When your event begins)</small></label>
                            <input class="c-input" type="date" name="startDate" id="startDate" required />
                        </div>
                        <div class="form-group">
                            <label>End Date <span class="required">*</span> <small>(When your event ends)</small></label>
                            <input class="c-input" type="date" name="endDate" id="endDate" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Time <span class="required">*</span> <small>(Event start time)</label>
                            <input class="c-input" type="time" name="time" id="time" required />
                        </div>
                        <div class="form-group">
                            <label>Duration <span class="required">*</span> <small>(Total number of hours per day)</label>
                            <input class="c-input" type="number" name="duration" min="1" max="24" placeholder="Must be between 1 and 24 hours" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Number of Days <span class="required">*</span> <small>(Total event duration)</small></label>
                            <input class="c-input" type="number" name="day" min="1" max="60" value="1" required />
                        </div>
                        <div class="form-group">
                            <label>Maximum Participants <span class="required">*</span></label>
                            <input class="c-input" type="number" name="maxPax" min="1" placeholder="Must be at least 20" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Location <span class="required">*</span></label>
                            <input class="c-input" type="text" name="location" placeholder="Event location or 'Online'" required />
                        </div>
                        <div class="form-group">
                            <label>Country <span class="required">*</span></label>
                            <select class="c-input c-input-select" id="registerCountry" name="country" required>
                                <option value="" disabled <?php echo (!isset($_POST['register']) || empty($_POST['country'])) ? 'selected' : ''; ?>>Select your Country</option>
                                <?php 
                                $query = "SELECT country FROM tblusers WHERE userID = ?";
                                $stmt = $connection->prepare($query);
                                $stmt->bind_param("i", $userID);
                                $stmt->execute();
                                $stmt->bind_result($userCountry);
                                $stmt->fetch();
                                $stmt->close();

                                foreach ($countries as $country) {
                                    $selected = ($userCountry == $country) ? 'selected' : '';
                                    echo "<option value=\"$country\" $selected>$country</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Event Mode <span class="required">*</span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="mode" value="online" required /> Online</label>
                            <label><input type="radio" name="mode" value="physical" /> Physical</label>
                            <label><input type="radio" name="mode" value="hybrid" /> Hybrid</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Event Type <span class="required">*</span></label>
                        <select class="c-input c-input-select" name="type" required>
                            <option value="" disabled selected>Select category</option>
                            <option value="talk">Talk</option>
                            <option value="workshop">Workshop</option>
                            <option value="webinar">Webinar</option>
                            <option value="clean-up">Clean-up</option>
                            <option value="campaign">Campaign</option>
                            <option value="competition">Competition</option>
                            <option value="tree-planting">Tree Planting</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Event Banner <small>(Optional - PNG, JPG, JPEG)</small></label>
                        <div id="fileStatus"></div>
                        <!-- File Upload Area -->
                        <div class="file-upload-area" id="fileUploadArea">
                            <input type="file" name="banner" id="fileInput" accept="image/*" />
                            <div class="file-upload-text" id="uploadText">Click to upload or drag and drop</div>
                            <div class="file-upload-hint">Best viewed at 1200×400px | PNG, JPG, JPEG (Max 5MB)</div>
                        </div>
                        
                        <!-- Preview Banner -->
                        <div class="preview-container" id="previewContainer">
                            <img id="imagePreview" class="preview-image" />
                            <button type="button" class="replace-btn" id="replaceBtn">Replace Image</button>
                        </div>
                    </div>

                    <button type="submit" class="save-btn">Create Event</button>
                </form>
            </div>
        </section>

        <!-- Search & Results -->
        <section class="search-container" id="searchContainer" style="display: none;">
            <!-- Tabs -->
            <div class="tabs" id="tabs">
                <div class="tab active" data-type="all">All</div>
                <?php if ($isAdmin): ?>
                    <div class="tab" data-type="tickets">Tickets</div>
                <?php endif; ?>
                <div class="tab" data-type="profiles">Profiles</div>
                <div class="tab" data-type="blogs">Blogs</div>
                <div class="tab" data-type="events">Events</div>
                <div class="tab" data-type="trades">Trades</div>
                <?php if ($isAdmin): ?>
                    <div class="tab" data-type="faqs">FAQ</div>
                <?php endif; ?>
            </div>

            <!-- Results -->
            <div class="results" id="results"></div>
        </section>
    </main>

    <?php if (!$isAdmin): ?>
    <!-- Footer (Member Only) -->
    <hr>
    <footer>
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
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.html">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>
    <?php endif; ?>

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        document.getElementById('startDate').addEventListener('click', function() {
            this.showPicker();
        });
        document.getElementById('endDate').addEventListener('click', function() {
            this.showPicker();
        });
        document.getElementById('time').addEventListener('click', function() {
            this.showPicker();
        });

        // File upload functionality with validation
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const replaceBtn = document.getElementById('replaceBtn');
            const uploadText = document.getElementById('uploadText');
            const fileStatus = document.getElementById('fileStatus');
            const form = document.getElementById('createEventForm');
            
            let validFile = null;

            // File validation function
            function validateFile(file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                // Clear previous status
                fileStatus.innerHTML = '';
                fileStatus.className = 'file-status';
                
                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    fileStatus.innerHTML = 'Invalid file type. Please select JPG, PNG, or GIF.';
                    fileStatus.className = 'file-status error';
                    return false;
                }
                
                // Check file size
                if (file.size > maxSize) {
                    fileStatus.innerHTML = 'File too large. Maximum size is 5MB.';
                    fileStatus.className = 'file-status error';
                    return false;
                }
                
                // File is valid
                fileStatus.innerHTML = 'File is valid and ready to upload.';
                fileStatus.className = 'file-status success';
                return true;
            }

            // Show preview and hide upload area
            function showPreview(file) {
                // Hide upload area
                fileUploadArea.style.display = 'none';
                
                // Show preview container
                previewContainer.style.display = 'block';
                
                // Create preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            // Show upload area and hide preview
            function showUploadArea() {
                // Show upload area
                fileUploadArea.style.display = 'block';
                
                // Hide preview container
                previewContainer.style.display = 'none';
                
                // Reset preview image
                imagePreview.src = '';
                
                // Reset file input
                fileInput.value = '';
                validFile = null;
                
                // Hide file status
                fileStatus.innerHTML = '';
                fileStatus.className = 'file-status-hide';
                
                // Reset upload text & styles
                uploadText.textContent = 'Click to upload or drag and drop';
                uploadText.style.color = '';
                fileUploadArea.style.borderColor = 'var(--Gray)';
                fileUploadArea.style.background = 'var(--bg-color)';
            }

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                if (file) {
                    // Validate file
                    if (validateFile(file)) {
                        validFile = file;
                        showPreview(file);
                    } else {
                        // Invalid file - reset
                        validFile = null;
                        fileInput.value = ''; // Clear the invalid file
                    }
                }
            });

            // Handle replace button click
            replaceBtn.addEventListener('click', function() {
                showUploadArea();
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                
                if (file && !validFile) {
                    e.preventDefault();
                    alert('Please fix the file upload errors before submitting.');
                    return;
                }
                
                if (file && validFile) {
                    // Double-check validation before submit
                    if (!validateFile(file)) {
                        e.preventDefault();
                        alert('Please fix the file upload errors before submitting.');
                        return;
                    }
                }
            });

            // Drag and drop functionality
            fileUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileUploadArea.style.borderColor = 'var(--MainGreen)';
                fileUploadArea.style.background = 'var(--LowGreen)';
            });

            fileUploadArea.addEventListener('dragleave', function() {
                if (!validFile) {
                    fileUploadArea.style.borderColor = 'var(--Gray)';
                    fileUploadArea.style.background = 'var(--bg-color)';
                }
            });

            fileUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').min = today;
            
            // Update end date min when start date changes
            document.getElementById('startDate').addEventListener('change', function() {
                document.getElementById('endDate').min = this.value;
            });
        });
    </script>
</body>
</html>

<?php
mysqli_close($connection);
?>