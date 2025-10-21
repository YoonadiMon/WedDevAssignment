<?php
session_start();
include("../../php/dbConn.php");

// Check if user is logged in and is a member
// if (!isset($_SESSION['userID']) || $_SESSION['userType'] !== 'member') {
//     header("Location: ../../pages/CommonPages/login.php");
//     exit();
// }
// $userID = $_SESSION['userID'];

$userID = 5; // Temporary hardcoded userID for testing

// Fetch user info and points
$userQuery = "SELECT fullName, username, point FROM tblusers WHERE userID = ?";
$stmt = $connection->prepare($userQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$stmt->close();

// Fetch all quiz stages
$stagesQuery = "SELECT stageID, stageName, stageOrder, description, points FROM tblquiz_stages ORDER BY stageOrder";
$stagesResult = $connection->query($stagesQuery);
$stages = [];
while ($row = $stagesResult->fetch_assoc()) {
    $stages[] = $row;
}

// Fetch user progress for all stages
$progressQuery = "SELECT stageID, score, completed FROM tbluser_quiz_progress WHERE userID = ?";
$stmt = $connection->prepare($progressQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$progressResult = $stmt->get_result();
$userProgress = [];
while ($row = $progressResult->fetch_assoc()) {
    $userProgress[$row['stageID']] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Environmental Quiz - ReLeaf</title>
        <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
        <link rel="stylesheet" href="../../style/style.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
        <style>
            .quiz-dashboard {
                max-width: 1200px;
                margin: 2rem auto;
                padding: 0 1rem;
            }

            .user-info-card {
                background: var(--bg-color);
                padding: 2rem;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                margin-bottom: 2rem;
                text-align: center;
            }

            .user-points {
                font-size: 3rem;
                font-weight: 700;
                color: var(--MainGreen);
                margin: 1rem 0;
            }

            .points-label {
                font-size: 1rem;
                color: var(--text-color);
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .roadmap-container {
                background: var(--bg-color);
                padding: 2rem;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .roadmap-title {
                text-align: center;
                margin-bottom: 2rem;
                color: var(--text-heading);
            }

            .roadmap {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                position: relative;
            }

            .roadmap::before {
                content: '';
                position: absolute;
                left: 30px;
                top: 0;
                bottom: 0;
                width: 4px;
                background: var(--Gray);
                z-index: 1;
            }

            .roadmap-stage {
                display: flex;
                align-items: center;
                gap: 1.5rem;
                position: relative;
                z-index: 2;
            }

            .stage-indicator {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 1.2rem;
                flex-shrink: 0;
                background: var(--Gray);
                color: var(--White);
                border: 4px solid var(--bg-color);
                transition: all 0.3s ease;
            }

            .stage-indicator.completed {
                background: var(--MainGreen);
            }

            .stage-indicator.available {
                background: var(--btn-color);
                cursor: pointer;
            }

            .stage-indicator.locked {
                background: var(--Gray);
                cursor: not-allowed;
            }

            .stage-content {
                flex: 1;
                background: var(--sec-bg-color);
                padding: 1.5rem;
                border-radius: 12px;
                transition: all 0.3s ease;
            }

            .stage-content.available {
                cursor: pointer;
            }

            .stage-content.available:hover {
                transform: translateX(8px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .stage-header {
                display: flex;
                justify-content: between;
                align-items: center;
                margin-bottom: 0.5rem;
            }

            .stage-name {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--text-heading);
                margin: 0;
            }

            .stage-points {
                background: var(--MainGreen);
                color: var(--White);
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.875rem;
                font-weight: 600;
            }

            .stage-description {
                color: var(--text-color);
                margin-bottom: 1rem;
                line-height: 1.5;
            }

            .stage-progress {
                display: flex;
                align-items: center;
                gap: 1rem;
                font-size: 0.875rem;
                color: var(--text-color);
            }

            .progress-bar {
                flex: 1;
                height: 8px;
                background: var(--Gray);
                border-radius: 4px;
                overflow: hidden;
            }

            .progress-fill {
                height: 100%;
                background: var(--MainGreen);
                transition: width 0.3s ease;
            }

            .stage-status {
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.75rem;
                letter-spacing: 0.5px;
            }

            .status-completed {
                color: var(--MainGreen);
            }

            .status-available {
                color: var(--btn-color);
            }

            .status-locked {
                color: var(--Gray);
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .quiz-dashboard {
                    margin: 1rem auto;
                    padding: 0 0.5rem;
                }

                .user-info-card {
                    padding: 1.5rem;
                }

                .user-points {
                    font-size: 2.5rem;
                }

                .roadmap-container {
                    padding: 1.5rem;
                }

                .roadmap::before {
                    left: 25px;
                }

                .roadmap-stage {
                    gap: 1rem;
                }

                .stage-indicator {
                    width: 50px;
                    height: 50px;
                    font-size: 1rem;
                }

                .stage-content {
                    padding: 1rem;
                }

                .stage-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.5rem;
                }

                .stage-name {
                    font-size: 1.1rem;
                }
            }

            @media (max-width: 480px) {
                .roadmap::before {
                    left: 20px;
                }

                .stage-indicator {
                    width: 40px;
                    height: 40px;
                    font-size: 0.9rem;
                }

                .stage-content {
                    padding: 0.75rem;
                }

                .stage-progress {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.5rem;
                }

                .progress-bar {
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

                            <a href="../../pages/MemberPages/mSetting.html">
                                <img src="../../assets/images/setting-light.svg" alt="Settings">
                            </a>
                        </section>

                        <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                        <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                        <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                        <a href="../../pages/CommonPages/mainTrade.php" class="active">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.html">About</a>
                    </div>
                </div>

            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.html">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php" class="active">Trade</a>
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
            <div class="quiz-dashboard">
                <!-- User Info Card -->
                <div class="user-info-card">
                    <h1 class="c-heading-2">Welcome, <?php echo htmlspecialchars($userData['fullName']); ?>!</h1>
                    <div class="user-points"><?php echo number_format($userData['point']); ?></div>
                    <div class="points-label">Eco Points</div>
                    <p>Complete quizzes to earn more points and track your sustainability journey!</p>
                </div>

                <!-- Quiz Roadmap -->
                <div class="roadmap-container">
                    <h2 class="roadmap-title c-heading-2">Sustainability Learning Path</h2>
                    <div class="roadmap">
                        <?php foreach ($stages as $index => $stage): 
                            $stageProgress = isset($userProgress[$stage['stageID']]) ? $userProgress[$stage['stageID']] : null;
                            $isCompleted = $stageProgress && $stageProgress['completed'];
                            $isAvailable = $index === 0 || isset($userProgress[$stages[$index-1]['stageID']]);
                            $score = $stageProgress ? $stageProgress['score'] : 0;
                            $maxScore = 5; // Assuming 5 questions per stage
                            $percentage = $score > 0 ? ($score / $maxScore) * 100 : 0;
                        ?>
                        <div class="roadmap-stage">
                            <div class="stage-indicator <?php echo $isCompleted ? 'completed' : ($isAvailable ? 'available' : 'locked'); ?>"
                                 <?php if ($isAvailable): ?>onclick="startStage(<?php echo $stage['stageID']; ?>)"<?php endif; ?>>
                                <?php echo $stage['stageOrder']; ?>
                            </div>
                            <div class="stage-content <?php echo $isAvailable ? 'available' : ''; ?>"
                                 <?php if ($isAvailable): ?>onclick="startStage(<?php echo $stage['stageID']; ?>)"<?php endif; ?>>
                                <div class="stage-header">
                                    <h3 class="stage-name"><?php echo htmlspecialchars($stage['stageName']); ?></h3>
                                    <div class="stage-points">+<?php echo $stage['points']; ?> points</div>
                                </div>
                                <p class="stage-description"><?php echo htmlspecialchars($stage['description']); ?></p>
                                <div class="stage-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <div class="stage-status <?php echo $isCompleted ? 'status-completed' : ($isAvailable ? 'status-available' : 'status-locked'); ?>">
                                        <?php if ($isCompleted): ?>
                                            Completed (<?php echo $score; ?>/<?php echo $maxScore; ?>)
                                        <?php elseif ($isAvailable): ?>
                                            Available
                                        <?php else: ?>
                                            Locked
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>

        <hr>
        
        <!-- Footer -->
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
                    <a href="../../pages/MemberPages/mChat.php">My Chat</a><br>
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
            const isAdmin = false;
            
            function startStage(stageID) {
                // Redirect to activeQuiz.php with the selected stage
                window.location.href = `activeQuiz.php?stage=${stageID}`;
            }

            // Add hover effects
            document.addEventListener('DOMContentLoaded', function() {
                const availableStages = document.querySelectorAll('.stage-content.available, .stage-indicator.available');
                
                availableStages.forEach(stage => {
                    stage.addEventListener('mouseenter', function() {
                        this.style.cursor = 'pointer';
                    });
                    
                    stage.addEventListener('click', function() {
                        const stageID = this.getAttribute('onclick').match(/\d+/)[0];
                        startStage(stageID);
                    });
                });
            });
        </script>
        <script src="../../javascript/mainScript.js"></script>
    </body>
</html>