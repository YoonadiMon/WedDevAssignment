<?php
    session_start();
    include("../../php/dbConn.php");
    include("../../php/sessionCheck.php");

    $userID = $_SESSION['userID'];
    $stageID = isset($_GET['stage']) ? intval($_GET['stage']) : 1;

    // get quiz questions for stage 1
    $quizQuestions = [];
    $questionQuery = "SELECT q.quizID, q.questionText, q.optionA, q.optionB, q.optionC, q.optionD, q.correctAnswer, q.points 
                    FROM tblquiz_questions q 
                    WHERE q.stageID = '$stageID'
                    ORDER BY q.questionOrder";
    $result = mysqli_query($connection, $questionQuery);
    if (!$result) { // error handling
        die("Query failed: " . mysqli_error($connection));
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $quizQuestions[] = $row;
    }

    // check if user has already completed this stage
    $progressQuery = "SELECT * FROM tbluser_quiz_progress WHERE userID = '$userID' AND stageID = '$stageID'";
    $progressResult = mysqli_query($connection, $progressQuery);
    if (!$progressResult) { // error handling
        die("Query failed: " . mysqli_error($connection));
    }
    $hasCompleted = mysqli_num_rows($progressResult) > 0;
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
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet">
        <style>
            .quiz-container {
                max-width: 800px;
                margin: 2rem auto;
                padding: 2rem;
                background: var(--bg-color);
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .quiz-header {
                text-align: center;
                margin-bottom: 2rem;
            }

            .quiz-progress {
                width: 100%;
                height: 8px;
                background: var(--Gray);
                border-radius: 4px;
                margin: 1rem 0;
                overflow: hidden;
            }

            .quiz-progress-bar {
                height: 100%;
                background: linear-gradient(90deg, var(--MainGreen), var(--btn-color-hover));
                transition: width 0.3s ease;
            }

            .quiz-question {
                margin: 2rem 0;
            }

            .quiz-question h3 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
                color: var(--text-heading);
            }

            .quiz-options {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .quiz-option {
                padding: 1.25rem;
                background: var(--sec-bg-color);
                border: 2px solid transparent;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 1rem;
            }

            .quiz-option:hover {
                border-color: var(--MainGreen);
                transform: translateX(8px);
            }

            .quiz-option.selected {
                background: var(--MainGreen);
                color: var(--White);
                border-color: var(--MainGreen);
            }

            .quiz-option.correct {
                background: var(--MainGreen);
                color: var(--White);
                border-color: var(--MainGreen);
            }

            .quiz-option.incorrect {
                background: var(--Red);
                color: var(--White);
                border-color: var(--Red);
            }

            .quiz-option.disabled {
                cursor: not-allowed;
                opacity: 0.6;
            }

            .quiz-controls {
                display: flex;
                justify-content: center;
                margin-top: 2rem;
                gap: 1rem;
            }

            .quiz-controls-next {
                display: flex;
                justify-content: space-between;
                margin-top: 2rem;
                gap: 1rem;
            }

            .quiz-start, .quiz-results {
                text-align: center;
            }

            .quiz-start h2, .quiz-results h2 {
                font-size: 2rem;
                margin-bottom: 1rem;
                color: var(--text-heading);
            }

            .quiz-start p, .quiz-results p {
                font-size: 1.1rem;
                margin-bottom: 2rem;
                color: var(--text-color);
                line-height: 1.6;
            }

            /* Popup Modal Styles */
            .quiz-popup {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }

            .quiz-popup-content {
                background: var(--bg-color);
                padding: 2rem;
                border-radius: 16px;
                max-width: 600px;
                width: 90%;
                text-align: center;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                animation: popupSlide 0.3s ease-out;
                max-height: 90vh;
                overflow-y: scroll;
            }

            @keyframes popupSlide {
                from {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .quiz-popup-stats {
                display: flex;
                justify-content: center;
                gap: 2rem;
                margin: 2rem 0;
                flex-wrap: wrap;
            }

            .quiz-popup-stat {
                padding: 1.5rem;
                background: var(--sec-bg-color);
                border-radius: 12px;
                min-width: 120px;
            }

            .quiz-popup-value {
                font-size: 2rem;
                font-weight: 700;
                color: var(--MainGreen);
                display: block;
            }

            .quiz-popup-label {
                font-size: 0.9rem;
                color: var(--text-color);
                margin-top: 0.5rem;
            }

            .quiz-popup-message {
                font-size: 1.3rem;
                font-weight: 600;
                margin: 1.5rem 0;
                padding: 1rem;
                background: var(--LightGreen);
                border-radius: 8px;
                color: var(--text-heading);
            }

            .hidden {
                display: none;
            }

            .quiz-feedback {
                margin-top: 1rem;
                padding: 1rem;
                background: var(--sec-bg-color);
                border-radius: 8px;
                font-size: 0.95rem;
                color: var(--text-color);
            }

            @media (max-width: 760px) {
                .quiz-container {
                    padding: 1.5rem;
                    margin: 1rem;
                }

                .quiz-question h3 {
                    font-size: 1.25rem;
                }

                .quiz-controls {
                    flex-direction: column;
                }

                .quiz-popup-stats {
                    flex-direction: column;
                    gap: 1rem;
                }

                .quiz-popup-stat {
                    min-width: 100%;
                }

                .quiz-popup-content {
                    padding: 2rem 1.5rem;
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
                                <a href="../../pages/MemberPages/mChat.php">
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
                        <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                        <a href="../../pages/CommonPages/aboutUs.php">About</a>
                    </div>
                </div>
            </nav>

            <!-- Menu Links Desktop + Tablet -->
            <nav class="c-navbar-desktop">
                <a href="../../pages/MemberPages/memberIndex.php">Home</a>
                <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                <a href="../../pages/CommonPages/aboutUs.php">About</a>
            </nav>
            <section class="c-navbar-more">
                <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
                
                <button id="themeToggle2">
                    <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
                </button>
                <a href="../../pages/MemberPages/mChat.php" class="c-chatbox" id="chatboxDesktop">
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
            <div class="quiz-container">
                <!-- Start Screen -->
                <div id="startScreen" class="quiz-start">
                    <h2 class="c-heading-2">Recycling Champion Quiz</h2>
                    <p>Test your knowledge about recycling and waste management! This quiz contains <?php echo count($quizQuestions); ?> questions about proper recycling practices.</p>
                    <?php if ($hasCompleted): ?>
                        <div style="background: var(--LightGreen); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <strong>You've already completed this stage!</strong> You can retake it to improve your score.
                        </div>
                    <?php endif; ?>
                    <button class="c-btn c-btn-primary c-btn-big" onclick="startQuiz()">Start Quiz</button>
                </div>

                <!-- Quiz Screen -->
                <div id="quizScreen" class="hidden">
                    <div class="quiz-header">
                        <div class="c-text-label">Question <span id="currentQuestion">1</span> of <span id="totalQuestions"><?php echo count($quizQuestions); ?></span></div>
                        <div class="quiz-progress">
                            <div id="progressBar" class="quiz-progress-bar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="quiz-question">
                        <h3 id="questionText"></h3>
                        <div id="optionsContainer" class="quiz-options"></div>
                        <div id="feedback" class="quiz-feedback hidden"></div>
                    </div>

                    <div class="quiz-controls-next">
                        <button class="c-btn c-btn-secondary" onclick="previousQuestion()" id="prevBtn" disabled>Previous</button>
                        <button class="c-btn c-btn-primary" onclick="nextQuestion()" id="nextBtn" disabled>Next</button>
                    </div>
                </div>
            </div>

            <!-- Results Popup -->
            <div id="resultsPopup" class="quiz-popup">
                <div class="quiz-popup-content">
                    <h2 class="c-heading-2">Quiz Complete!</h2>
                    <div class="quiz-popup-message" id="popupMessage"></div>
                    
                    <div class="quiz-popup-stats">
                        <div class="quiz-popup-stat">
                            <span class="quiz-popup-value" id="popupScore">0</span>
                            <div class="quiz-popup-label">Score</div>
                        </div>
                        <div class="quiz-popup-stat">
                            <span class="quiz-popup-value" id="popupCorrect">0</span>
                            <div class="quiz-popup-label">Correct</div>
                        </div>
                        <div class="quiz-popup-stat">
                            <span class="quiz-popup-value" id="popupPercentage">0%</span>
                            <div class="quiz-popup-label">Percentage</div>
                        </div>
                    </div>

                    <p>Your progress has been saved! Redirecting to quiz page...</p>
                    
                    <div class="quiz-controls">
                        <button class="c-btn c-btn-primary c-btn-big" onclick="redirectToQuizPage()">Continue</button>
                    </div>
                </div>
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

        <script>
            const isAdmin = false;
            const quizData = <?php echo json_encode($quizQuestions); ?>;
            const userID = <?php echo $userID; ?>;
            const stageID = <?php echo $stageID; ?>;

            let currentQuestionIndex = 0;
            let selectedAnswers = [];
            let score = 0;

            function startQuiz() {
                document.getElementById('startScreen').classList.add('hidden');
                document.getElementById('quizScreen').classList.remove('hidden');
                currentQuestionIndex = 0;
                selectedAnswers = new Array(quizData.length).fill(null);
                score = 0;
                loadQuestion();
            }

            function loadQuestion() {
                const question = quizData[currentQuestionIndex];
                document.getElementById('questionText').textContent = question.questionText;
                document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;
                document.getElementById('totalQuestions').textContent = quizData.length;
                
                const progress = ((currentQuestionIndex + 1) / quizData.length) * 100;
                document.getElementById('progressBar').style.width = progress + '%';

                const optionsContainer = document.getElementById('optionsContainer');
                optionsContainer.innerHTML = '';
                
                const options = [
                    question.optionA,
                    question.optionB, 
                    question.optionC,
                    question.optionD
                ];

                options.forEach((option, index) => {
                    if (!option) return; // Skip empty options
                    
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'quiz-option';
                    optionDiv.textContent = option;
                    
                    if (selectedAnswers[currentQuestionIndex] === index) {
                        optionDiv.classList.add('selected');
                    }
                    
                    optionDiv.onclick = () => selectOption(index);
                    optionsContainer.appendChild(optionDiv);
                });

                document.getElementById('feedback').classList.add('hidden');
                document.getElementById('prevBtn').disabled = currentQuestionIndex === 0;
                document.getElementById('nextBtn').disabled = selectedAnswers[currentQuestionIndex] === null;
                
                if (currentQuestionIndex === quizData.length - 1) {
                    document.getElementById('nextBtn').textContent = 'Finish';
                } else {
                    document.getElementById('nextBtn').textContent = 'Next';
                }
            }

            function selectOption(index) {
                selectedAnswers[currentQuestionIndex] = index;
                const options = document.querySelectorAll('.quiz-option');
                options.forEach((opt, i) => {
                    opt.classList.remove('selected');
                    if (i === index) {
                        opt.classList.add('selected');
                    }
                });
                
                document.getElementById('nextBtn').disabled = false;
            }

            function nextQuestion() {
                if (selectedAnswers[currentQuestionIndex] === null) {
                    return;
                }
                
                if (currentQuestionIndex < quizData.length - 1) {
                    currentQuestionIndex++;
                    loadQuestion();
                } else {
                    showResults();
                }
            }

            function previousQuestion() {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    loadQuestion();
                }
            }

            function showResults() {
                score = 0;
                let totalPoints = 0;
                
                selectedAnswers.forEach((answer, index) => {
                    const correctAnswer = quizData[index].correctAnswer.toLowerCase();
                    const userAnswer = String.fromCharCode(97 + answer); // Convert 0,1,2,3 to 'a','b','c','d'
                    
                    if (userAnswer === correctAnswer) {
                        score++;
                        totalPoints += parseInt(quizData[index].points) || 1;
                    }
                });

                const percentage = Math.round((score / quizData.length) * 100);
                
                // Update popup content
                document.getElementById('popupScore').textContent = score + '/' + quizData.length;
                document.getElementById('popupCorrect').textContent = score;
                document.getElementById('popupPercentage').textContent = percentage + '%';
                
                let message = '';
                if (percentage === 100) {
                    message = "Perfect! You're a Recycling Champion! 🌟";
                } else if (percentage >= 80) {
                    message = "Excellent work! Great recycling knowledge! 🌱";
                } else if (percentage >= 60) {
                    message = "Good job! Keep learning about recycling! ♻️";
                } else if (percentage >= 40) {
                    message = "Not bad! There's room to learn more! 🌿";
                } else {
                    message = "Keep exploring! Every step counts! 🌳";
                }
                
                document.getElementById('popupMessage').textContent = message;
                
                // Save results to database
                saveQuizResults(score, totalPoints, percentage);
                
                // Show popup
                document.getElementById('resultsPopup').style.display = 'flex';
            }

            function saveQuizResults(score, totalPoints, percentage) {
                const formData = new FormData();
                formData.append('userID', userID);
                formData.append('stageID', stageID);
                formData.append('score', score);
                formData.append('totalPoints', totalPoints);
                formData.append('percentage', percentage);

                // Use the correct path to saveQuizResults.php
                fetch('../../php/saveQuizResults.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Quiz results saved successfully');
                        console.log('Points added:', totalPoints);
                    } else {
                        console.error('Failed to save quiz results:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error saving quiz results:', error);
                });
            }

            function redirectToQuizPage() {
                window.location.href = '../../pages/MemberPages/mQuiz.php';
            }
        </script>
        <script src="../../javascript/mainScript.js"></script>
        <?php
            // last step - close the connection
            mysqli_close($connection);
        ?>
    </body>
</html>