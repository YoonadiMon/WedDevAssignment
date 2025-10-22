<?php
function showErrorPopup($message, $redirectUrl = '') {
    // If no redirect URL provided, default to appropriate index page
    if (empty($redirectUrl)) {
        $isAdmin = isset($_SESSION['userType']) && $_SESSION['userType'] === 'admin';
        $redirectUrl = $isAdmin ? '../../pages/AdminPages/adminIndex.php' : '../../pages/MemberPages/memberIndex.php';
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ReLeaf - Error</title>
        <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
        <link rel="stylesheet" href="../../style/style.css">
        <style>
            .error-popup-overlay {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.85);
                z-index: 10000;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            .error-popup {
                background: var(--bg-color);
                border-radius: 16px;
                padding: 2.5rem;
                max-width: 450px;
                width: 90%;
                text-align: center;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.4s ease;
                border: 2px solid var(--Red);
            }

            .dark-mode .error-popup {
                border-color: var(--Red);
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .error-popup-icon {
                width: 70px;
                height: 70px;
                margin: 0 auto 1.25rem;
            }

            .error-popup-icon img {
                width: 100%;
                height: 100%;
            }

            .error-popup-title {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--Red);
                margin-bottom: 1rem;
            }

            .error-popup-message {
                font-size: 1.05rem;
                color: var(--text-color);
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .error-popup-btn {
                padding: 0.875rem 2.5rem;
                background: var(--Red);
                color: var(--White);
                border: none;
                border-radius: 10px;
                cursor: pointer;
                font-weight: 600;
                font-size: 1rem;
                transition: all 0.3s ease;
            }

            .error-popup-btn:hover {
                opacity: 0.8;
                transform: translateY(-2px);
            }

            .error-countdown {
                font-size: 0.9rem;
                color: var(--DarkerGray);
                margin-top: 1rem;
            }

            .dark-mode .error-countdown {
                color: var(--Gray);
            }

            @media (max-width: 480px) {
                .error-popup {
                    padding: 2rem 1.5rem;
                }
                .error-popup-title {
                    font-size: 1.5rem;
                }
                .error-popup-icon {
                    width: 60px;
                    height: 60px;
                }
            }
        </style>
    </head>
    <body>
        <div class="error-popup-overlay">
            <div class="error-popup">
                <div class="error-popup-icon">
                    <img src="../../assets/images/banned-icon-red.svg" alt="Error">
                </div>
                <h2 class="error-popup-title">Error</h2>
                <p class="error-popup-message"><?php echo htmlspecialchars($message); ?></p>
                <button class="error-popup-btn" onclick="redirectBack()">OK</button>
                <p class="error-countdown">Redirecting in <span id="countdown">3</span> seconds</p>
            </div>
        </div>
        <script>
            const redirectUrl = <?php echo json_encode($redirectUrl); ?>;
            let timeLeft = 3;
            const countdownEl = document.getElementById('countdown');
            
            function redirectBack() {
                window.location.href = redirectUrl;
            }
            
            const timer = setInterval(() => {
                timeLeft--;
                if (countdownEl) countdownEl.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    redirectBack();
                }
            }, 1000);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    redirectBack();
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>