<!-- STILL TESTING -->
<?php

$autoCloseQuery = "UPDATE tblevents SET status = 'closed' WHERE endDate < CURDATE() AND status NOT IN ('cancelled', 'closed')";
$connection->query($autoCloseQuery);

?>