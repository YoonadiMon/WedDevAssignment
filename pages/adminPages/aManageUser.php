<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

// Check if user is admin
if ($_SESSION['userType'] !== 'admin') {
    header("Location: ../../pages/memberPages/memberIndex.php");
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $userIDToDelete = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($userIDToDelete == $_SESSION['userID']) {
        $error = "You cannot delete your own account!";
    } else {
        // Start transaction
        $connection->begin_transaction();
        
        try {
            // Delete user's related data first (if needed)
            // Delete tickets
            $stmt = $connection->prepare("DELETE FROM tbltickets WHERE userID = ?");
            $stmt->bind_param("i", $userIDToDelete);
            $stmt->execute();
            $stmt->close();
            
            // Delete trade listings
            $stmt = $connection->prepare("DELETE FROM tbltrade_listings WHERE userID = ?");
            $stmt->bind_param("i", $userIDToDelete);
            $stmt->execute();
            $stmt->close();
            
            // Delete the user
            $stmt = $connection->prepare("DELETE FROM tblusers WHERE userID = ? AND userType = 'member'");
            $stmt->bind_param("i", $userIDToDelete);
            $stmt->execute();
            $stmt->close();
            
            $connection->commit();
            $success = "User deleted successfully!";
        } catch (Exception $e) {
            $connection->rollback();
            $error = "Error deleting user: " . $e->getMessage();
        }
    }
}

// Fetch all users with search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT userID, fullName, username, email, gender, country, userType, point, createdAt, lastLogin 
          FROM tblusers 
          WHERE userType = 'member'";

if (!empty($searchTerm)) {
    $query .= " AND (fullName LIKE ? OR username LIKE ? OR email LIKE ? OR country LIKE ?)";
}

$query .= " ORDER BY createdAt DESC";

$stmt = $connection->prepare($query);

if (!empty($searchTerm)) {
    $searchParam = "%$searchTerm%";
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total user count
$totalQuery = "SELECT COUNT(*) as total FROM tblusers WHERE userType = 'member'";
$totalResult = $connection->query($totalQuery);
$totalUsers = $totalResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReLeaf - Manage Users</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .manage-users-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
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
            color: var(--DarkerGray);
            text-decoration: underline;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .user-count {
            color: var(--Gray);
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        .search-section {
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            max-width: 600px;
        }

        .search-input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            transition: all 0.3s ease;
        }

        .search-btn {
            padding: 0.875rem 2rem;
            background: var(--MainGreen);
            color: var(--White);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--LowGreen);
        }

        .clear-btn {
            padding: 0.875rem 1.5rem;
            background: var(--Gray);
            color: var(--White);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .clear-btn:hover {
            background: var(--DarkerGray);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
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

        .users-table-container {
            background: var(--bg-color);
            border-radius: 16px;
            box-shadow: 0 4px 12px var(--Gray);
            overflow: hidden;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table thead {
            background: var(--LowGreen);
            color: var(--text-color);
        }

        .users-table th {
            padding: 1.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .users-table td {
            text-align: center;
            padding: 1.25rem;
            border-bottom: 1px solid var(--Gray);
            color: var(--text-color);
        }

        .users-table .user {
            text-align: left;
        }

        .users-table tbody tr {
            transition: all 0.2s ease;
        }

        .users-table tbody tr:hover {
            background: var(--LightGreen);
        }


        .dark-mode .users-table tbody tr:hover {
            background: var(--DarkerGray);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-details h4 {
            margin: 0;
            font-weight: 600;
            color: var(--text-color);
        }

        .user-details p {
            margin: 0.25rem 0 0;
            font-size: 0.85rem;
            color: var(--Gray);
        }

        .btn-delete {
            margin: 0 auto;
            display: flex;
            padding: 0.5rem 1rem;
            background: var(--Red);
            color: var(--White);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .btn-delete:hover {
            background: var(--LowRed);
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--Gray);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        /* Delete Confirmation Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: var(--bg-color);
            border: 1px solid var(--Gray);
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal h2 {
            font-size: 1.75rem;
            color: var(--Red);
            margin-bottom: 1rem;
        }

        .modal p {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .modal ul {
            color: var(--text-color); 
            margin-left: 1.5rem; 
            margin-bottom: 1.5rem;
        }

        .modal-user-info {
            background: var(--Gray);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .modal-user-info strong {
            color: var(--DarkerGray);
        }

        .modal-user-info .user-info {
            color: var(--Black);
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: var(--Gray);
            color: var(--White);
        }

        .btn-cancel:hover {
            background: var(--DarkerGray);
        }

        .btn-confirm {
            background: var(--Red);
            color: var(--White);
        }

        .btn-confirm:hover {
            background: var(--LowRed);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .users-table {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .manage-users-container {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .search-form {
                flex-direction: column;
            }

            .users-table-container {
                overflow-x: auto;
            }

            .users-table {
                min-width: 800px;
            }

            .modal {
                padding: 1.5rem;
            }

            .btn-modal {
                
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div id="cover" class="" onclick="hideMenu()"></div>
    
    <!-- Header -->
    <header>
        <section class="c-logo-section">
            <a href="../../pages/adminPages/adminIndex.php" class="c-logo-link">
                <img src="../../assets/images/Logo.png" alt="Logo" class="c-logo">
                <div class="c-text">ReLeaf</div>
            </a>
        </section>

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
                        <a href="../../pages/adminPages/aProfile.php">
                            <img src="../../assets/images/profile-light.svg" alt="Profile">
                        </a>
                    </section>
                    <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
                    <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
                    <a href="../../pages/CommonPages/mainEvent.php">Event</a>
                    <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
                    <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
                    <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
                </div>
            </div>
        </nav>

        <nav class="c-navbar-desktop">
            <a href="../../pages/adminPages/adminIndex.php">Dashboard</a>
            <a href="../../pages/CommonPages/mainBlog.php">Blog</a>
            <a href="../../pages/CommonPages/mainEvent.php">Event</a>
            <a href="../../pages/CommonPages/mainTrade.php">Trade</a>
            <a href="../../pages/CommonPages/mainFAQ.php">FAQs</a>
            <a href="../../pages/adminPages/aHelpTicket.php">Help</a>
        </nav>
        
        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>
            <a href="../../pages/adminPages/aProfile.php">
                <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
            </a>
        </section>
    </header>
    <hr>

    <!-- Main Content -->
    <main>
        <div class="manage-users-container">
            <a href="../../pages/adminPages/adminIndex.php" class="back-button">
                ← Back to Dashboard
            </a>

            <div class="page-header">
                <h1>Manage Users</h1>
                <p class="user-count">Total Users: <?php echo number_format($totalUsers); ?></p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="search-section">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="c-input search-input" 
                        placeholder="Search by name, username, email, or country..." 
                        value="<?php echo htmlspecialchars($searchTerm); ?>" >
                    <button type="submit" class="search-btn">Search</button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="../../pages/adminPages/aManageUser.php" class="clear-btn">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="users-table-container">
                <?php if (count($users) > 0): ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th class="user">User</th>
                                <th>Email</th>
                                <th>Gender</th>
                                <th>Country</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="user">
                                        <div class="user-info"> 
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($user['fullName']); ?></h4>
                                                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($user['gender'])); ?></td>
                                    <td><?php echo htmlspecialchars($user['country']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['createdAt'])); ?></td>
                                    <td>
                                        <?php 
                                        if ($user['lastLogin']) {
                                            echo date('M d, Y', strtotime($user['lastLogin']));
                                        } else {
                                            echo '<span style="color: var(--Gray);">Never</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn-delete" onclick="showDeleteModal(
                                                <?php echo $user['userID']; ?>, 
                                                '<?php echo htmlspecialchars($user['fullName']); ?>', 
                                                '<?php echo htmlspecialchars($user['username']); ?>'
                                            )">Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No users found</h3>
                        <p>
                            <?php if (!empty($searchTerm)): ?>
                                No users match your search criteria. Try a different search term.
                            <?php else: ?>
                                There are currently no users in the system.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h2>Confirm Deletion</h2>
            <p>Are you absolutely sure you want to delete this user? This action cannot be undone.</p>
            <div class="modal-user-info">
                <strong>User:</strong> <span class="user-info" id="modalUserName"></span><br>
                <strong>Username:</strong> <span class="user-info" id="modalUsername"></span>
            </div>
            <p style="color: var(--Red);">
                This will permanently delete:
            </p>
            <ul>
                <li>User account and profile</li>
                <li>All posts, uploads and tickets created by this user</li>
            </ul>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="user_id" id="deleteUserId">
                <div class="modal-buttons">
                    <button type="button" class="btn-modal btn-cancel" onclick="closeDeleteModal()">
                        Cancel
                    </button>
                    <button type="submit" name="delete_user" class="btn-modal btn-confirm">
                        Yes, Delete User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>const isAdmin = true;</script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        function showDeleteModal(userId, fullName, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('modalUserName').textContent = fullName;
            document.getElementById('modalUsername').textContent = '@' + username;
            document.getElementById('deleteModal').classList.add('active');
            document.body.classList.add('stopScroll');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.body.classList.remove('stopScroll');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>