<?php
    // step 1 - create a connection to the database
    include("dbConn.php");

    // step 2 - create a SQL query to fetch data from the table
    $query = "SELECT * FROM tbltrade_listings ORDER BY userID DESC";

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
            <th>Listing ID</th>
            <th>User ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Tags</th>
            <th>Image URL</th>
            <th>Category</th>
            <th>Date Listed</th>
            <th>Status</th>
            <th>Item Type</th>
            <th>Item Condition</th>
            <th>Species</th>
            <th>Growth Stage</th>
            <th>Care Instructions</th>
            <th>Brand</th>
            <th>Dimensions</th>
            <th>Usage History</th>
            <th>Looking For</th>
            <th>Reported</th>

        </tr>
        <?php
            // step 4 - process the result
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <th><?php echo $row["listingID"];?></td>
                <td><?php echo $row["userID"]; ?></td>
                <td><?php echo $row["title"]; ?></td>
                <td><?php echo $row["description"]; ?></td>
                <td><?php echo $row["tags"]; ?></td>
                <td><?php echo $row["imageUrl"]; ?></td>
                <td><?php echo $row["category"]; ?></td>
                <td><?php echo $row["dateListed"]; ?></td>
                <td><?php echo $row["status"]; ?></td>
                <td><?php echo $row["itemType"]; ?></td>
                <td><?php echo $row["itemCondition"]; ?></td>
                <td><?php echo $row["species"]; ?></td>
                <td><?php echo $row["growthStage"]; ?></td>
                <td><?php echo $row["careInstructions"]; ?></td>
                <td><?php echo $row["brand"]; ?></td>
                <td><?php echo $row["dimensions"]; ?></td>
                <td><?php echo $row["usageHistory"]; ?></td>
                <td><?php echo $row["lookingFor"]; ?></td>
                <td>
                    <?php echo $row["reported"] == 1 ? 'true' : 'false'; ?>
                </td>
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

