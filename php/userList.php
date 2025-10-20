<?php
    // step 1 - create a connection to the database
    include("dbConn.php");

    // step 2 - create a SQL query to fetch data from the table
    $query = "SELECT * FROM tblusers ORDER BY userID DESC";

    // step 3 - run the query
    $result = mysqli_query($connection, $query);

    // error handling
    if (!$result) {
        die("Query failed: " . mysqli_error($connection));
    }
?>


<!-- html -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer List</title>
</head>
<body>
    <h1>User List</h1>
    <table border="1">
        <tr>
            <th>User ID</th>
            <th>Full name</th>
            <th>Username</th>
            <th>Gender</th>
            <th>Email</th>
            <th>Password</th>
            <th>Bio</th>
            <th>Country</th>
            <th>User Type</th>
            <th>Points</th>
            <th>Trades Completed</th>
        </tr>
        <?php
            // step 4 - process the result
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?php echo $row["userID"]; ?></td>
                <td><?php echo $row["fullName"]; ?></td>
                <td><?php echo $row["username"]; ?></td>
                <td><?php echo $row["gender"]; ?></td>
                <td><?php echo $row["email"]; ?></td>
                <td><?php echo $row["password"]; ?></td>
                <td><?php echo $row["bio"]; ?></td>
                <td><?php echo $row["country"]; ?></td>
                <td><?php echo $row["userType"]; ?></td>
                <td><?php echo $row["point"]; ?></td>
                <td><?php echo $row["tradesCompleted"]; ?></td>
            </tr>
        <?php
            }
            // step 5 - close the connection
            mysqli_close($connection);
        ?>
    </table>
    <br>
</body>
</html>

