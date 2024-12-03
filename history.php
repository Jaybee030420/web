<?php
@include 'config.php'; // Only include once

$checkinData = null; // Initialize variable to hold check-in data

if (isset($_POST['logout'])) {
    echo "<script>alert('Successfully logged out! Thank You!'); window.location.href = 'index.php'; </script>";
}

if (isset($_POST['edit'])) {
    $checkinId = mysqli_real_escape_string($conn, $_POST['checkinId']); // Get the check-in ID
    $query = "SELECT * FROM checkout WHERE ID = '$checkinId'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $checkinData = mysqli_fetch_assoc($result); // Fetch check-in data for editing
    } else {
        echo "<script>alert('No check-in data found for this ID.');</script>";
    }
}
if (isset($_POST['view'])) {
    $checkinId = mysqli_real_escape_string($conn, $_POST['checkinId']); // Get the check-in ID
    $query = "SELECT * FROM checkout WHERE ID = '$checkinId'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $details = mysqli_fetch_assoc($result); // Fetch check-in data for editing
    } else {
        echo "<script>alert('No check-in data found for this ID.');</script>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Grain Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
    <link rel="stylesheet" href="check-in.css">
</head>
<body>
<nav id="sidebar" class='mx-lt-5'>
    <div class="title">
        <h1 style="color:darkorange;">Golden Grain Hotel <p style="font-weight: 500; color:darkgoldenrod">frontdesk</p></h1>
    </div>
    <div class="sidebar-list">
        <ul>
            <li><a href="Dashboard.php"><span class='icon-field'><i class="fa fa-book"></i>Dashboard</span></a></li>
            <li><a href="Book.php"><span class='icon-field'><i class="fa fa-book"></i>Booking List</span></a></li>
            <li><a href="extra.php"><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Service</span></a></li>
            <li><a href="check-in.php"><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Check - in</span></a></li>
            <li><a href="check-out.php" class=""><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="history.php" class="cat"><span class='icon-field'><i class="fa fa-sign-out-alt"></i>History</span></a></li>
            <li><a href="room.php"><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
            <li>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="submit" class="btn" name="logout" value="Log-out">
                </form>
            </li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="card">
        <h2>Check - out List</h2>
        <div class="search-container" style="margin-left: 130vh;">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="text" name="search" placeholder="Search here" required>
                <input type="submit" class="btn" name="searchBtn" value="Search">
            </form>
        </div>

        <table class="room-table">
    <thead>
        <tr>
            <th>Room Number</th>
            <th>Room Type</th>
            <th>Check-in Date</th>
            <th>Guest Name</th>
            <th>Contact Number</th>
            <th>Operation</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT * From checkout";

    // Check if the search button is clicked
    if (isset($_POST['searchBtn'])) {
        // Get the search term
        $searchTerm = $conn->real_escape_string(trim($_POST['search']));
        // Modify the SQL query to search in roomnum or guest last name
        $sql .= " AND (checkout.roomnum = '$searchTerm' OR checkout.lname LIKE '%$searchTerm%')";
    }

    // Add ordering and limit
    $sql .= " ORDER BY checkout.ID DESC LIMIT 10";

    // Perform the query
    $result = $conn->query($sql);

    // Check if query execution was successful
    if ($result === false) {
        echo '<tr><td colspan="6">Error executing query: ' . $conn->error . '</td></tr>';
    } else {
        // Check if there are results
        if ($result->num_rows > 0) {
            // Iterate through results and display each row
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['roomnum']); ?></td>
                    <td><?php echo htmlspecialchars($row['roomtype']); ?></td>
                    <td><?php echo htmlspecialchars($row['arrival']); ?></td>
                    <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                    <td><?php echo htmlspecialchars($row['number']); ?></td>
                    <td>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                            <input type="hidden" name="checkinId" value="<?php echo htmlspecialchars($row['ID']); ?>">
                            <input type="submit" class="btn" name="view" value="View">
                            <input type="submit" class="btn" name="edit" value="Print">
                        </form>
                    </td>
                </tr>
                <?php
            }
        } else {
            // No records found
            echo '<tr><td colspan="6">No records found for the search term</td></tr>';
        }
    }
    ?>
    </tbody>
</table>
    </div>
</div>


<div id="detailsModal" class="modal" style="display: <?php echo isset($details) ? 'block' : 'none'; ?>;">
    <div class="modal-content">
        <span class="close" onclick="this.parentElement.parentElement.style.display='none'">&times;</span>
        <h1>Golden Grain Hotel</h1>
        <h2>Checkout Details</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ccc; padding: 5px;">Field</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Receipt Number</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo sprintf('%05d', htmlspecialchars($details['ID'])); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Customer Name</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars($details['fname'] . ' ' . $details['lname']); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Room Number</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars($details['roomnum']); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Check-in Date</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars($details['arrival']); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Contact Number</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars($details['number']); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Total Amount</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars('₱' . number_format($details['total_amount'], 2)); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Service Charge</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars('₱' . number_format($details['service'], 2)); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Payment</td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars('₱' . number_format($details['payment'], 2)); ?></td>
                </tr>
                <!-- Add any additional fields from your checkout table here -->
            </tbody>
        </table>

        <button class="btn" onclick="window.print();">Print Receipt</button>
    </div>
</div>



<div id="downpaymentModal" class="modal" style="display: <?php echo isset($checkinData) ? 'block' : 'none'; ?>;">
    <div class="modal-content">
        <span class="close" onclick="this.parentElement.parentElement.style.display='none'">&times;</span>
        <h1>Golden Grain Hotel</h1>
        <div class="receipt-details">
            <div>Date: <span id="receipt-date"></span></div>
            <div>Receipt Number: <span id="receipt-number"><?php echo sprintf('%05d', htmlspecialchars($checkinData['ID'])); ?></span></div>
            <div>Customer Name: <span id="customer-name"><?php echo htmlspecialchars($checkinData['fname'] . ' ' . $checkinData['lname']); ?></span></div>
            <div>Room Number: <span id="room-number"><?php echo htmlspecialchars($checkinData['roomnum']); ?></span></div>
            <div>Check-in Date: <span id="checkin-date"><?php echo htmlspecialchars($checkinData['arrival']); ?></span></div>
        </div>

        <h3>Items</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ccc; padding: 5px;">Description</th>
                    <th style="border: 1px solid #ccc; padding: 5px;">Price</th>
                </tr>
            </thead>
            <tbody id="item-list">
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><strong>Room charge</strong></td>
                    <td style="border: 1px solid #ccc; padding: 5px;" id="room-charge"><strong><?php echo htmlspecialchars('₱' . number_format($checkinData['total_amount'], 2)); ?></strong></td>
                </tr>

                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><strong>Service Charge (Breakdown)</strong></td>
                </tr>
                
                <?php
                // Display extra services if they exist
                if (!empty($checkinData['extra'])) {
                    // Assuming extra is a comma-separated string like "Service1 x Quantity1, Service2 x Quantity2"
                    $extraServices = explode(', ', $checkinData['extra']);
                    foreach ($extraServices as $service) {
                        // Split each service into name and quantity
                        list($serviceName, $quantity) = explode(' x ', $service);
                        
                        // Fetch the price of the service
                        $serviceQuery = "SELECT price FROM amenities WHERE name = '" . mysqli_real_escape_string($conn, trim($serviceName)) . "'";
                        $serviceResult = mysqli_query($conn, $serviceQuery);
                        $servicePrice = 0;

                        if ($serviceResult && mysqli_num_rows($serviceResult) > 0) {
                            $serviceData = mysqli_fetch_assoc($serviceResult);
                            $servicePrice = $serviceData['price'];
                        }

                        $totalServicePrice = $servicePrice * intval($quantity);
                        ?>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars($serviceName) . ' x ' . htmlspecialchars($quantity); ?></td>
                            <td style="border: 1px solid #ccc; padding: 5px;"><?php echo htmlspecialchars('₱' . number_format($totalServicePrice, 2)); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><strong>Total Service Charge</strong></td>
                    <td style="border: 1px solid #ccc; padding: 5px;" id="service-charge"><strong><?php echo htmlspecialchars('₱' . number_format($checkinData['service'], 2)); ?></strong></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">Payment</td>
                    <td style="border: 1px solid #ccc; padding: 5px;" id="payment"><?php echo htmlspecialchars('₱' . number_format($checkinData['payment'], 2)); ?></td>
                </tr>
            </tbody>
        </table><br>

        <div class="total"><strong>TOTAL:    </strong><span id="total-amount"><?php echo htmlspecialchars('₱' . number_format($checkinData['total_amount'] + $checkinData['service'], 2)); ?></span></div><br><br>
        
        <button class="btn" onclick="window.print();">Print Receipt</button>
        <script>
            // Set receipt date
            document.getElementById('receipt-date').textContent = new Date().toLocaleDateString();
        </script>
    </div>
</div>


<script>
    var modal = document.getElementById("downpaymentModal");

    var closeBtn = document.getElementsByClassName("close")[0];
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>
