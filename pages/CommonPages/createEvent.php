<?php
session_start();
include("../../php/dbConn.php");
include("../../php/sessionCheck.php");

$countries = [
    'Afghanistan', 'Åland Islands', 'Albania', 'Algeria', 'American Samoa', 'Andorra',
    'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia',
    'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh',
    'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia',
    'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory',
    'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon',
    'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile',
    'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo',
    'Congo, The Democratic Republic of The', 'Cook Islands', 'Costa Rica', "Cote D'ivoire",
    'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica',
    'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea',
    'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland',
    'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon',
    'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada',
    'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-bissau', 'Guyana',
    'Haiti', 'Heard Island and Mcdonald Islands', 'Holy See (Vatican City State)', 'Honduras',
    'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran, Islamic Republic of',
    'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jersey',
    'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', "Korea, Democratic People's Republic of",
    'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', "Lao People's Democratic Republic",
    'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein',
    'Lithuania', 'Luxembourg', 'Macao', 'Macedonia, The Former Yugoslav Republic of',
    'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands',
    'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of',
    'Moldova, Republic of', 'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco',
    'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles',
    'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island',
    'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory, Occupied',
    'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland',
    'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda',
    'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Pierre and Miquelon',
    'Saint Vincent and The Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe',
    'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore',
    'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa',
    'South Georgia and The South Sandwich Islands', 'Spain', 'Sri Lanka', 'Sudan',
    'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland',
    'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania, United Republic of',
    'Thailand', 'Timor-leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago',
    'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu',
    'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States',
    'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu',
    'Venezuela', 'Viet Nam', 'Virgin Islands, British', 'Virgin Islands, U.S.',
    'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe'
];

// Function to get timezone by country
function getTimezoneByCountry($country) {
    $timezoneGroups = [
        'UTC+13:00' => ['Samoa', 'Tonga'],
        'UTC+12:00' => ['Fiji', 'Kiribati', 'Marshall Islands', 'Nauru', 'New Zealand', 'Tuvalu', 'Wallis and Futuna'],
        'UTC+11:00' => ['New Caledonia', 'Norfolk Island', 'Solomon Islands', 'Vanuatu'],
        'UTC+10:00' => ['Australia', 'Guam', 'Micronesia, Federated States of', 'Northern Mariana Islands', 'Papua New Guinea'],
        'UTC+09:00' => ['Japan', 'Korea, Democratic People\'s Republic of', 'Korea, Republic of', 'Palau', 'Timor-leste'],
        'UTC+08:00' => ['China', 'Hong Kong', 'Macao', 'Malaysia', 'Philippines', 'Singapore', 'Taiwan', 'Brunei Darussalam', 'Mongolia'],
        'UTC+07:00' => ['Cambodia', 'Christmas Island', 'Indonesia', 'Lao People\'s Democratic Republic', 'Thailand', 'Viet Nam'],
        'UTC+06:30' => ['Cocos (Keeling) Islands', 'Myanmar'],
        'UTC+06:00' => ['Bangladesh', 'Bhutan', 'British Indian Ocean Territory', 'Kazakhstan', 'Kyrgyzstan'],
        'UTC+05:45' => ['Nepal'],
        'UTC+05:30' => ['India', 'Sri Lanka'],
        'UTC+05:00' => ['Maldives', 'Pakistan', 'French Southern Territories', 'Heard Island and Mcdonald Islands', 'Tajikistan', 'Turkmenistan', 'Uzbekistan'],
        'UTC+04:30' => ['Afghanistan'],
        'UTC+04:00' => ['Armenia', 'Azerbaijan', 'Georgia', 'Mauritius', 'Oman', 'Reunion', 'Seychelles', 'United Arab Emirates'],
        'UTC+03:30' => ['Iran, Islamic Republic of'],
        'UTC+03:00' => ['Bahrain', 'Belarus', 'Comoros', 'Djibouti', 'Eritrea', 'Ethiopia', 'Iraq', 'Jordan', 'Kenya', 'Kuwait', 'Madagascar', 'Mayotte', 'Qatar', 'Russian Federation', 'Saudi Arabia', 'Somalia', 'South Africa', 'Sudan', 'Syrian Arab Republic', 'Tanzania, United Republic of', 'Turkey', 'Uganda', 'Yemen'],
        'UTC+02:00' => ['Åland Islands', 'Botswana', 'Bulgaria', 'Burundi', 'Cyprus', 'Egypt', 'Estonia', 'Finland', 'Greece', 'Israel', 'Latvia', 'Lebanon', 'Lesotho', 'Libyan Arab Jamahiriya', 'Lithuania', 'Malawi', 'Moldova, Republic of', 'Mozambique', 'Namibia', 'Palestinian Territory, Occupied', 'Romania', 'Rwanda', 'Swaziland', 'Ukraine', 'Zambia', 'Zimbabwe'],
        'UTC+01:00' => ['Albania', 'Algeria', 'Andorra', 'Angola', 'Austria', 'Belgium', 'Benin', 'Bosnia and Herzegovina', 'Cameroon', 'Central African Republic', 'Chad', 'Congo', 'Congo, The Democratic Republic of The', 'Croatia', 'Czech Republic', 'Denmark', 'Equatorial Guinea', 'France', 'Gabon', 'Germany', 'Gibraltar', 'Holy See (Vatican City State)', 'Hungary', 'Italy', 'Liechtenstein', 'Luxembourg', 'Macedonia, The Former Yugoslav Republic of', 'Malta', 'Monaco', 'Montenegro', 'Morocco', 'Netherlands', 'Niger', 'Nigeria', 'Norway', 'Poland', 'San Marino', 'Serbia', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Tunisia', 'United Kingdom', 'Western Sahara'],
        'UTC+00:00' => ['Bouvet Island', 'Burkina Faso', 'Cote D\'ivoire', 'Faroe Islands', 'Gambia', 'Ghana', 'Greenland', 'Guernsey', 'Guinea', 'Guinea-bissau', 'Iceland', 'Ireland', 'Isle of Man', 'Jersey', 'Liberia', 'Mali', 'Mauritania', 'Portugal', 'Saint Helena', 'Sao Tome and Principe', 'Senegal', 'Sierra Leone', 'Svalbard and Jan Mayen', 'Togo', 'Tokelau'],
        'UTC-01:00' => ['Cape Verde'],
        'UTC-02:00' => ['South Georgia and The South Sandwich Islands'],
        'UTC-03:00' => ['Antarctica', 'Argentina', 'Brazil', 'Falkland Islands (Malvinas)', 'French Guiana', 'Paraguay', 'Saint Pierre and Miquelon', 'Suriname', 'Uruguay'],
        'UTC-04:00' => ['Anguilla', 'Antigua and Barbuda', 'Aruba', 'Barbados', 'Bermuda', 'Bolivia', 'Chile', 'Dominica', 'Dominican Republic', 'Grenada', 'Guadeloupe', 'Guyana', 'Martinique', 'Montserrat', 'Netherlands Antilles', 'Puerto Rico', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and The Grenadines', 'Trinidad and Tobago', 'Venezuela', 'Virgin Islands, British', 'Virgin Islands, U.S.'],
        'UTC-05:00' => ['Bahamas', 'Canada', 'Cayman Islands', 'Colombia', 'Cuba', 'Ecuador', 'Haiti', 'Jamaica', 'Panama', 'Peru', 'Turks and Caicos Islands', 'United States'],
        'UTC-06:00' => ['Belize', 'Costa Rica', 'El Salvador', 'Guatemala', 'Honduras', 'Mexico', 'Nicaragua'],
        'UTC-08:00' => ['Pitcairn'],
        'UTC-10:00' => ['Cook Islands', 'French Polynesia'],
        'UTC-11:00' => ['American Samoa', 'Niue', 'United States Minor Outlying Islands'],
    ];

    // Create a flattened array for quick lookup
    $countryTimezones = [];
    foreach ($timezoneGroups as $timezone => $countries) {
        foreach ($countries as $countryName) {
            $countryTimezones[$countryName] = $timezone;
        }
    }

    return $countryTimezones[$country] ?? 'UTC+08:00';
}

$errors = [];
$formData = [];
$hasError = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store form data for repopulation
    $formData = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'startDate' => $_POST['startDate'] ?? '',
        'endDate' => $_POST['endDate'] ?? '',
        'time' => $_POST['time'] ?? '',
        'day' => $_POST['day'] ?? '',
        'duration' => $_POST['duration'] ?? '',
        'location' => $_POST['location'] ?? '',
        'country' => $_POST['country'] ?? '',
        'maxPax' => $_POST['maxPax'] ?? '',
        'mode' => $_POST['mode'] ?? '',
        'type' => $_POST['type'] ?? ''
    ];
    
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $time = $_POST['time'];
    $day = $_POST['day'];
    $duration = $_POST['duration'];
    $location = trim($_POST['location']);
    $country = $_POST['country'];
    $maxPax = $_POST['maxPax'];
    $mode = $_POST['mode'] ?? '';
    $type = $_POST['type'] ?? '';
    $status = 'open';
    
    $timeZone = getTimezoneByCountry($country);

    // Validation
    if (empty($title) || strlen($title) < 5) {
        $errors['title'] = "Title must be at least 5 characters long";
    } elseif (strlen($title) > 60) {
        $errors['title'] = "Title cannot exceed 60 characters";
    }

    if (empty($description) || strlen($description) < 20) {
        $errors['description'] = "Description must be at least 20 characters long";
    } elseif (strlen($description) > 2000) {
        $errors['description'] = "Description cannot exceed 2000 characters";
    }

    $today = date('Y-m-d');
    if (empty($startDate) || $startDate < $today) {
        $errors['startDate'] = "Start date cannot be in the past";
    }
    
    if (empty($endDate) || $endDate < $startDate) {
        $errors['endDate'] = "End date cannot be before start date";
    }

    if (empty($time)) {
        $errors['time'] = "Time is required";
    }
    
    if (empty($duration) || $duration < 1 || $duration > 24) {
        $errors['duration'] = "Duration must be between 1 and 24 hours";
    }
    
    if (empty($day) || $day < 1 || $day > 60) {
        $errors['day'] = "Number of days must be between 1 and 60";
    }

    if (empty($location) || strlen($location) < 2) {
        $errors['location'] = "Location must be at least 2 characters long";
    }
    
    if (empty($country) || strlen($country) < 2) {
        $errors['country'] = "Please enter a valid country name";
    }
    
    if (empty($maxPax) || $maxPax < 20) {
        $errors['maxPax'] = "Maximum participants must be at least 20";
    } elseif ($maxPax > 10000) {
        $errors['maxPax'] = "Maximum participants cannot exceed 10,000";
    }

    if (empty($mode)) {
        $errors['mode'] = "Please select an event mode";
    }

    if (empty($type)) {
        $errors['type'] = "Please select a valid event type";
    }

    // Handle file upload
    $bannerFilePath = null;
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/eventBanner/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors['banner'] = "Failed to create upload directory";
                }
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            $fileType = $_FILES['banner']['type'];
            $fileName = $_FILES['banner']['name'];
            $fileSize = $_FILES['banner']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors['banner'] = "Only JPG, JPEG, PNG, and GIF files are allowed";
            } elseif (!in_array($fileType, $allowedTypes)) {
                $errors['banner'] = "Invalid file type. Please upload a valid image file";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $errors['banner'] = "File size must be less than 5MB";
            } elseif (!getimagesize($_FILES['banner']['tmp_name'])) {
                $errors['banner'] = "File is not a valid image";
            } else {
                $uniqueFileName = 'event_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $uniqueFileName;
                
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $targetPath)) {
                    $bannerFilePath = $targetPath;
                } else {
                    $errors['banner'] = "Failed to upload file. Please try again.";
                }
            }
        } else {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File size too large. Maximum size is 5MB.',
                UPLOAD_ERR_FORM_SIZE => 'File size too large.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to save file.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
            ];
            $errors['banner'] = $uploadErrors[$_FILES['banner']['error']] ?? 'File upload error occurred';
        }
    }
    
    // Insert into database if no errors
    if (empty($errors)) {
        if ($bannerFilePath === null) {
            $query = "INSERT INTO tblevents (userID, title, duration, day, startDate, endDate, time, timeZone, location, country, description, mode, type, status, maxPax, datePosted) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $connection->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("isiissssssssssi", 
                    $userID, $title, $duration, $day, $startDate, $endDate, 
                    $time, $timeZone, $location, $country, $description, 
                    $mode, $type, $status, $maxPax
                );
            }
        } else {
            $query = "INSERT INTO tblevents (userID, title, duration, day, startDate, endDate, time, timeZone, location, country, description, mode, type, status, maxPax, datePosted, bannerFilePath) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $connection->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("isiissssssssssis", 
                    $userID, $title, $duration, $day, $startDate, $endDate, 
                    $time, $timeZone, $location, $country, $description, 
                    $mode, $type, $status, $maxPax, $bannerFilePath
                );
            }
        }
        
        if ($stmt && $stmt->execute()) {
            $eventID = $stmt->insert_id;
            $_SESSION['success_message'] = "Event created successfully!";
            header("Location: createEvent.php");
            exit();
        } else {
            $errors['database'] = "Error creating event. Please try again.";
            if ($bannerFilePath && file_exists($bannerFilePath)) {
                unlink($bannerFilePath);
            }
        }
        
        if ($stmt) {
            $stmt->close();
        }
    }
    
    if (!empty($errors)) {
        $hasError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host An Event - ReLeaf</title>
    <link rel="icon" type="image/png" href="../../assets/images/Logo.png">

    <link rel="stylesheet" href="../../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
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

        .create-event-wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2.5rem;
        }

        .form-container {
            margin-bottom: 5rem;
            background: transparent;
            border: none;
        }

        .form-header {
            position: relative; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .form-header img {
            position: relative;
            width: 100%;
            max-width: 60px;
            height: auto;
            margin-bottom: 1rem;
            z-index: 1; 
        }

        .form-header::before {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            background: var(--sec-bg-color); 
            border-radius: 50%;
            filter: blur(50px);
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1; 
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            line-height: 1.4;
            margin-bottom: 1rem;
        }

        .form-header p {
            font-size: 1rem;
            color: var(--DarkerGray);
        }

        .dark-mode .form-header p {
            color: var(--Gray);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-container label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-container label .required {
            color: var(--Red);
        }

        .form-container label small {
            font-weight: 400;
            color: var(--Gray);
            font-size: 0.85rem;
        }

        .form-container textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container input[type="date"],
        .form-container input[type="time"],
        .form-container select,
        .form-container textarea {
            height: 3rem;
        }

        .radio-group {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            margin: 0;
            cursor: pointer;
            font-weight: 400;
        }

        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: var(--MainGreen);
        }

        .file-upload-area {
            margin-top: 0.5rem;
            border: 2px dashed var(--Gray);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-color);
            position: relative;
        }

        .file-upload-area:hover {
            border-color: var(--MainGreen);
            background: var(--LowGreen);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-text {
            color: var(--text-color);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .file-upload-hint {
            color: var(--DarkerGray);
            font-size: 0.8rem;
        }

        .dark-mode .file-upload-hint {
            color: var(--Gray);
        }

        .preview-container {
            display: none;
            text-align: center;
        }

        .preview-image {
            max-width: 100%;
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: var(--bg-color);
            border-radius: 8px;
            border: 2px solid var(--MainGreen);
            padding: 0.5rem;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .replace-btn {
            background: var(--Red);
            color: var(--White);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .replace-btn:hover {
            background: var(--DarkerGray);
            transform: translateY(-2px);
        }

        .save-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--LowGreen), var(--MainGreen), var(--LowGreen));
            color: var(--White);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px var(--Gray);
        }

        .error-message,
        .success-message {
            padding: 1rem;
            color: var(--White);
            border: 1px solid var(--Gray);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .error-message {
            background: var(--LowRed);
        }

        .success-message {
            background: var(--LowGreen);
        }

        .file-status {
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .file-status-hide {
            display: none;
        }

        .file-status.success {
            color: var(--White);
            background: var(--LowGreen);
            border: none;
        }

        .file-status.error {
            color: var(--text-color);
            background: var(--LowRed);
            border: none;
        }

        /* Field Error Styling */
        .field-error {
            color: var(--Red);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
            font-weight: 500;
        }

        .form-group.has-error input,
        .form-group.has-error textarea,
        .form-group.has-error select {
            border-color: var(--Red);
        }

        .form-group.has-error input:focus,
        .form-group.has-error textarea:focus,
        .form-group.has-error select:focus {
            border-color: var(--Red);
            box-shadow: 0 0 0 3px var(--LowRed);
        }

        .error-message {
            padding: 1rem;
            color: var(--White);
            border: 1px solid var(--Red);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
            background: var(--Red);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .radio-group {
                flex-direction: column;
                gap: 0.75rem;
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
                                <a href="../../pages/MemberPages/mChat.php">
                                    <img src="../../assets/images/chat-light.svg" alt="Chatbox">
                                </a>
                                <?php if ($unread_count > 0): ?>
                                    <span class="c-notification-badge" id="chatBadgeMobile"></span>
                                <?php endif; ?>
                            </div>
                            <a href="../../pages/MemberPages/mSetting.php">
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
                        <a href="../../pages/CommonPages/aboutUs.php">About</a>
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
                <a href="../../pages/CommonPages/aboutUs.php">About</a>
            <?php endif; ?>
        </nav>

        <section class="c-navbar-more">
            <input type="text" placeholder="Search..." id="searchBar" class="search-bar">
            <button id="themeToggle2">
                <img src="../../assets/images/light-mode-icon.svg" alt="Light Mode Icon">
            </button>

            <?php if ($isAdmin): ?>
                <!-- Admin Navbar More -->
                <a href="../../pages/adminPages/aProfile.php">
                    <img src="../../assets/images/profile-light.svg" alt="Profile" id="profileImg">
                </a>
            <?php else: ?>
                <!-- Member Navbar More -->
                <a href="../../pages/MemberPages/mChat.php" class="c-chatbox" id="chatboxDesktop">
                    <img src="../../assets/images/chat-light.svg" alt="Chatbox" id="chatImg">
                    <?php if ($unread_count > 0): ?>
                        <span class="c-notification-badge" id="chatBadgeDesktop"></span>
                    <?php endif; ?>

                </a>
                <a href="../../pages/MemberPages/mSetting.php">
                    <img src="../../assets/images/setting-light.svg" alt="Settings" id="settingImg">
                </a>
            <?php endif; ?>
        </section>
    </header>
    <hr>

    <!-- Main Content -->
    <main class="content" id="content">
        <section class="create-event-wrapper">
            <?php if ($hasError): ?>
                <div class="error-message">
                    <strong>Failed to create event</strong>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <a href="mainEvent.php" class="back-button">← Back to Events</a>
            
            <form class="form-container" method="POST" enctype="multipart/form-data" id="createEventForm">
                <div class="form-header">
                    <img src="../../assets/images/Logo.png" alt="Logo">
                    <h2>Create Your Event</h2>
                    <p>Start by filling up the form below.</p>
                </div>
                
                <!-- Title Field -->
                <div class="form-group full-width <?php echo isset($errors['title']) ? 'has-error' : ''; ?>">
                    <label>Event Title <span class="required">*</span></label>
                    <input class="c-input" type="text" name="title" placeholder="Enter event title" 
                        value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>" required />
                    <?php if (isset($errors['title'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['title']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Description Field -->
                <div class="form-group full-width <?php echo isset($errors['description']) ? 'has-error' : ''; ?>">
                    <label>Description <span class="required">*</span></label>
                    <textarea class="c-input" name="description" placeholder="Describe your event" required><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['description']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Date Fields Row -->
                <div class="form-row">
                    <div class="form-group <?php echo isset($errors['startDate']) ? 'has-error' : ''; ?>">
                        <label>Start Date <span class="required">*</span> <small>(When your event begins)</small></label>
                        <input class="c-input" type="date" name="startDate" id="startDate" 
                            value="<?php echo htmlspecialchars($formData['startDate'] ?? ''); ?>" required />
                        <?php if (isset($errors['startDate'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['startDate']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['endDate']) ? 'has-error' : ''; ?>">
                        <label>End Date <span class="required">*</span> <small>(When your event ends)</small></label>
                        <input class="c-input" type="date" name="endDate" id="endDate" 
                            value="<?php echo htmlspecialchars($formData['endDate'] ?? ''); ?>" required />
                        <?php if (isset($errors['endDate'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['endDate']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Time & Duration Row -->
                <div class="form-row">
                    <div class="form-group <?php echo isset($errors['time']) ? 'has-error' : ''; ?>">
                        <label>Time <span class="required">*</span> <small>(Event start time)</small></label>
                        <input class="c-input" type="time" name="time" id="time" 
                            value="<?php echo htmlspecialchars($formData['time'] ?? ''); ?>" required />
                        <?php if (isset($errors['time'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['time']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['duration']) ? 'has-error' : ''; ?>">
                        <label>Duration <span class="required">*</span> <small>(Total number of hours per day)</small></label>
                        <input class="c-input" type="number" name="duration" min="1" max="24" 
                            placeholder="Must be between 1 and 24 hours" 
                            value="<?php echo htmlspecialchars($formData['duration'] ?? ''); ?>" required />
                        <?php if (isset($errors['duration'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['duration']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Days & Max Participants Row -->
                <div class="form-row">
                    <div class="form-group <?php echo isset($errors['day']) ? 'has-error' : ''; ?>">
                        <label>Number of Days <span class="required">*</span> <small>(Total event duration)</small></label>
                        <input class="c-input" type="number" name="day" min="1" max="60" 
                            value="<?php echo htmlspecialchars($formData['day'] ?? '1'); ?>" required />
                        <?php if (isset($errors['day'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['day']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['maxPax']) ? 'has-error' : ''; ?>">
                        <label>Maximum Participants <span class="required">*</span></label>
                        <input class="c-input" type="number" name="maxPax" min="1" 
                            placeholder="Must be at least 20" 
                            value="<?php echo htmlspecialchars($formData['maxPax'] ?? ''); ?>" required />
                        <?php if (isset($errors['maxPax'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['maxPax']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Location & Country Row -->
                <div class="form-row">
                    <div class="form-group <?php echo isset($errors['location']) ? 'has-error' : ''; ?>">
                        <label>Location <span class="required">*</span></label>
                        <input class="c-input" type="text" name="location" placeholder="Event location or 'Online'" 
                            value="<?php echo htmlspecialchars($formData['location'] ?? ''); ?>" required />
                        <?php if (isset($errors['location'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['location']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['country']) ? 'has-error' : ''; ?>">
                        <label>Country <span class="required">*</span></label>
                        <select class="c-input c-input-select" id="registerCountry" name="country" required>
                            <option value="" disabled <?php echo empty($formData['country']) ? 'selected' : ''; ?>>Select your Country</option>
                            <?php 
                            $query = "SELECT country FROM tblusers WHERE userID = ?";
                            $stmt = $connection->prepare($query);
                            $stmt->bind_param("i", $userID);
                            $stmt->execute();
                            $stmt->bind_result($userCountry);
                            $stmt->fetch();
                            $stmt->close();

                            foreach ($countries as $country) {
                                $selected = '';
                                if (!empty($formData['country'])) {
                                    $selected = ($formData['country'] == $country) ? 'selected' : '';
                                } elseif ($userCountry == $country) {
                                    $selected = 'selected';
                                }
                                echo "<option value=\"$country\" $selected>$country</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['country'])): ?>
                            <span class="field-error"><?php echo htmlspecialchars($errors['country']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Event Mode -->
                <div class="form-group <?php echo isset($errors['mode']) ? 'has-error' : ''; ?>">
                    <label>Event Mode <span class="required">*</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="mode" value="online" 
                            <?php echo ($formData['mode'] ?? '') === 'online' ? 'checked' : ''; ?> required /> Online</label>
                        <label><input type="radio" name="mode" value="physical" 
                            <?php echo ($formData['mode'] ?? '') === 'physical' ? 'checked' : ''; ?> /> Physical</label>
                        <label><input type="radio" name="mode" value="hybrid" 
                            <?php echo ($formData['mode'] ?? '') === 'hybrid' ? 'checked' : ''; ?> /> Hybrid</label>
                    </div>
                    <?php if (isset($errors['mode'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['mode']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Event Type -->
                <div class="form-group <?php echo isset($errors['type']) ? 'has-error' : ''; ?>">
                    <label>Event Type <span class="required">*</span></label>
                    <select class="c-input c-input-select" name="type" required>
                        <option value="" disabled <?php echo empty($formData['type']) ? 'selected' : ''; ?>>Select category</option>
                        <option value="talk" <?php echo ($formData['type'] ?? '') === 'talk' ? 'selected' : ''; ?>>Talk</option>
                        <option value="workshop" <?php echo ($formData['type'] ?? '') === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                        <option value="seminar" <?php echo ($formData['type'] ?? '') === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                        <option value="clean-up" <?php echo ($formData['type'] ?? '') === 'clean-up' ? 'selected' : ''; ?>>Clean-up</option>
                        <option value="campaign" <?php echo ($formData['type'] ?? '') === 'campaign' ? 'selected' : ''; ?>>Campaign</option>
                        <option value="competition" <?php echo ($formData['type'] ?? '') === 'competition' ? 'selected' : ''; ?>>Competition</option>
                        <option value="tree-planting" <?php echo ($formData['type'] ?? '') === 'tree-planting' ? 'selected' : ''; ?>>Tree Planting</option>
                        <option value="other" <?php echo ($formData['type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <?php if (isset($errors['type'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['type']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Event Banner -->
                <div class="form-group <?php echo isset($errors['banner']) ? 'has-error' : ''; ?>">
                    <label>Event Banner <small>(Optional - PNG, JPG, JPEG)</small></label>
                    <div id="fileStatus"></div>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" name="banner" id="fileInput" accept="image/*" />
                        <div class="file-upload-text" id="uploadText">Click to upload or drag and drop</div>
                        <div class="file-upload-hint">Best viewed at 1200×400px | PNG, JPG, JPEG (Max 5MB)</div>
                    </div>
                    <div class="preview-container" id="previewContainer">
                        <img id="imagePreview" class="preview-image" />
                        <button type="button" class="replace-btn" id="replaceBtn">Replace Image</button>
                    </div>
                    <?php if (isset($errors['banner'])): ?>
                        <span class="field-error"><?php echo htmlspecialchars($errors['banner']); ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="save-btn">Create Event</button>
            </form>
        </section>
    </main>
    <!-- Search & Results -->
    <section class="search-container" id="searchContainer" style="display: none;">
        <!-- Tabs -->
        <div class="tabs" id="tabs">
            <div class="tab active" data-type="all">All</div>
            <div class="tab" data-type="profiles">Profiles</div>
            <div class="tab" data-type="blogs">Blogs</div>
            <div class="tab" data-type="events">Events</div>
            <div class="tab" data-type="trades">Trades</div>
        </div>

        <!-- Results -->
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
    <?php endif; ?>

    <script>
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        const unreadCount = <?php echo $unread_count; ?>;
    </script>
    <script src="../../javascript/mainScript.js"></script>
    <script>
        document.getElementById('startDate').addEventListener('click', function() {
            this.showPicker();
        });
        document.getElementById('endDate').addEventListener('click', function() {
            this.showPicker();
        });
        document.getElementById('time').addEventListener('click', function() {
            this.showPicker();
        });

        // File upload functionality with validation
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const replaceBtn = document.getElementById('replaceBtn');
            const uploadText = document.getElementById('uploadText');
            const fileStatus = document.getElementById('fileStatus');
            const form = document.getElementById('createEventForm');
            
            let validFile = null;

            // File validation function
            function validateFile(file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                // Clear previous status
                fileStatus.innerHTML = '';
                fileStatus.className = 'file-status';
                
                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    fileStatus.innerHTML = 'Invalid file type. Please select JPG, PNG, or GIF.';
                    fileStatus.className = 'file-status error';
                    return false;
                }
                
                // Check file size
                if (file.size > maxSize) {
                    fileStatus.innerHTML = 'File too large. Maximum size is 5MB.';
                    fileStatus.className = 'file-status error';
                    return false;
                }
                
                // File is valid
                fileStatus.innerHTML = 'File is valid and ready to upload.';
                fileStatus.className = 'file-status success';
                return true;
            }

            // Show preview and hide upload area
            function showPreview(file) {
                fileUploadArea.style.display = 'none';
                previewContainer.style.display = 'block';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            // Show upload area and hide preview
            function showUploadArea() {
                fileUploadArea.style.display = 'block';
                previewContainer.style.display = 'none';
                imagePreview.src = '';
                fileInput.value = '';
                validFile = null;
                fileStatus.innerHTML = '';
                fileStatus.className = 'file-status-hide';
                uploadText.textContent = 'Click to upload or drag and drop';
                uploadText.style.color = '';
                fileUploadArea.style.borderColor = 'var(--Gray)';
                fileUploadArea.style.background = 'var(--bg-color)';
            }

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                if (file) {
                    if (validateFile(file)) {
                        validFile = file;
                        showPreview(file);
                    } else {
                        validFile = null;
                        fileInput.value = '';
                    }
                }
            });

            // Handle replace button click
            replaceBtn.addEventListener('click', function() {
                showUploadArea();
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                
                if (file && !validFile) {
                    e.preventDefault();
                    alert('Please fix the file upload errors before submitting.');
                    return;
                }
                
                if (file && validFile) {
                    if (!validateFile(file)) {
                        e.preventDefault();
                        alert('Please fix the file upload errors before submitting.');
                        return;
                    }
                }
            });

            // Drag and drop functionality
            fileUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileUploadArea.style.borderColor = 'var(--MainGreen)';
                fileUploadArea.style.background = 'var(--LowGreen)';
            });

            fileUploadArea.addEventListener('dragleave', function() {
                if (!validFile) {
                    fileUploadArea.style.borderColor = 'var(--Gray)';
                    fileUploadArea.style.background = 'var(--bg-color)';
                }
            });

            fileUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').min = today;
            
            // Update end date min when start date changes
            document.getElementById('startDate').addEventListener('change', function() {
                document.getElementById('endDate').min = this.value;
            });

            // Auto-hide success message after 5 seconds
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.remove();
                    }, 500);
                }, 5000);
            }

            // Scroll to first error field if errors exist
            const firstError = document.querySelector('.has-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const input = firstError.querySelector('input, textarea, select');
                if (input) {
                    setTimeout(() => input.focus(), 500);
                }
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($connection);
?>