<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../../pages/MemberPages/login.php");
    exit;
}

$userID = $_SESSION['userID'];
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['blogTitle'] ?? '');
    $excerpt = trim($_POST['blogExcerpt'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content = trim($_POST['blogContent'] ?? '');
    $tags = isset($_POST['selectedTags']) ? json_decode($_POST['selectedTags'], true) : [];

    // Validation
    if (empty($title) || empty($excerpt) || empty($category) || empty($content)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (strlen($title) > 100) {
        $errorMessage = "Title must not exceed 100 characters.";
    } elseif (strlen($excerpt) > 200) {
        $errorMessage = "Excerpt must not exceed 200 characters.";
    } else {
        // Insert blog into database
        $title = mysqli_real_escape_string($connection, $title);
        $excerpt = mysqli_real_escape_string($connection, $excerpt);
        $category = mysqli_real_escape_string($connection, $category);
        $content = mysqli_real_escape_string($connection, $content);

        $insertQuery = "
            INSERT INTO tblblog (userID, title, excerpt, category, content, date)
            VALUES ($userID, '$title', '$excerpt', '$category', '$content', NOW())
        ";

        if (mysqli_query($connection, $insertQuery)) {
            $blogID = mysqli_insert_id($connection);

            // Insert tags if any
            if (!empty($tags)) {
                foreach ($tags as $tagName) {
                    $tagName = mysqli_real_escape_string($connection, trim($tagName));
                    
                    // Check if tag exists, if not create it
                    $tagCheckQuery = "SELECT tagID FROM tbltag WHERE tagName = '$tagName'";
                    $tagCheckResult = mysqli_query($connection, $tagCheckQuery);
                    
                    if (mysqli_num_rows($tagCheckResult) > 0) {
                        $tagRow = mysqli_fetch_assoc($tagCheckResult);
                        $tagID = $tagRow['tagID'];
                    } else {
                        $tagInsertQuery = "INSERT INTO tbltag (tagName) VALUES ('$tagName')";
                        mysqli_query($connection, $tagInsertQuery);
                        $tagID = mysqli_insert_id($connection);
                    }
                    
                    // Link tag to blog
                    $blogTagQuery = "INSERT INTO tblblogtag (blogID, tagID) VALUES ($blogID, $tagID)";
                    mysqli_query($connection, $blogTagQuery);
                }
            }

            $successMessage = "Blog post published successfully! Redirecting...";
            header("refresh:2;url=readBlog.php?id=$blogID");
        } else {
            $errorMessage = "Error publishing blog. Please try again.";
        }
    }
}

// Fetch available tags from database
$tagsQuery = "SELECT DISTINCT tagName FROM tbltag ORDER BY tagName";
$tagsResult = mysqli_query($connection, $tagsQuery);
$availableTags = [];
while ($row = mysqli_fetch_assoc($tagsResult)) {
    $availableTags[] = $row['tagName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <style>
        /* Add Blog Page Styling */
        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 0.5rem;
            margin-left: 10rem;
        }

        .page-header p {
            color: var(--text-color-2);
            font-size: 1.125rem;
        }

        .back-to-blogs {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            background-color: transparent;
            text-decoration: none;
            font-size: 1.1rem;
        }

        .back-to-blogs img {
            width: 35px;
            height: 35px;
            transition: all 0.3s ease;
        }

        .back-to-blogs:hover {
            background-color: var(--bg-color-light-3);
            transform: translateX(-2px);
        }

        .blog-form {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .form-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background-color: var(--MainGreen);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--MainGreen);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        #blogContent {
            min-height: 300px;
        }

        .char-count {
            text-align: right;
            font-size: 0.875rem;
            color: var(--text-color-2);
            margin-top: 0.25rem;
        }

        /* Tags Section */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .popular-tags {
            margin-bottom: 1rem;
        }

        .popular-tags-label {
            font-size: 0.875rem;
            color: var(--text-color-2);
            margin-bottom: 0.5rem;
            display: block;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--bg-color-light-3);
            border: 1px solid var(--MainGreen);
            border-radius: 20px;
            color: var(--MainGreen);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tag-item:hover {
            background-color: var(--MainGreen);
            color: white;
        }

        .tag-item.selected {
            background-color: var(--MainGreen);
            color: white;
        }

        .selected-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
            min-height: 40px;
            padding: 0.5rem;
            border: 1px dashed var(--border-color);
            border-radius: 8px;
        }

        .selected-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--MainGreen);
            color: white;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .selected-tag button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.125rem;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .selected-tag button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Category Select */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
        }

        .category-option {
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--text-color);
            background-color: var(--bg-color);
        }

        .category-option:hover {
            border-color: var(--MainGreen);
            background-color: var(--bg-color-light-3);
        }

        .category-option.selected {
            border-color: var(--MainGreen);
            background-color: var(--MainGreen);
            color: white;
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .success-message {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--MainGreen);
            color: var(--MainGreen);
        }

        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--Red);
            color: var(--Red);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .btn-clear {
            padding: 0.875rem 2rem;
            background-color: transparent;
            color: var(--text-color);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-clear:hover {
            background-color: var(--Red);
            border-color: var(--Red);
            color: white;
            transform: translateY(-2px);
        }

        .btn-primary {
            padding: 0.875rem 2rem;
            background-color: var(--MainGreen);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: var(--btn-color-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        .dark-mode .blog-form {
            background-color: var(--bg-color-dark);
        }

        .dark-mode .form-group input[type="text"],
        .dark-mode .form-group textarea,
        .dark-mode .form-group select {
            background-color: var(--bg-color-dark-2);
            border-color: var(--border-color);
        }

        .dark-mode .tag-item {
            background-color: var(--bg-color-dark-4);
        }

        .dark-mode .category-option {
            background-color: var(--bg-color-dark-2);
        }

        .dark-mode .category-option:hover {
            background-color: var(--bg-color-dark-4);
        }

        .dark-mode .selected-tags {
            background-color: var(--bg-color-dark-2);
        }

        .c-notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
            background-color: var(--Red);
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            border-radius: 999px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            z-index: 2;
        }

        .c-navbar-side .c-notification-badge {
            top: 6px;
            right: 5px;
        }

        @media (max-width: 768px) {
            main {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .blog-form {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn-clear,
            .btn-primary {
                width: 100%;
            }

            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.75rem;
            }

            .category-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div id="cover" class="" onclick="hideMenu()"></div>

    <!-- Header -->
    <header>
        <section class="c-logo-section">
            <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
            <div class="c-text">ReLeaf</div>
        </section>

        <!-- Mobile Navigation -->
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
                    <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/aboutUs.html">About</a>
                </div>
            </div>
        </nav>

        <!-- Desktop Navigation -->
        <nav class="c-navbar-desktop">
            <a href="../../pages/MemberPages/memberIndex.php">Home</a>
            <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.php">Event</a>
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
        <section class="page-header">
            <div class="header-top">
                <a href="mainBlog.php" class="back-to-blogs" title="Back to Blogs">
                    <img src="../../assets/images/icon-back-light.svg" alt="Back" />
                </a>
                <div>
                    <h1>Create a New Blog Post</h1>
                </div>
            </div>
            <p>Share your thoughts and ideas with the ReLeaf community</p>
        </section>

        <?php if ($successMessage): ?>
            <div class="message success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="message error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form class="blog-form" id="blogForm" method="POST">
            <!-- Blog Header Section -->
            <div class="form-section">
                <h2>Blog Header</h2>

                <div class="form-group">
                    <label for="blogTitle">Title *</label>
                    <input type="text" id="blogTitle" name="blogTitle" placeholder="Enter your blog title..." required maxlength="100">
                    <div class="char-count"><span id="titleCount">0</span>/100</div>
                </div>

                <div class="form-group">
                    <label for="blogExcerpt">Excerpt *</label>
                    <textarea id="blogExcerpt" name="blogExcerpt" placeholder="Write a brief summary of your blog post..." required maxlength="200"></textarea>
                    <div class="char-count"><span id="excerptCount">0</span>/200</div>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <div class="category-grid">
                        <div class="category-option" data-category="Environment">🌱 Environment</div>
                        <div class="category-option" data-category="Technology">💻 Technology</div>
                        <div class="category-option" data-category="Gardening">🌿 Gardening</div>
                        <div class="category-option" data-category="Agriculture">🌾 Agriculture</div>
                        <div class="category-option" data-category="Travel">✈️ Travel</div>
                        <div class="category-option" data-category="Lifestyle">🏡 Lifestyle</div>
                    </div>
                    <input type="hidden" id="selectedCategory" name="category" required>
                </div>

                <div class="form-group">
                    <label>Tags</label>
                    <div class="popular-tags">
                        <span class="popular-tags-label">Popular tags:</span>
                        <div class="tags-container">
                            <?php foreach ($availableTags as $tag): ?>
                                <div class="tag-item" data-tag="<?php echo htmlspecialchars($tag); ?>">
                                    <?php echo htmlspecialchars($tag); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="selected-tags" id="selectedTags"></div>
                    <input type="hidden" id="tagsInput" name="selectedTags" value="[]">
                </div>
            </div>

            <!-- Blog Content Section -->
            <div class="form-section">
                <h2>Blog Content</h2>

                <div class="form-group">
                    <label for="blogContent">Content *</label>
                    <textarea id="blogContent" name="blogContent" placeholder="Write your blog content here..." required></textarea>
                    <div class="char-count"><span id="contentCount">0</span> characters</div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-clear" onclick="clearForm()">Clear All</button>
                <button type="submit" class="btn-primary">Publish Blog</button>
            </div>
        </form>
    </main>

    <!-- Footer -->
    <footer>
        <section class="c-footer-info-section">
            <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
            <div class="c-text">ReLeaf</div>
            <div class="c-text c-text-center">
                "Relief for the Planet, One Leaf at a Time."<br>
                "Together, We Can ReLeaf the Earth."
            </div>
            <div class="c-text c-text-label">+60 12 345 6789</div>
            <div class="c-text">abc@gmail.com</div>
        </section>

        <section class="c-footer-links-section">
            <div>
                <b>My Account</b><br>
                <a href="../../pages/MemberPages/mProfile.php">My Account</a><br>
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.html">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.html">FAQs</a><br>
                <a href="../../pages/MemberPages/mContactSupport.php">Helps and Support</a>
            </div>
            <div>
                <b>Community</b><br>
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.php">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>

    <script>const isAdmin = false;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        let selectedTags = [];

        // Character counter for title
        document.getElementById('blogTitle').addEventListener('input', function() {
            document.getElementById('titleCount').textContent = this.value.length;
        });

        // Character counter for excerpt
        document.getElementById('blogExcerpt').addEventListener('input', function() {
            document.getElementById('excerptCount').textContent = this.value.length;
        });

        // Character counter for content
        document.getElementById('blogContent').addEventListener('input', function() {
            document.getElementById('contentCount').textContent = this.value.length;
        });

        // Category selection
        document.querySelectorAll('.category-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedCategory').value = this.dataset.category;
            });
        });

        // Tag selection
        document.querySelectorAll('.tag-item').forEach(tag => {
            tag.addEventListener('click', function() {
                const tagName = this.dataset.tag;
                if (selectedTags.includes(tagName)) {
                    selectedTags = selectedTags.filter(t => t !== tagName);
                    this.classList.remove('selected');
                } else {
                    selectedTags.push(tagName);
                    this.classList.add('selected');
                }
                updateSelectedTags();
            });
        });

        function updateSelectedTags() {
            const container = document.getElementById('selectedTags');
            container.innerHTML = '';
            
            selectedTags.forEach(tag => {
                const tagElement = document.createElement('div');
                tagElement.className = 'selected-tag';
                tagElement.innerHTML = `
                    ${tag}
                    <button type="button" onclick="removeTag('${tag}')">&times;</button>
                `;
                container.appendChild(tagElement);
            });

            document.getElementById('tagsInput').value = JSON.stringify(selectedTags);
        }

        function removeTag(tagName) {
            selectedTags = selectedTags.filter(t => t !== tagName);
            document.querySelectorAll('.tag-item').forEach(tag => {
                if (tag.dataset.tag === tagName) {
                    tag.classList.remove('selected');
                }
            });
            updateSelectedTags();
        }

        function clearForm() {
            if (confirm('Are you sure you want to clear all fields?')) {
                document.getElementById('blogForm').reset();
                document.getElementById('titleCount').textContent = '0';
                document.getElementById('excerptCount').textContent = '0';
                document.getElementById('contentCount').textContent = '0';
                document.getElementById('selectedCategory').value = '';
                document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
                selectedTags = [];
                document.querySelectorAll('.tag-item').forEach(tag => tag.classList.remove('selected'));
                document.getElementById('selectedTags').innerHTML = '';
                document.getElementById('tagsInput').value = '[]';
            }
        }

        // Form validation before submit
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            const category = document.getElementById('selectedCategory').value;
            if (!category) {
                e.preventDefault();
                alert('Please select a category');
            }
        });
    </script>
</body>
</html>