<?php
    include("php/dbConn.php");
    
    // sql data for active members 
    $activeMembersQuery = "SELECT COUNT(*) as count FROM tblusers";
    $result = mysqli_query($connection, $activeMembersQuery);
    if (!$result) { die("Query failed: " . mysqli_error($connection)); } // error handling
    $row = mysqli_fetch_assoc($result); // step 4 - process the result
    $activeMembers = $row['count'];
    
    // sql data for events
    $communityEventsQuery = "SELECT COUNT(*) as count FROM tblevents";
    $result = mysqli_query($connection, $communityEventsQuery);
    if (!$result) { die("Query failed: " . mysqli_error($connection)); } // error handling
    $row = mysqli_fetch_assoc($result); // step 4 - process the result
    $communityEvents = $row['count'];
    
    // sql data for products swapped 
    $productsSwappedQuery = "SELECT SUM(tradesCompleted) as count FROM tblusers";
    $result = mysqli_query($connection, $productsSwappedQuery);
    if (!$result) { die("Query failed: " . mysqli_error($connection)); } // error handling
    $row = mysqli_fetch_assoc($result); // step 4 - process the result
    $productsSwapped = $row['count'] ?: 0; // Handle if its null
    
    // sql data for total quizzes
    $quizzesQuery = "SELECT COUNT(*) as count FROM tblquiz_stages";
    $result = mysqli_query($connection, $quizzesQuery);
    if (!$result) { die("Query failed: " . mysqli_error($connection)); } // error handling
    $row = mysqli_fetch_assoc($result); // step 4 - process the result
    $quizzes = $row['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - Sustainable Community Platform</title>
    <link rel="icon" type="image/png" href="assets/images/Logo.png">
    
    <link rel="stylesheet" href="style/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <style>
        /* Additional styling unique to page */
        body {
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .landing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .landing-container-first {
            max-width: 50vw;
            margin: 0 3rem 0 auto;
        }

        .hero-section img {
            width: 40%;
            max-width: 350px;
            margin: 0 auto 0 0;
            transform: translateY(20px);
            opacity: 0;
            animation: floatIn 1s ease-out 0.5s forwards;
        }
        
        /* Hero Section Styling */
        .hero-section {
            padding: 2.5rem 0;
            text-align: center;
            background: linear-gradient(135deg, var(--MainGreen) 0%, var(--LightGreen) 100%);
            color: var(--White);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: row;
            align-items: center;
            min-height: 100vh;
        }
        
        .hero-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.1"><path fill="white" d="M500,250c138,0,250,112,250,250S638,750,500,750S250,638,250,500S362,250,500,250z M500,200c-165,0-300,135-300,300 s135,300,300,300s300-135,300-300S665,200,500,200L500,200z"/></svg>');
            background-size: 300px;
            opacity: 0.1;
        }
        
        .hero-section h1 {
            margin-bottom: 1.5rem;
            font-size: 3.5rem;
            font-weight: 800;
            position: relative;
            color: var(--White);
            transform: translateY(30px);
            opacity: 0;
            animation: slideIn 1s ease-out 0.2s forwards;
        }
        
        .hero-section .c-text-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            color: var(--White);
            transform: translateY(30px);
            opacity: 0;
            animation: slideIn 1s ease-out 0.4s forwards;
        }
        
        .hero-section .c-text-body {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto 3rem;
            line-height: 1.7;
            color: var(--White);
            transform: translateY(30px);
            opacity: 0;
            animation: slideIn 1s ease-out 0.6s forwards;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            transform: translateY(30px);
            opacity: 0;
            animation: slideIn 1s ease-out 0.8s forwards;
        }
        
        /* Content Section Styling */
        .content-section {
            padding: 5rem 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease;
        }
        
        .content-section.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .content-section:nth-child(even) {
            background-color: var(--sec-bg-color);
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .content-section.visible .section-header {
            opacity: 1;
            transform: translateY(0);
        }

        .section-header > * {
            margin-bottom: 2rem;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            color: var(--text-color);
            position: relative;
            display: inline-block;
        }
        
        .section-header h2::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 70%;
            height: 4px;
            background: var(--MainGreen);
            border-radius: 2px;
            margin-top: 1rem;
        }
        
        .section-header p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            color: var(--text-color);
        }
        
        /* Features Grid Styling */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background-color: var(--bg-color);
            padding: 2.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
            transform: translateY(30px);
            opacity: 0;
        }
        
        .content-section.visible .feature-card {
            opacity: 1;
            transform: translateY(0);
        }
        
        .feature-card:nth-child(1) {
            transition-delay: 0.2s;
        }
        
        .feature-card:nth-child(2) {
            transition-delay: 0.4s;
        }
        
        .feature-card:nth-child(3) {
            transition-delay: 0.6s;
        }
        
        .feature-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--MainGreen);
        }
        
        .feature-card:hover {
            transform: translateY(-10px) !important;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .feature-card h3 {
            margin-bottom: 1.5rem;
            color: var(--text-heading);
            font-size: 1.5rem;
            position: relative;
            padding-left: 2.5rem;
        }
        
        .feature-card h3::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--MainGreen);
            font-weight: bold;
        }
        
        .feature-card p {
            line-height: 1.7;
            color: var(--text-color);
        }
        
        .test-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
            background-color: var(--MainGreen);
            color: var(--White);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stats-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
            z-index: 1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .stat-item {
            padding: 1.5rem;
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.8s ease;
        }
        
        .stats-section.visible .stat-item {
            transform: scale(1);
            opacity: 1;
        }
        
        .stat-item:nth-child(1) {
            transition-delay: 0.1s;
        }
        
        .stat-item:nth-child(2) {
            transition-delay: 0.3s;
        }
        
        .stat-item:nth-child(3) {
            transition-delay: 0.5s;
        }
        
        .stat-item:nth-child(4) {
            transition-delay: 0.7s;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--White);
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            color: var(--White);
        }
        
        /* CTA Section */
        .cta-section {
            padding: 5rem 0;
            text-align: center;
            background-color: var(--bg-color);
            opacity: 0;
            transform: translateY(30px);
            transition: all 1s ease;
        }
        
        .cta-section.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .cta-content {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--text-heading);
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            line-height: 1.7;
            color: var(--text-color);
        }
        
        /* Animation Keyframes */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Ensure all text uses the correct variables */
        .landing-page * {
            color: var(--text-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 880px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section .c-text-subtitle {
                font-size: 1.2rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .feature-card {
                padding: 2rem 1.5rem;
            }

            .hero-section {
                flex-direction: column;
            }

            .landing-container-first {
                max-width: 60vw;
                margin: 0 auto;
            }
            
            .hero-section img {
                width: 50%;
                max-width: 80vw;
                margin: 0 auto;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="landing-container-first">
            <h1 class="c-heading-1">Welcome to ReLeaf</h1>
            <p class="c-text-subtitle">Combining comfort and nature to foster sustainable living and community support</p>
            <p class="c-text-body">"ReLeaf" symbolizes comfort, support, and ease, while also being closely associated with nature, growth, and renewal. Join us in creating a greener, healthier world together.</p>
            <div class="hero-buttons">
                <a href="pages/CommonPages/signUpPage.php" class="c-btn c-btn-primary c-btn-big">Join Our Community</a>
                <a href="#about" class="c-btn c-btn-secondary c-btn-big">Learn More</a>
            </div>
        </div>
        <img src="assets/images/earthImg.png" alt="Earth Image">
    </section>
    
    <!-- Stats Section -->
    <section class="stats-section" id="stats">
        <div class="landing-container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-count="<?php echo $activeMembers; ?>">0</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="<?php echo $communityEvents; ?>">0</div>
                    <div class="stat-label">Community Events</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="<?php echo $productsSwapped; ?>">0</div>
                    <div class="stat-label">Products Swapped</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="<?php echo $quizzes * 5; ?>">0</div>
                    <div class="stat-label">Quizzes</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="content-section" id="about">
        <div class="landing-container">
            <div class="section-header">
                <h2 class="c-heading-2">About ReLeaf</h2>
                <p class="c-text-body">The ReLeaf web application is designed with three key objectives to promote sustainable living and community engagement.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <h3 class="c-heading-4">Encourage Sustainable Living</h3>
                    <p class="c-text-body">ReLeaf serves as a central hub of knowledge for the community, providing detailed information about local recycling schemes, energy-saving tips, and community gardening initiatives.</p>
                </div>
                
                <div class="feature-card">
                    <h3 class="c-heading-4">Foster Community Engagement</h3>
                    <p class="c-text-body">Create interactive features like product swaps, community blogs, and environmental challenges to build an active community focused on sustainability.</p>
                </div>
                
                <div class="feature-card">
                    <h3 class="c-heading-4">Provide a Reliable Platform</h3>
                    <p class="c-text-body">Ensure a safe, accessible, and user-friendly platform with effective management tools and responsive design for all community members.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Objectives Section -->
    <section class="content-section">
        <div class="landing-container">
            <div class="section-header">
                <h2 class="c-heading-2">Our Objectives</h2>
                <p class="c-text-body">ReLeaf is designed with three key objectives to promote sustainable living and community engagement</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <h3 class="c-heading-5">Sustainable Living Hub</h3>
                    <p class="c-text-body">Provide comprehensive resources on recycling schemes, energy-saving practices, and community gardening to guide the community in environmentally friendly living.</p>
                </div>
                
                <div class="feature-card">
                    <h3 class="c-heading-5">Active Community Engagement</h3>
                    <p class="c-text-body">Foster environmental awareness through interactive features that allow users to collaborate, exchange products, share stories, and participate in community events.</p>
                </div>
                
                <div class="feature-card">
                    <h3 class="c-heading-5">Reliable & Safe Platform</h3>
                    <p class="c-text-body">ReLeaf provides a secure and accessible platform with user-friendly interfaces, protective measures, and responsive design for community members of all ages and abilities.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="landing-container">
            <div class="cta-content">
                <h2 class="c-heading-2">Ready to Make a Difference?</h2>
                <p class="c-text-body">Join our growing community of environmentally conscious individuals working together to create a more sustainable future.</p>
                <a href="pages/CommonPages/signUpPage.php" class="c-btn c-btn-primary c-btn-big">Sign Up Today</a>
            </div>
        </div>
    </section>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // uncomment this to add click event listeners to every link in page for debugging purpose
            // document.querySelectorAll('a').forEach(link => {
            //     link.addEventListener('click', function(e) {
            //         console.log('Navigating to:', this.getAttribute('href'));
            //     });
            // });
            
            // data value for scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        
                        // If user is looking at stats section, starts the number animation
                        if (entry.target.id === 'stats') {
                            animateNumbers();
                        }
                    }
                });
            }, observerOptions);
            
            // check all sections to see whether user is viewing that sec
            document.querySelectorAll('.content-section, .stats-section, .cta-section').forEach(section => {
                observer.observe(section);
            });
            
            // Function to animate the numbers in a counting motion
            function animateNumbers() {
                const statNumbers = document.querySelectorAll('.stat-number');
                
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-count'));
                    const duration = 2000; // the total time for animation in sec
                    const step = target / (duration / 16); // formula to calculate fps, 60fps
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        stat.textContent = Math.floor(current) + (target > 100 ? '+' : '');
                    }, 16);
                });
            }
        });
    </script>
</body>
</html>