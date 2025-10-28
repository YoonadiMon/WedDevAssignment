<?php
include("dbConn.php");

header('Content-Type: application/json');

// Get search parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage; // to show search results page by page

// avatar
function getInitials($name) {
    $words = explode(' ', trim($name));
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($words[0], 0, 2));
}

// truncate text
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Initialize results array
$results = [
    'profiles' => [],
    'blogs' => [],
    'events' => [],
    'trades' => []
];

$counts = [
    'profiles' => 0,
    'blogs' => 0,
    'events' => 0,
    'trades' => 0
];

// If query is empty, return empty results
if (empty($query)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'query' => '',
        'type' => $type,
        'results' => $results,
        'counts' => $counts,
        'page' => $page,
        'perPage' => $perPage
    ]);
    exit;
}

$searchQuery = "%$query%";

try {
    // Search User Profiles
    if ($type === 'all' || $type === 'profiles') {
        // Count
        $countSql = "SELECT COUNT(*) as total FROM tblusers 
                    WHERE (username LIKE '$searchQuery' OR fullName LIKE '$searchQuery') 
                    AND userType = 'member'";
        $countResult = mysqli_query($connection, $countSql);
        if ($countResult) {
            $counts['profiles'] = mysqli_fetch_assoc($countResult)['total'];
        }

        // Results
        if ($counts['profiles'] > 0) {
            $sql = "SELECT userID, fullName, username, bio, userType, country 
                    FROM tblusers 
                    WHERE (username LIKE '$searchQuery' OR fullName LIKE '$searchQuery') 
                    AND userType = 'member'
                    ORDER BY fullName ASC
                    LIMIT $perPage OFFSET $offset";
            
            $result = mysqli_query($connection, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $initials = getInitials($row['fullName']);
                    $bio = truncateText($row['bio'], 120);
                    
                    $results['profiles'][] = [
                        'id' => $row['userID'],
                        'type' => 'profile',
                        'title' => $row['fullName'],
                        'subtitle' => '@' . $row['username'],
                        'description' => $row['country'] ?? 'Unknown Location',
                        'initials' => $initials,
                        'bio' => $bio,
                        'userType' => $row['userType']
                    ];
                }
            }
        }
    }

    // Search Blogs
    if ($type === 'all' || $type === 'blogs') {
        // Get count
        $countSql = "SELECT COUNT(*) as total FROM tblblog b 
                     WHERE (b.title LIKE '$searchQuery' OR b.excerpt LIKE '$searchQuery' OR b.category LIKE '$searchQuery')";
        $countResult = mysqli_query($connection, $countSql);
        if ($countResult) {
            $counts['blogs'] = mysqli_fetch_assoc($countResult)['total'];
        }

        // Results
        if ($counts['blogs'] > 0) {
            $sql = "SELECT b.blogID, b.title, b.excerpt, b.category, b.date, u.username, u.fullName
                    FROM tblblog b 
                    JOIN tblusers u ON b.userID = u.userID
                    WHERE (b.title LIKE '$searchQuery' OR b.excerpt LIKE '$searchQuery' OR b.category LIKE '$searchQuery')
                    ORDER BY b.date DESC
                    LIMIT $perPage OFFSET $offset";
            
            $result = mysqli_query($connection, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $excerpt = truncateText($row['excerpt'] ?? '');
                    $results['blogs'][] = [
                        'id' => $row['blogID'],
                        'type' => 'blog',
                        'title' => truncateText($row['title'], 60),
                        'subtitle' => 'By ' . $row['fullName'],
                        'description' => $excerpt,
                        'category' => $row['category'],
                        'date' => date('M d, Y', strtotime($row['date']))
                    ];
                }
            }
        }
    }

    // Search Events
    if ($type === 'all' || $type === 'events') {
        // Get count
        $countSql = "SELECT COUNT(*) as total FROM tblevents e 
                     WHERE (e.title LIKE '$searchQuery' OR e.description 
                     LIKE '$searchQuery' OR e.location LIKE '$searchQuery' OR e.country LIKE '$searchQuery')";
        $countResult = mysqli_query($connection, $countSql);
        if ($countResult) {
            $counts['events'] = mysqli_fetch_assoc($countResult)['total'];
        }

        // Results
        if ($counts['events'] > 0) {
            $sql = "SELECT e.eventID, e.title, e.description, e.location, e.country, e.startDate, e.type, u.username, u.fullName
                    FROM tblevents e 
                    JOIN tblusers u ON e.userID = u.userID
                    WHERE (e.title LIKE '$searchQuery' OR e.description LIKE '$searchQuery' OR e.location LIKE '$searchQuery' OR e.country LIKE '$searchQuery')
                    ORDER BY e.startDate ASC
                    LIMIT $perPage OFFSET $offset";
            
            $result = mysqli_query($connection, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $description = truncateText($row['description'] ?? '');
                    $results['events'][] = [
                        'id' => $row['eventID'],
                        'type' => 'event',
                        'title' => truncateText($row['title'], 60),
                        'subtitle' => 'By ' . $row['fullName'],
                        'description' => $description,
                        'location' => truncateText($row['location'] . ', ' . $row['country'], 40),
                        'eventType' => $row['type'],
                        'date' => date('M d, Y', strtotime($row['startDate']))
                    ];
                }
            }
        }
    }

    // Search Trades
    if ($type === 'all' || $type === 'trades') {
        // Get count
        $countSql = "SELECT COUNT(*) as total FROM tbltrade_listings t 
                     WHERE (t.title LIKE '$searchQuery' OR t.description LIKE '$searchQuery' OR t.tags LIKE '$searchQuery')
                     AND t.status = 'active'";
        $countResult = mysqli_query($connection, $countSql);
        if ($countResult) {
            $counts['trades'] = mysqli_fetch_assoc($countResult)['total'];
        }

        // Results
        if ($counts['trades'] > 0) {
            $sql = "SELECT t.listingID, t.title, t.description, t.category, t.dateListed, t.status, u.username, u.fullName
                    FROM tbltrade_listings t 
                    JOIN tblusers u ON t.userID = u.userID
                    WHERE (t.title LIKE '$searchQuery' OR t.description LIKE '$searchQuery' OR t.tags LIKE '$searchQuery')
                    AND t.status = 'active'
                    ORDER BY t.dateListed DESC
                    LIMIT $perPage OFFSET $offset";
            
            $result = mysqli_query($connection, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $description = truncateText($row['description'] ?? '');
                    $results['trades'][] = [
                        'id' => $row['listingID'],
                        'type' => 'trade',
                        'title' => truncateText($row['title'], 60),
                        'subtitle' => 'By ' . $row['fullName'],
                        'description' => $description,
                        'category' => $row['category'],
                        'status' => $row['status'],
                        'date' => date('M d, Y', strtotime($row['dateListed']))
                    ];
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'query' => $query,
        'type' => $type,
        'results' => $results,
        'counts' => $counts,
        'page' => $page,
        'perPage' => $perPage,
        'hasMore' => $page * $perPage < array_sum($counts)
    ]);

} catch (Exception $error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Search error: ' . $error->getMessage()
    ]);
}

mysqli_close($connection);
?>