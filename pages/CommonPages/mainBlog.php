<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Fetch all blogs with author info
$query = "
    SELECT b.blogID, b.userID, b.title, b.excerpt, b.category, b.date, u.fullName
    FROM tblblog b
    JOIN tblusers u ON b.userID = u.userID
    ORDER BY b.date DESC
";

$result = mysqli_query($connection, $query);
$blogs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $blogs[] = $row;
}

// Fetch all tags for filtering
$tagsQuery = "SELECT DISTINCT tagID, tagName FROM tbltag ORDER BY tagName";
$tagsResult = mysqli_query($connection, $tagsQuery);
$tags = [];

while ($row = mysqli_fetch_assoc($tagsResult)) {
    $tags[] = $row;
}

// Return JSON for AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'getBlogs') {
        echo json_encode(['blogs' => $blogs, 'count' => count($blogs)]);
    } elseif ($_POST['action'] === 'getTags') {
        echo json_encode(['tags' => $tags]);
    } elseif ($_POST['action'] === 'searchBlogs') {
        $searchQuery = isset($_POST['query']) ? strtolower($_POST['query']) : '';
        $filtered = array_filter($blogs, function($blog) use ($searchQuery) {
            return stripos($blog['title'], $searchQuery) !== false || 
                   stripos($blog['excerpt'], $searchQuery) !== false;
        });
        echo json_encode(['blogs' => array_values($filtered), 'count' => count($filtered)]);
    } elseif ($_POST['action'] === 'filterBlogs') {
        $filterType = isset($_POST['type']) ? $_POST['type'] : 'all';
        $filtered = $blogs;
        
        if ($filterType === 'recent') {
            $threeDaysAgo = strtotime('-3 days');
            $filtered = array_filter($blogs, function($blog) use ($threeDaysAgo) {
                return strtotime($blog['date']) >= $threeDaysAgo;
            });
        }
        
        echo json_encode(['blogs' => array_values($filtered), 'count' => count($filtered)]);
    } elseif ($_POST['action'] === 'filterByTag') {
        $tagName = isset($_POST['tag']) ? $_POST['tag'] : 'all';
        
        if ($tagName === 'all') {
            $filtered = $blogs;
        } else {
            $tagQuery = "
                SELECT b.blogID
                FROM tblblog b
                JOIN tblblogtag bt ON b.blogID = bt.blogID
                JOIN tbltag t ON bt.tagID = t.tagID
                WHERE t.tagName = '" . mysqli_real_escape_string($connection, $tagName) . "'
            ";
            $tagResult = mysqli_query($connection, $tagQuery);
            $taggedBlogIds = [];
            
            while ($row = mysqli_fetch_assoc($tagResult)) {
                $taggedBlogIds[] = $row['blogID'];
            }
            
            $filtered = array_filter($blogs, function($blog) use ($taggedBlogIds) {
                return in_array($blog['blogID'], $taggedBlogIds);
            });
        }
        
        echo json_encode(['blogs' => array_values($filtered), 'count' => count($filtered)]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - Blog</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <style>
        /* Hero Section */
        .hero {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--bg-color);
            border-bottom: 1px solid var(--border-color);
        }

        .hero h5 {
            color: var(--text-color-2);
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .hero p {
            max-width: 600px;
            margin: 0 auto 2rem;
            color: var(--text-color-2);
            font-size: 1rem;
            line-height: 1.6;
        }

        .btn {
            background-color: var(--MainGreen);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .btn:hover {
            background-color: var(--btn-color-hover);
            transform: translateY(-2px);
        }

        .hero .search-bar {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            margin-top: 3rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .hero .search-bar input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 1rem;
            outline: none;
        }

        .hero .search-bar button {
            background-color: var(--MainGreen);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .hero .search-bar button:hover {
            background-color: var(--btn-color-hover);
        }

        /* Blog Section */
        .blogs {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .blog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .blog-header p {
            color: var(--text-color-2);
            font-size: 1rem;
        }

        #blogCount {
            color: var(--text-color);
            font-weight: 600;
        }

        .sort {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .sort button {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color-2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .sort button.active,
        .sort button:hover {
            background-color: var(--MainGreen);
            color: white;
            border-color: var(--MainGreen);
        }

        .sort select {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .blog-card {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .blog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .blog-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--LightGreen), var(--MainGreen));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .blog-content {
            padding: 1.5rem;
        }

        .blog-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .blog-category {
            background-color: var(--MainGreen);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .blog-date {
            color: var(--text-color-2);
            font-size: 0.875rem;
        }

        .blog-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-color);
        }

        .blog-excerpt {
            color: var(--text-color-2);
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .read-more {
            color: var(--MainGreen);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .read-more:hover {
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-color-2);
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }

            .blog-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .sort {
                width: 100%;
                justify-content: flex-start;
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
          <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
          <a href="../../pages/CommonPages/aboutUs.html">About</a>
        </div>
      </div>
    </nav>

    <!-- Menu Links Desktop + Tablet -->
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

      <a href="../../pages/MemberPages/mSetting.php">
        <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
      </a>
    </section>
  </header>

  <hr>

  <!-- Main Content -->
  <main class="content" id="content">
    <section class="hero">
        <h5>OUR BLOGS</h5>
        <h1>Find all our blogs from here</h1>
        <p>Explore insightful articles and stories from the ReLeaf community. Share your knowledge and join the conversation about sustainability and environmental conservation.</p>
        <a href="../../pages/CommonPages/addBlog.php" class="btn">Write A Blog</a>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search by Blog title or keyword">
            <button onclick="searchBlogs()">🔍 Search</button>
        </div>
    </section>

    <section class="blogs">
        <div class="blog-header">
            <p>Showing <span id="blogCount"><?php echo count($blogs); ?></span> Blogs</p>
            <div class="sort">
                <button class="active" onclick="filterBlogs('all')">All</button>
                <button onclick="filterBlogs('recent')">Recent (3 days)</button>
                <select id="tagFilter" onchange="filterByTag()">
                    <option value="all">All Tags</option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?php echo htmlspecialchars($tag['tagName']); ?>">
                            <?php echo htmlspecialchars($tag['tagName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="blog-grid" id="blogGrid">
            <?php foreach ($blogs as $blog): ?>
                <div class="blog-card" onclick="location.href='readBlog.php?id=<?php echo $blog['blogID']; ?>'">
                    <div class="blog-image">📰</div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="blog-category"><?php echo htmlspecialchars($blog['category']); ?></span>
                            <span class="blog-date"><?php echo date('M d, Y', strtotime($blog['date'])); ?></span>
                        </div>
                        <h3 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h3>
                        <p class="blog-excerpt"><?php echo htmlspecialchars(substr($blog['excerpt'], 0, 100)) . '...'; ?></p>
                        <a href="readBlog.php?id=<?php echo $blog['blogID']; ?>" class="read-more">Read More →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
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
  </main>
  
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
                <a href="../../pages/MemberPages/mSetting.php">Settings</a>
            </div>
            <div>
                <b>Helps</b><br>
                <a href="../../pages/CommonPages/aboutUs.html">Contact</a><br>
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

    <script>const isAdmin = false;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        function filterBlogs(type) {
            // Update active button
            document.querySelectorAll('.sort button').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Reset tag filter
            document.getElementById('tagFilter').value = 'all';
            
            // Fetch filtered blogs
            fetch('mainBlog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=filterBlogs&type=' + encodeURIComponent(type)
            })
            .then(response => response.json())
            .then(data => {
                updateBlogDisplay(data.blogs, data.count);
            })
            .catch(error => console.error('Error:', error));
        }

        function filterByTag() {
            const tag = document.getElementById('tagFilter').value;
            
            // Reset button filters
            document.querySelectorAll('.sort button').forEach(btn => btn.classList.remove('active'));
            
            fetch('mainBlog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=filterByTag&tag=' + encodeURIComponent(tag)
            })
            .then(response => response.json())
            .then(data => {
                updateBlogDisplay(data.blogs, data.count);
            })
            .catch(error => console.error('Error:', error));
        }

        function searchBlogs() {
            const query = document.getElementById('searchInput').value;
            
            if (query.trim() === '') {
                alert('Please enter a search term');
                return;
            }
            
            // Reset filters
            document.querySelectorAll('.sort button').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tagFilter').value = 'all';
            
            fetch('mainBlog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=searchBlogs&query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(data => {
                updateBlogDisplay(data.blogs, data.count);
            })
            .catch(error => console.error('Error:', error));
        }

        function updateBlogDisplay(blogs, count) {
            const blogGrid = document.getElementById('blogGrid');
            const blogCount = document.getElementById('blogCount');
            
            blogCount.textContent = count;
            
            if (blogs.length === 0) {
                blogGrid.innerHTML = '<div class="no-results">No blogs found. Try a different search or filter.</div>';
                return;
            }
            
            blogGrid.innerHTML = blogs.map(blog => `
                <div class="blog-card" onclick="location.href='readBlog.php?id=${blog.blogID}'">
                    <div class="blog-image">📰</div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="blog-category">${escapeHtml(blog.category)}</span>
                            <span class="blog-date">${formatDate(blog.date)}</span>
                        </div>
                        <h3 class="blog-title">${escapeHtml(blog.title)}</h3>
                        <p class="blog-excerpt">${escapeHtml(blog.excerpt.substring(0, 100))}...</p>
                        <a href="readBlog.php?id=${blog.blogID}" class="read-more">Read More →</a>
                    </div>
                </div>
            `).join('');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>