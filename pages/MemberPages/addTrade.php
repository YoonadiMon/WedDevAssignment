<?php
    include("../../php/dbConn.php");
    
    // Set current user ID 
    $currentUserID = 4;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Listing - ReLeaf Trade</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <link rel="stylesheet" href="../../style/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        /* Additional styling for enhanced visual appeal */
        .add-listing-container {
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

        .form-section {
            background: var(--bg-color);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 5px;
        }

        .section-header p {
            color: var(--Gray);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-heading);
            font-size: 14px;
        }

        .form-group label.required::after {
            content: " *";
            color: var(--Red);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            background: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            font-family: inherit;
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
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .character-count {
            text-align: right;
            font-size: 12px;
            color: var(--Gray);
            margin-top: 5px;
        }

        .character-count.warning {
            color: var(--Red);
        }

        /* File Upload Styles */
        .file-upload {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--sec-bg-color);
        }

        .file-upload:hover {
            border-color: var(--MainGreen);
            background: rgba(16, 185, 129, 0.05);
        }

        .file-upload.dragover {
            border-color: var(--MainGreen);
            background: rgba(16, 185, 129, 0.1);
        }

        .file-upload input {
            display: none;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--Gray);
        }

        .file-upload-text {
            font-size: 16px;
            margin-bottom: 10px;
            color: var(--text-heading);
        }

        .file-upload-hint {
            font-size: 14px;
            color: var(--Gray);
            margin-bottom: 15px;
        }

        .browse-btn {
            background: var(--MainGreen);
            color: var(--White);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .browse-btn:hover {
            background: var(--btn-color-hover);
        }

        .attachments-preview {
            margin-top: 20px;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            background: var(--sec-bg-color);
            border-radius: 6px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
        }

        .attachment-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .attachment-icon {
            font-size: 20px;
            color: var(--Gray);
        }

        .attachment-details {
            flex: 1;
        }

        .attachment-name {
            font-weight: 500;
            color: var(--text-heading);
            margin-bottom: 2px;
        }

        .attachment-size {
            font-size: 12px;
            color: var(--Gray);
        }

        .remove-attachment {
            background: none;
            border: none;
            color: var(--Red);
            cursor: pointer;
            padding: 5px;
            font-size: 18px;
            transition: color 0.3s;
        }

        .remove-attachment:hover {
            color: #dc2626;
        }

        /* Plant-specific fields */
        .plant-fields {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid var(--LightGreen);
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
        }

        /* Item-specific fields */
        .item-fields {
            background: rgba(99, 102, 241, 0.05);
            border: 1px solid #a5b4fc;
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .cancel-btn {
            background: transparent;
            color: var(--Gray);
            border: 1px solid var(--border-color);
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .cancel-btn:hover {
            background: var(--sec-bg-color);
        }

        .submit-btn {
            background: var(--MainGreen);
            color: var(--White);
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background: var(--btn-color-hover);
        }

        .submit-btn:disabled {
            background: var(--Gray);
            cursor: not-allowed;
        }

        /* Progress indicator */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border-color);
            z-index: 1;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--bg-color);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--Gray);
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .step-number.active {
            background: var(--MainGreen);
            border-color: var(--MainGreen);
            color: var(--White);
        }

        .step-label {
            font-size: 12px;
            color: var(--Gray);
            text-align: center;
        }

        .step-label.active {
            color: var(--MainGreen);
            font-weight: 500;
        }

        /* Responsive Design */
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
            
            .progress-steps {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .progress-steps::before {
                display: none;
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
            <a href="../../pages/MemberPages/memberIndex.php" class="c-logo-link">
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

                        <a href="../../pages/MemberPages/mSetting.php">
                            <img src="../../assets/images/setting-light.svg" alt="Settings">
                        </a>
                    </section>

                    <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php" class="active">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.php">About</a>
                </div>
            </div>

        </nav>

        <!-- Menu Links Desktop + Tablet -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/MemberPages/memberIndex.php">Home</a>
            <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.php">Event</a>
            <a href="../../pages/CommonPages/mainTrade.php" class="active">Trade</a>
            <a href="../../pages/CommonPages/aboutUs.php">About</a>
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

            <a href="../../pages/MemberPages/mSetting.php">
                <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
            </a>
        </section>
    
    </header>

    <hr>

    <!-- Main Content -->
    <main class="content" id="content">
        <div class="add-listing-container">
            <!-- Back Button -->
            <a href="../../pages/CommonPages/mainTrade.php" class="back-button">
                ← Back to Marketplace
            </a>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="progress-step">
                    <div class="step-number active">1</div>
                    <div class="step-label active">Basic Info</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">2</div>
                    <div class="step-label">Details</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-label">Media</div>
                </div>
            </div>

            <!-- PHP Form Processing -->
            <?php
            
            // Process form submission
            $successMessage = "";
            $errorMessage = "";
            
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Collect and sanitize form data
                $title = mysqli_real_escape_string($connection, $_POST['title']);
                $description = mysqli_real_escape_string($connection, $_POST['description']);
                $tags = mysqli_real_escape_string($connection, $_POST['tags']);
                $category = mysqli_real_escape_string($connection, $_POST['category']);
                $itemType = mysqli_real_escape_string($connection, $_POST['listingType']);
                $itemCondition = mysqli_real_escape_string($connection, $_POST['condition']);
                $lookingFor = mysqli_real_escape_string($connection, $_POST['lookingFor']);
                
                // Handle file upload
                $imageUrl = "../../assets/images/placeholder-image.jpg"; // Default placeholder
                
                // if (isset($_FILES['fileInput']) && $_FILES['fileInput']['error'] === UPLOAD_ERR_OK) {
                //     $uploadDir = "../../assets/uploads/";
                    
                //     // Create uploads directory if it doesn't exist
                //     if (!is_dir($uploadDir)) {
                //         mkdir($uploadDir, 0755, true);
                //     }
                    
                //     // Generate unique filename
                //     $fileName = time() . '_' . uniqid() . '_' . basename($_FILES['fileInput']['name']);

                //     $targetFilePath = $uploadDir . $fileName;

                //     // Simple file type check
                //     $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                //     $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                //     if (in_array($imageFileType, $allowedTypes)) {
                //         if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $targetFilePath)) {
                //             $imageUrl = $targetFilePath;
                //         } else {
                //             $errorMessage .= "Failed to upload file. ";
                //         }
                //     } else {
                //         $errorMessage .= "Only JPG, JPEG, PNG & GIF files are allowed. ";
                //     }
                // } else {
                //     // No file uploaded or upload error
                //     if (isset($_FILES['fileInput'])) {
                //         $uploadError = $_FILES['fileInput']['error'];
                //         if ($uploadError != UPLOAD_ERR_NO_FILE) {
                //             $errorMessage .= "File upload error: " . $uploadError . ". ";
                //         }
                //     }
                // }
                
                // Handle type-specific fields
                $species = "";
                $growthStage = "";
                $careInstructions = "";
                $brand = "";
                $dimensions = "";
                $usageHistory = "";
                
                if ($itemType == 'plant') {
                    $species = mysqli_real_escape_string($connection, $_POST['species']);
                    $growthStage = mysqli_real_escape_string($connection, $_POST['growthStage']);
                    $careInstructions = mysqli_real_escape_string($connection, $_POST['careInstructions']);
                } else if ($itemType == 'item') {
                    $brand = mysqli_real_escape_string($connection, $_POST['brand']);
                    $dimensions = mysqli_real_escape_string($connection, $_POST['dimensions']);
                    $usageHistory = mysqli_real_escape_string($connection, $_POST['usageHistory']);
                }
                
                // Insert into database
                $sql = "INSERT INTO tbltrade_listings (
                    userID, title, description, tags, imageUrl, category, dateListed, 
                    status, itemType, itemCondition, species, growthStage, careInstructions, 
                    brand, dimensions, usageHistory, lookingFor
                ) VALUES (
                    '$currentUserID', '$title', '$description', '$tags', '$imageUrl', '$category', 
                    CURDATE(), 'active', '$itemType', '$itemCondition', '$species', '$growthStage', 
                    '$careInstructions', '$brand', '$dimensions', '$usageHistory', '$lookingFor'
                )";
                
                if (mysqli_query($connection, $sql)) {
                    $successMessage = "Your listing has been created successfully!";
                } else {
                    $errorMessage = "Error: " . $sql . "<br>" . mysqli_error($connection);
                }
            }
            
            // Display success or error message
            if (!empty($successMessage)) {
                echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>$successMessage</div>";
            }
            
            if (!empty($errorMessage)) {
                echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>$errorMessage</div>";
            }
            ?>

            <!-- Listing Form -->
            <form id="addListingForm" method="POST" enctype="multipart/form-data">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>Basic Information</h2>
                        <p>Tell us about what you're offering for trade</p>
                    </div>

                    <div class="form-group">
                        <label for="listingType" class="required">What are you listing?</label>
                        <select id="listingType" name="listingType" required>
                            <option value="">Select type</option>
                            <option value="Plant" <?php if(isset($_POST['listingType']) && $_POST['listingType'] == 'Plant') echo 'selected'; ?>>Plant</option>
                            <option value="Item" <?php if(isset($_POST['listingType']) && $_POST['listingType'] == 'Item') echo 'selected'; ?>>Item</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="listingTitle" class="required">Listing Title</label>
                        <input type="text" id="listingTitle" name="title" required 
                               placeholder="e.g., Mature Monstera Plant, Gardening Tool Set, etc." 
                               maxlength="100" value="<?php if(isset($_POST['title'])) echo htmlspecialchars($_POST['title']); ?>">
                        <div class="character-count" id="titleCount">0/100</div>
                    </div>

                    <div class="form-group">
                        <label for="listingCategory" class="required">Category</label>
                        <select id="listingCategory" name="category" required>
                            <option value="">Select a category</option>
                            <option value="Plants" <?php if(isset($_POST['category']) && $_POST['category'] == 'Plants') echo 'selected'; ?>>Plants</option>
                            <option value="Gardening Tools" <?php if(isset($_POST['category']) && $_POST['category'] == 'Gardening Tools') echo 'selected'; ?>>Gardening Tools</option>
                            <option value="Seeds & Saplings" <?php if(isset($_POST['category']) && $_POST['category'] == 'Seeds & Saplings') echo 'selected'; ?>>Seeds & Saplings</option>
                            <option value="Garden Decor" <?php if(isset($_POST['category']) && $_POST['category'] == 'Garden Decor') echo 'selected'; ?>>Garden Decor</option>
                            <option value="Gardening Books" <?php if(isset($_POST['category']) && $_POST['category'] == 'Gardening Books') echo 'selected'; ?>>Gardening Books</option>
                            <option value="Other" <?php if(isset($_POST['category']) && $_POST['category'] == 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="listingDescription" class="required">Description</label>
                        <textarea id="listingDescription" name="description" required 
                                  placeholder="Describe your item in detail. Include condition, size, special features, and what you're looking to trade for..."
                                  maxlength="500"><?php if(isset($_POST['description'])) echo htmlspecialchars($_POST['description']); ?></textarea>
                        <div class="character-count" id="descriptionCount">0/500</div>
                    </div>

                    <div class="form-group">
                        <label for="listingTags">Tags</label>
                        <input type="text" id="listingTags" name="tags" 
                               placeholder="e.g., indoor plant, gardening tools, organic, vintage (separate with commas)"
                               maxlength="200" value="<?php if(isset($_POST['tags'])) echo htmlspecialchars($_POST['tags']); ?>">
                        <div class="character-count" id="tagsCount">0/200</div>
                    </div>
                </div>

                <!-- Specific Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>Item Details</h2>
                        <p>Provide specific details about your listing</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="itemCondition" class="required">Condition</label>
                            <select id="itemCondition" name="condition" required>
                                <option value="">Select condition</option>
                                <option value="New" <?php if(isset($_POST['condition']) && $_POST['condition'] == 'New') echo 'selected'; ?>>New</option>
                                <option value="Excellent" <?php if(isset($_POST['condition']) && $_POST['condition'] == 'Excellent') echo 'selected'; ?>>Excellent</option>
                                <option value="Good" <?php if(isset($_POST['condition']) && $_POST['condition'] == 'Good') echo 'selected'; ?>>Good</option>
                                <option value="Fair" <?php if(isset($_POST['condition']) && $_POST['condition'] == 'Fair') echo 'selected'; ?>>Fair</option>
                                <option value="Poor" <?php if(isset($_POST['condition']) && $_POST['condition'] == 'Poor') echo 'selected'; ?>>Poor</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lookingFor">Looking For (Trade Preferences)</label>
                            <textarea id="lookingFor" name="lookingFor" 
                                      placeholder="What specific items are you looking to trade for? e.g., gardening tools, specific plants, etc."
                                      maxlength="200"><?php if(isset($_POST['lookingFor'])) echo htmlspecialchars($_POST['lookingFor']); ?></textarea>
                            <div class="character-count" id="lookingForCount">0/200</div>
                        </div>
                    </div>

                    <!-- Plant-specific fields (shown when plant is selected) -->
                    <div id="plantFields" class="plant-fields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="plantSpecies">Plant Species</label>
                                <input type="text" id="plantSpecies" name="species" 
                                       placeholder="e.g., Monstera Deliciosa, Sansevieria Trifasciata"
                                       value="<?php if(isset($_POST['species'])) echo htmlspecialchars($_POST['species']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="plantGrowthStage">Growth Stage</label>
                                <select id="plantGrowthStage" name="growthStage">
                                    <option value="">Select stage</option>
                                    <option value="seedling" <?php if(isset($_POST['growthStage']) && $_POST['growthStage'] == 'seedling') echo 'selected'; ?>>Seedling</option>
                                    <option value="young" <?php if(isset($_POST['growthStage']) && $_POST['growthStage'] == 'young') echo 'selected'; ?>>Young Plant</option>
                                    <option value="established" <?php if(isset($_POST['growthStage']) && $_POST['growthStage'] == 'established') echo 'selected'; ?>>Established</option>
                                    <option value="mature" <?php if(isset($_POST['growthStage']) && $_POST['growthStage'] == 'mature') echo 'selected'; ?>>Mature</option>
                                    <option value="cutting" <?php if(isset($_POST['growthStage']) && $_POST['growthStage'] == 'cutting') echo 'selected'; ?>>Cutting/Propagation</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="plantCare">Care Instructions</label>
                            <textarea id="plantCare" name="careInstructions" 
                                      placeholder="Light requirements, watering schedule, special care tips..."
                                      maxlength="300"><?php if(isset($_POST['careInstructions'])) echo htmlspecialchars($_POST['careInstructions']); ?></textarea>
                            <div class="character-count" id="careCount">0/300</div>
                        </div>
                    </div>

                    <!-- Item-specific fields (shown when item is selected) -->
                    <div id="itemFields" class="item-fields" style="display: none;">
                        <div class="form-group">
                            <label for="itemBrand">Brand/Manufacturer</label>
                            <input type="text" id="itemBrand" name="brand" 
                                   placeholder="e.g., Fiskars, Gardena, Generic"
                                   value="<?php if(isset($_POST['brand'])) echo htmlspecialchars($_POST['brand']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="itemDimensions">Dimensions/Size</label>
                            <input type="text" id="itemDimensions" name="dimensions" 
                                   placeholder="e.g., 12x8 inches, Large, etc."
                                   value="<?php if(isset($_POST['dimensions'])) echo htmlspecialchars($_POST['dimensions']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="itemUsage">Usage History</label>
                            <textarea id="itemUsage" name="usageHistory" 
                                      placeholder="How long have you used this item? Any notable wear and tear?"
                                      maxlength="200"><?php if(isset($_POST['usageHistory'])) echo htmlspecialchars($_POST['usageHistory']); ?></textarea>
                            <div class="character-count" id="usageCount">0/200</div>
                        </div>
                    </div>
                </div>

                <!-- Media Upload Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>Photos & Media</h2>
                        <p>Add clear photos to help others see what you're offering</p>
                    </div>

                    <div class="form-group">
                        <label>Upload Photos</label>
                        <div class="file-upload" id="fileUploadArea">
                            <div class="upload-icon">📷</div>
                            <div class="file-upload-text">Drag & drop photos here or click to browse</div>
                            <div class="file-upload-hint">Supported formats: JPG, PNG, GIF • Max 5MB per file</div>
                            <button type="button" class="browse-btn">Browse Files</button>
                            <input type="file" id="fileInput" name="fileInput" accept="image/*" multiple>
                        </div>
                        <div class="attachments-preview" id="attachmentsPreview">
                            <!-- Attachments will be listed here -->
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="window.location.href='../../pages/CommonPages/mainTrade.php'">
                        Cancel
                    </button>
                    <button type="submit" class="submit-btn" id="submitBtn">
                        Create Listing
                    </button>
                </div>
            </form>
        </div>
    </main>
    <!-- Search & Results -->
    <section class="search-container" id="searchContainer" style="display: none;">
        <div class="tabs" id="tabs">
            <div class="tab active" data-type="all">All</div>
            <div class="tab" data-type="profiles">Profiles</div>
            <div class="tab" data-type="blogs">Blogs</div>
            <div class="tab" data-type="events">Events</div>
            <div class="tab" data-type="trades">Trades</div>
        </div>
        <div class="results" id="results"></div>
    </section>
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
                <a href="../../pages/MemberPages/mProfile.php">My Account</a><br>
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.php">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.php">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a><br>
                <a href="../../pages/MemberPages/mSetting.php">Settings</a>
            </div>
            <div>
                <b>Community</b><br>
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.php">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addListingForm');
            const listingType = document.getElementById('listingType');
            const plantFields = document.getElementById('plantFields');
            const itemFields = document.getElementById('itemFields');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('fileInput');
            const attachmentsPreview = document.getElementById('attachmentsPreview');
            const fileUploadText = fileUploadArea.querySelector('.file-upload-text');
            
            // Character counters
            const titleInput = document.getElementById('listingTitle');
            const descriptionInput = document.getElementById('listingDescription');
            const tagsInput = document.getElementById('listingTags');
            const careInput = document.getElementById('plantCare');
            const usageInput = document.getElementById('itemUsage');
            const lookingForInput = document.getElementById('lookingFor');

            // Initialize character counters
            setupCharacterCounter(titleInput, 'titleCount', 100);
            setupCharacterCounter(descriptionInput, 'descriptionCount', 500);
            setupCharacterCounter(tagsInput, 'tagsCount', 200);
            setupCharacterCounter(careInput, 'careCount', 300);
            setupCharacterCounter(usageInput, 'usageCount', 200);
            setupCharacterCounter(lookingForInput, 'lookingForCount', 200);
            
            // Show/hide plant or item specific fields based on initial value
            updateFieldVisibility();
            
            // Show/hide plant or item specific fields
            listingType.addEventListener('change', function() {
                updateFieldVisibility();
            });
            
            function updateFieldVisibility() {
                const type = listingType.value;
                
                plantFields.style.display = type === 'Plant' ? 'block' : 'none';
                itemFields.style.display = type === 'Item' ? 'block' : 'none';
            }
            
            // File upload handling - SIMPLIFIED
            fileUploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    
                    // Check file size (5MB limit)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size too large. Maximum size is 5MB.');
                        this.value = '';
                        return;
                    }
                    
                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('File type not supported. Please upload JPG, PNG, or GIF files.');
                        this.value = '';
                        return;
                    }
                    
                    // Update UI to show selected file
                    fileUploadText.textContent = `Selected: ${file.name}`;
                    updateFilePreview(file);
                }
            });
            
            function updateFilePreview(file) {
                attachmentsPreview.innerHTML = '';
                
                const attachmentItem = document.createElement('div');
                attachmentItem.className = 'attachment-item';
                
                // Create preview for images
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        attachmentItem.innerHTML = `
                            <div class="attachment-info">
                                <div class="attachment-icon">🖼️</div>
                                <img src="${e.target.result}" alt="Preview" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <div class="attachment-details">
                                    <div class="attachment-name">${file.name}</div>
                                    <div class="attachment-size">${formatFileSize(file.size)}</div>
                                </div>
                            </div>
                            <button type="button" class="remove-attachment" id="removeFile">×</button>
                        `;
                        
                        // Add event listener to remove button
                        attachmentItem.querySelector('#removeFile').addEventListener('click', function() {
                            fileInput.value = '';
                            attachmentsPreview.innerHTML = '';
                            fileUploadText.textContent = 'Click to select a photo';
                        });
                    };
                    reader.readAsDataURL(file);
                }
                
                attachmentsPreview.appendChild(attachmentItem);
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            function setupCharacterCounter(inputElement, countElementId, maxLength) {
                const countElement = document.getElementById(countElementId);
                
                inputElement.addEventListener('input', function() {
                    const length = this.value.length;
                    countElement.textContent = `${length}/${maxLength}`;
                    
                    if (length > maxLength * 0.9) {
                        countElement.classList.add('warning');
                    } else {
                        countElement.classList.remove('warning');
                    }
                });
                
                // Initialize count
                countElement.textContent = `0/${maxLength}`;
            }
            
            // Form validation
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
            
            function validateForm() {
                const listingTypeValue = listingType.value;
                
                if (!listingTypeValue) {
                    alert('Please select what type of item you are listing.');
                    listingType.focus();
                    return false;
                }

                if (listingTypeValue === 'Plant') {
                    const species = document.getElementById('plantSpecies').value;
                    const growthStage = document.getElementById('plantGrowthStage').value;
                    
                    if (!species || !growthStage) {
                        alert('Please fill in all required plant details.');
                        return false;
                    }
                }
                
                if (listingTypeValue === 'Item') {
                    const condition = document.getElementById('itemCondition').value;
                    
                    if (!condition) {
                        alert('Please select the condition of your item.');
                        return false;
                    }
                }
                
                return true;
            }
        });
    </script>
    <script src="../../javascript/mainScript.js"></script>
</body>
</html>