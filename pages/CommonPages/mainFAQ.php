<?php
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $action = $_POST['action'];
    if ($action === 'add') {
        $category = mysqli_real_escape_string($connection, $_POST['category']);
        $question = mysqli_real_escape_string($connection, $_POST['question']);
        $answer = mysqli_real_escape_string($connection, $_POST['answer']);
        
        $sql = "INSERT INTO tblfaq (category, question, answer) VALUES ('$category', '$question', '$answer')";
        $result = mysqli_query($connection, $sql);
        
        echo json_encode(['success' => $result, 'message' => $result ? 'FAQ added successfully' : 'Failed to add FAQ']);
        exit;
    }
    
    if ($action === 'update') {
        $faqID = intval($_POST['faqID']);
        $category = mysqli_real_escape_string($connection, $_POST['category']);
        $question = mysqli_real_escape_string($connection, $_POST['question']);
        $answer = mysqli_real_escape_string($connection, $_POST['answer']);
        
        $sql = "UPDATE tblfaq SET category='$category', question='$question', answer='$answer' WHERE faqID=$faqID";
        $result = mysqli_query($connection, $sql);
        
        echo json_encode(['success' => $result, 'message' => $result ? 'FAQ updated successfully' : 'Failed to update FAQ']);
        exit;
    }
    
    if ($action === 'delete') {
        $faqID = intval($_POST['faqID']);
        
        $sql = "DELETE FROM tblfaq WHERE faqID=$faqID";
        $result = mysqli_query($connection, $sql);
        
        echo json_encode(['success' => $result, 'message' => $result ? 'FAQ deleted successfully' : 'Failed to delete FAQ']);
        exit;
    }
    
    if ($action === 'get') {
        $faqID = intval($_POST['faqID']);
        
        $sql = "SELECT * FROM tblfaq WHERE faqID=$faqID";
        $result = mysqli_query($connection, $sql);
        $faq = mysqli_fetch_assoc($result);
        
        echo json_encode(['success' => true, 'faq' => $faq]);
        exit;
    }
}

// Fetch all FAQs grouped by category
$sql = "SELECT * FROM tblfaq ORDER BY category, faqID";
$result = mysqli_query($connection, $sql);

$faqsByCategory = [];
while ($row = mysqli_fetch_assoc($result)) {
    $category = $row['category'];
    if (!isset($faqsByCategory[$category])) {
        $faqsByCategory[$category] = [];
    }
    $faqsByCategory[$category][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        .faq-container {
            display: flex;
            max-width: 1200px;
            padding-left: 1rem auto;
            margin: 2rem auto 0;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .faq-container.admin-layout {
            display: grid;
            grid-template-columns: 1fr 500px;
            gap: 2rem;
            max-width: 1200px;
            align-items: start;
        }

        .faq-content {
            order: 1;
            transition: all 0.3s ease;
            min-width: 0; /* Prevent overflow */
        }

        .faq-container.admin-layout .faq-header {
            display: none;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 3rem;
            transition: all 0.3s ease;
        }

        .faq-header img {
            width: 60px;
            height: auto;
        }

        .faq-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .faq-header p {
            font-size: 1.125rem;
            color: var(--DarkerGray);
        }

        .dark-mode .faq-header p {
            color: var(--Gray);
        }

        .faq-category {
            margin-bottom: 2.5rem;
        }

        .category-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--MainGreen);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--LowGreen);
        }

        .faq-item {
            background-color: var(--bg-color);
            border: 2px solid var(--Gray);
            border-radius: 12px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .faq-item:hover {
            color: var(--MainGreen);
            border-color: var(--MainGreen);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        .faq-item:hover .question-text {
            color: var(--MainGreen);
        }

        .faq-question {
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            user-select: none;
            min-height: 60px;
            position: relative;
        }

        .question-text {
            font-size: 1.125rem;
            font-weight: 600;
            flex: 1;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            min-width: 0;
            max-width: calc(100% - 7rem);
            line-height: 1.4;
            padding-right: 2rem;
        }

        .faq-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
            color: var(--MainGreen);
            font-weight: bold;
            font-size: 1.5rem;
            line-height: 1;
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        .answer-text {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-color);
            opacity: 0.85;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .faq-cta {
            background: linear-gradient(135deg, var(--MainGreen), var(--LowGreen), var(--MainGreen));
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            margin-top: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 8px 24px var(--shadow-color);
        }

        .faq-cta h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .faq-cta p {
            font-size: 1.125rem;
            color: var(--DarkerGray);
            margin-bottom: 1.5rem;
        }

        .dark-mode .faq-cta p {
            color: var(--Gray);
        }

        .cta-button {
            display: inline-block;
            padding: 0.875rem 2rem;
            background-color: var(--MainGreen);
            color: var(--White);
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px var(--shadow-color);
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        /* Admin Panel Styles */
        .admin-panel {
            display: none;
            position: sticky;
            top: 2rem;
            background-color: var(--bg-color);
            border: 2px solid var(--MainGreen);
            border-radius: 16px;
            padding: 1.5rem;
            height: fit-content;
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
            order: 2;
            box-sizing: border-box;
        }

        .admin-panel.active {
            display: block;
        }

        .admin-panel h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--MainGreen);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--MainGreen);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group label small {
            color: var(--Gray);
            font-size: 0.75rem;
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--Gray);
            border-radius: 8px;
            font-size: 1rem;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--MainGreen);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn-group button {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--MainGreen);
            color: var(--White);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--Gray);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            opacity: 0.8;
        }

        .admin-actions {
            position: absolute;
            top: 1rem;
            right: 3.75rem;
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: -1;
        }

        .faq-item.active .admin-actions {
            opacity: 1;
            visibility: visible;
            z-index: 1000;
        }

        .admin-actions button {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-edit {
            background-color: var(--LowGreen);
            color: var(--White);
        }

        .btn-delete {
            background-color: var(--LowRed);
            color: var(--White);
        }

        .btn-edit:hover,
        .btn-delete:hover {
            opacity: 0.8;
            transform: scale(1.1);
        }

        .admin-fab { 
            position: fixed; 
            bottom: 2rem; 
            right: 2rem; 
            width: 60px; 
            height: 60px; 
            background: var(--MainGreen); 
            border: none; 
            border-radius: 50%; 
            cursor: pointer; 
            font-size: 1.5rem; 
            box-shadow: 0 8px 24px var(--shadow-color); 
            transition: all 0.3s ease; 
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        } 

        .admin-fab img { 
            width: 28px; 
            height: auto; 
        } 

        .admin-fab:hover { 
            transform: scale(1.1); 
            box-shadow: 0 6px 12px var(--MainGreen); 
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background: var(--LowGreen);
            color: var(--White);
            border: 1px solid var(--MainGreen);
        }

        .alert-error {
            background: var(--LowRed);
            color: var(--White);
            border: 1px solid var(--Red);
        }

        .required {
            color: var(--Red);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .faq-container.admin-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
                max-width: 100%;
            }
            
            .admin-panel {
                position: static;
                order: 3;
                max-height: none;
            }
            
            .faq-content {
                order: 1;
            }
        }

        @media (max-width: 768px) {
            .faq-container {
                padding: 0 1rem;
            }
            
            .faq-header img {
                width: 40px;
                height: auto;
            }

            .faq-header h1 {
                font-size: 2rem;
            }

            .category-title {
                font-size: 1.5rem;
            }

            .question-text {
                font-size: 1rem;
            }

            .faq-question {
                padding: 1rem;
            }

            .faq-cta {
                padding: 2rem 1.5rem;
            }

            .faq-cta h2 {
                font-size: 1.5rem;
            }

            .admin-actions {
                position: static;
                display: flex;
                justify-content: flex-end;
                padding: 0.5rem 1rem;
                background-color: var(--LowGreen);
                opacity: 1;
                visibility: visible;
            }

            .faq-icon {
                align-self: flex-end;
            }
            
            .admin-panel {
                padding: 1rem;
            }
            
            .admin-fab {
                bottom: 1rem;
                right: 1rem;
                width: 50px;
                height: 50px;
            }
            
            .admin-fab img {
                width: 24px;
            }
        }

        @media (max-width: 480px) {
            .faq-header h1 {
                font-size: 1.75rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .faq-container {
                margin: 1rem auto;
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
                            <a href="../../pages/adminPages/aProfile.php">
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
                        <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
                        <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                    <?php else: ?>
                        <!-- Member Menu Items -->
                        <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
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
                <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
                <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
            <?php else: ?>
                <!-- Member Desktop Menu -->
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
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
    <main class="content" id="content">
        <section class="faq-container" id="faqContainer">
            <div class="faq-content" id="faqContent">
                <!-- Header -->
                <div class="faq-header" id="faqHeader">
                    <img src="../../assets/images/seedling-img-green.svg">
                    <h1>Frequently Asked Questions</h1>
                    <p>Find answers to common questions about ReLeaf</p>
                </div>

                <!-- FAQ Categories -->
                <?php foreach ($faqsByCategory as $category => $faqs): ?>
                <div class="faq-category">
                    <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                    
                    <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item" data-faq-id="<?php echo $faq['faqID']; ?>">
                        <?php if ($isAdmin): ?>
                        <div class="admin-actions">
                            <button class="btn-edit" onclick="editFAQ(<?php echo $faq['faqID']; ?>)" title="Edit">✏️</button>
                            <button class="btn-delete" onclick="deleteFAQ(<?php echo $faq['faqID']; ?>)" title="Delete">🗑️</button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="faq-question">
                            <span class="question-text"><?php echo htmlspecialchars($faq['question']); ?></span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p class="answer-text"><?php echo htmlspecialchars($faq['answer']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                
                <?php if (!$isAdmin): ?>         
                <!-- Call to Action -->
                <div class="faq-cta" id="faqCta">
                    <h2>Still have questions?</h2>
                    <p>Our team is here to help you get the most out of ReLeaf</p>
                    <a href="../../pages/MemberPages/mContactSupport.php" class="c-btn c-btn-primary cta-button">Contact Support</a>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($isAdmin): ?>
            <!-- Admin Panel -->
            <div class="admin-panel" id="adminPanel">
                <h2 id="panelTitle">Add New FAQ</h2>
                
                <div id="alertContainer"></div>
                
                <form id="faqForm" onsubmit="return false;">
                    <input type="hidden" id="faqID" value="">
                    
                    <div class="form-group">
                        <label for="categorySelect">Category <span class="required">*</span></label>
                        <select class="c-input c-input-select" id="categorySelect" required>
                            <option value="" disabled>-- Select Category --</option>
                            <option value="General">General</option>
                            <option value="Blog">Blog</option>
                            <option value="Event">Event</option>
                            <option value="Trade">Trade</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Security">Security</option>
                            <option value="Support">Support</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="questionInput">Question <span class="required">*</span> <small>(max 200 characters)</small></label>
                        <input type="text" id="questionInput" placeholder="Enter FAQ question" maxlength="200" required>
                        <small style="color: var(--Gray); font-size: 0.85rem; margin-top: 0.25rem; display: block;">
                            <span id="questionCount">0</span>/200 characters
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="answerInput">Answer <span class="required">*</span> <small>(max 500 characters)</small></label>
                        <textarea id="answerInput" placeholder="Enter FAQ answer" maxlength="500" required></textarea>
                        <small style="color: var(--Gray); font-size: 0.85rem; margin-top: 0.25rem; display: block;">
                            <span id="answerCount">0</span>/500 characters
                        </small>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn-primary" onclick="saveFAQ()">Save FAQ</button>
                        <button type="button" class="btn-secondary" onclick="resetForm()">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Admin FAB Button -->
            <button class="admin-fab" id="adminFab" onclick="toggleAdminPanel()">
                <img src="../../assets/images/edit-icon-light.svg" alt="edit-icon">
            </button>
            <?php endif; ?>
        </section>
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
                <a href="../../pages/MemberPages/mProfile.php">My Account</a><br>
                <a href="../../pages/MemberPages/mChat.html">My Chat</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.html">Contact</a><br>
                <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a><br>
                <a href="../../pages/MemberPages/mSetting.html">Settings</a>
            </div>
            <div>
                <b>Community</b><br>
                <a href="../../pages/CommonPages/mainEvent.php">Events</a><br>
                <a href="../../pages/CommonPages/mainBlog.php">Blogs</a><br>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            </div>
        </section>
    </footer>
    <?php endif; ?>

    <script>const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        // FAQ Dropdown
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Close all other items
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Toggle current item
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });

        <?php if ($isAdmin): ?>
        // Character counter functionality
        const questionInput = document.getElementById('questionInput');
        const answerInput = document.getElementById('answerInput');
        const questionCount = document.getElementById('questionCount');
        const answerCount = document.getElementById('answerCount');

        if (questionInput && answerInput) {
            questionInput.addEventListener('input', () => {
                questionCount.textContent = questionInput.value.length;
            });

            answerInput.addEventListener('input', () => {
                answerCount.textContent = answerInput.value.length;
            });
        }

        // Admin Panel Functions
        function toggleAdminPanel() {
            const container = document.getElementById('faqContainer');
            const panel = document.getElementById('adminPanel');
            const header = document.getElementById('faqHeader');
            const fab = document.getElementById('adminFab');
            
            if (!container || !panel) return;
            
            container.classList.toggle('admin-layout');
            panel.classList.toggle('active');
            
            // Update FAB text
            if (fab) {
                if (panel.classList.contains('active')) {
                    fab.innerHTML = '<img src="../../assets/images/icon-menu-close.svg" alt="close-icon">';
                } else {
                    fab.innerHTML = '<img src="../../assets/images/edit-icon-light.svg" alt="edit-icon">';
                }
            }
            
            // Hide header when in edit mode
            if (panel.classList.contains('active')) {
                if (header) header.style.display = 'none';
                // Scroll panel into view on mobile
                if (window.innerWidth <= 1024) {
                    setTimeout(() => {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            } else {
                if (header) header.style.display = 'block';
                resetForm();
            }
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            if (!alertContainer) return;
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 3000);
        }

        function resetForm() {
            const form = document.getElementById('faqForm');
            const panelTitle = document.getElementById('panelTitle');
            const alertContainer = document.getElementById('alertContainer');
            
            if (form) form.reset();
            if (document.getElementById('faqID')) document.getElementById('faqID').value = '';
            if (panelTitle) panelTitle.textContent = 'Add New FAQ';
            if (alertContainer) alertContainer.innerHTML = '';
            if (questionCount) questionCount.textContent = '0';
            if (answerCount) answerCount.textContent = '0';
        }

        async function saveFAQ() {
            const faqID = document.getElementById('faqID')?.value || '';
            const category = document.getElementById('categorySelect')?.value;
            const question = document.getElementById('questionInput')?.value;
            const answer = document.getElementById('answerInput')?.value;

            if (!category || !question || !answer) {
                showAlert('Please fill in all required fields', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', faqID ? 'update' : 'add');
            formData.append('category', category);
            formData.append('question', question);
            formData.append('answer', answer);
            if (faqID) formData.append('faqID', faqID);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            }
        }

        async function editFAQ(faqID) {
            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('faqID', faqID);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && result.faq) {
                    const faq = result.faq;
                    
                    // Show admin panel if not visible
                    const container = document.getElementById('faqContainer');
                    const panel = document.getElementById('adminPanel');
                    
                    if (container && panel && !panel.classList.contains('active')) {
                        container.classList.add('admin-layout');
                        panel.classList.add('active');
                        
                        // Update FAB
                        const fab = document.getElementById('adminFab');
                        if (fab) {
                            fab.innerHTML = '<img src="../../assets/images/close-icon-light.svg" alt="close-icon">';
                        }
                    }
                    
                    // Populate form
                    if (document.getElementById('faqID')) document.getElementById('faqID').value = faq.faqID;
                    if (document.getElementById('categorySelect')) document.getElementById('categorySelect').value = faq.category;
                    if (document.getElementById('questionInput')) document.getElementById('questionInput').value = faq.question;
                    if (document.getElementById('answerInput')) document.getElementById('answerInput').value = faq.answer;
                    if (document.getElementById('panelTitle')) document.getElementById('panelTitle').textContent = 'Edit FAQ';
                    
                    // Update character counters
                    if (questionCount) questionCount.textContent = faq.question.length;
                    if (answerCount) answerCount.textContent = faq.answer.length;
                    
                    // Scroll to panel
                    if (panel) {
                        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            } catch (error) {
                showAlert('Failed to load FAQ details', 'error');
            }
        }

        async function deleteFAQ(faqID) {
            if (!confirm('Are you sure you want to delete this FAQ? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('faqID', faqID);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>