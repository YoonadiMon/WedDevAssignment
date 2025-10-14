<?php
    // step 1 - create a connection to the database
    include("dbConn.php");

    // step 2 - create a SQL query to fetch data from the table
    $query = "SELECT * FROM tbltickets ORDER BY ticketID DESC";

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
            <th>Ticket ID</th>
            <th>Subject</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Description</th>
            <th>User ID</th>
            <th>Username</th>
            <th>User Email</th>
            <th>Admin Assigned ID</th>
            <th>Is Unread</th>
            <th>Type</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Last Reply At</th>

        </tr>
        <?php
            // step 4 - process the result
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?php echo $row["ticketID"]; ?></td>
                <td><?php echo $row["subject"]; ?></td>
                <td><?php echo $row["category"]; ?></td>
                <td><?php echo $row["priority"]; ?></td>
                <td><?php echo $row["status"]; ?></td>
                <td><?php echo $row["description"]; ?></td>
                <td><?php echo $row["userID"]; ?></td>
                <td><?php echo $row["username"]; ?></td>
                <td><?php echo $row["userEmail"]; ?></td>
                <td><?php echo $row["adminAssignedID"]; ?></td>
                <td><?php echo $row["isUnread"]; ?></td>
                <td><?php echo $row["type"]; ?></td>
                <td><?php echo $row["createdAt"]; ?></td>
                <td><?php echo $row["updatedAt"]; ?></td>
                <td><?php echo $row["lastReplyAt"]; ?></td>
                
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

