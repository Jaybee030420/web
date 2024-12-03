<?php
@include 'config.php';


session_name('frontdesk');
session_start();

if (isset($_SESSION['username']) && isset($_SESSION['position'])) {

    $username = $_SESSION['username'];
    $position = $_SESSION['position'];
}else{
    header("Location: verify.php");  
}

$paymentDate = date('Y-m-d H:i:s');

$viewServiceDetails = null;
if (isset($_POST['viewService'])) {
    $viewCheckinID = $_POST['checkinID'];
    $sql = "SELECT * FROM service WHERE checkinID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $viewCheckinID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $viewServiceDetails .= "<p><strong>Service ID: </strong>" . $row['id'] . "<br><br>";
            $viewServiceDetails .= "<strong>Details: </strong><br>" . nl2br($row['serviceDetails']) . "<br><br>";
            $viewServiceDetails .= "<strong>Total Amount: </strong> ₱" . number_format($row['totalAmount'], 2) . "</p><p>
            -------------------------------------------------------------</p> <br><br>";
        }
    } else {
        $viewServiceDetails = "No services found for this Check-in ID.";
    }
}

if (isset($_POST['confirmOrder'])) {
    $checkinID = $_POST['checkinID'];
    $roomNumber = $_POST['roomNumber'];
    $quantity = $_POST['quantity'];
    $totalAmount = 0;

    // Query to fetch selected amenities
    $sql = "SELECT * FROM amenities WHERE amenitiesID IN (" . implode(",", array_keys($quantity)) . ")";
    $result = $conn->query($sql);

    $serviceDetails = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $amenityID = $row['amenitiesID'];
            $price = (float) $row['price'];  
            $qty = (int) $quantity[$amenityID];  
            $totalAmount += $price * $qty;
            if ($qty > 0) {
                $serviceDetails[] = "{$row['name']} (x$qty) - ₱ " . number_format($price * $qty, 2);
            }
        }
    }

    // Convert the service details array into a string
    $serviceDetailsStr = implode("\n", $serviceDetails);

    // If no services were selected, show an alert
    if (empty($serviceDetailsStr)) {
        echo "<script>alert('No services selected!');</script>";
    } else {
        // Display the modal with order review and service details
        ?>
        <div id="reviewModal" style="display:block; background-color: rgba(0, 0, 0, 0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000;">
            <div style="background-color: white; padding: 20px; margin: 100px auto; width: 50%; max-width: 600px; border-radius: 10px; position: relative;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; font-weight: bold; cursor: pointer;" onclick="document.getElementById('reviewModal').style.display='none'">&times;</span>

                <form method="POST" > <!-- your_php_script.php is the same script handling the order -->
                    <h2>Golden Grain Hotel</h2>
                    <p><strong>ID:</strong> 000<?= htmlspecialchars($checkinID) ?></p>
                    <p><strong>Room Number:</strong> <?= htmlspecialchars($roomNumber) ?></p>
                    <p>-------------------------------------------------------</p>
                    <p><strong>Selected Services:</strong></p>
                    <pre><?= nl2br(htmlspecialchars($serviceDetailsStr)) ?></pre>
                    <p>-------------------------------------------------------</p>
                    <p><strong>Total: </strong>₱ <?= number_format($totalAmount, 2) ?></p>

                    <label><strong>Enter Amount:</strong></label><br>
                    <input type="number" name="amount" placeholder="Enter amount here" required><br><br>
                    <input type="hidden" name="checkinID" value="<?= $checkinID ?>">
                    <input type="hidden" name="roomnum" value="<?= htmlspecialchars($roomNumber)?>">
                    <input type="hidden" name="serviceDetails" value="<?= htmlspecialchars($serviceDetailsStr) ?>">
                    <input type="hidden" name="totalAmount" value="<?= $totalAmount ?>">


                    <button type="submit" class="btn" name="confirmFinalOrder">Confirm Order</button>

                    
                </form>
            </div>
        </div>
        <?php
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
    <style>
        .modal {
            display: <?php echo isset($_POST['service']) ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

<nav id="sidebar" class='mx-lt-5'>
    <div class="title">
        <h1 style="color:darkorange;">Golden Grain Hotel <p style="font-weight: 500; color:darkgoldenrod">frontdesk</p></h1>
    </div>
    <div class="sidebar-list">
        <ul>
            <li><a href="Dashboard.php" class=""><span class='icon-field'><i class="fa fa-book"></i>Dashboard</span></a></li>
            <li><a href="Book.php" class=""><span class='icon-field'><i class="fa fa-book"></i>Booking List</span></a></li>
            <li><a href="extra.php" class="cat"><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Service</span></a></li>
            <li><a href="check-in.php" class=""><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Check - in</span></a></li>
            <li><a href="check-out.php" class=""><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="room.php" class=""><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
        </ul>
    </div>
</nav>


<div id="serviceModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Order Services</h2>
        <?php
        if (isset($_POST['service'])) {
            $checkinID = $_POST['checkinID'];
            $roomNumber = $_POST['roomNumber'];

            @include 'config.php';
            $sql = "SELECT * FROM amenities where bit = 1";
            $result = $conn->query($sql);
        }
        ?>
        <form method="post">
            <p><strong>Room Number:</strong> <?php echo isset($roomNumber) ? $roomNumber : ''; ?></p>
            <p><strong>Check-in ID:</strong> <?php echo isset($checkinID) ? $checkinID : ''; ?></p>

            <table class="service-table">
                <thead>
                    <tr>
                        <th>Amenity Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['price']); ?></td>
                                <td><input type="number" name="quantity[<?php echo $row['amenitiesID']; ?>]" min="0" ></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table><br><br>

            <input type="hidden" name="checkinID" value="<?php echo htmlspecialchars($checkinID); ?>">
            <input type="hidden" name="roomNumber" value="<?php echo htmlspecialchars($roomNumber); ?>">
            <input type="submit" class="btn" name="confirmOrder" value="Confirm Order">
        </form>
    </div>
</div>


<div class="container1">
    <div class="card">
        <h2>Golden Grain Hotel Service</h2>
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
                    <th>Check-out Date</th>
                    <th>No. of Person</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
            <?php
            @include 'config.php';

            $sql = "SELECT c.*, r.roomnumber, r.name, r.roombit FROM checkin c
            LEFT JOIN roomcat r ON c.roomnum = r.roomnumber
            WHERE r.roombit = 1 and c.status = 'checkin' ";

            if (isset($_POST['searchBtn'])) {
                $searchTerm = $conn->real_escape_string(trim($_POST['search']));
                $sql .= " AND (roomnum = '$searchTerm' OR roomnumber LIKE '%$searchTerm%')";
            }

            $sql .= " ORDER BY checkinID DESC LIMIT 10";

            $result = $conn->query($sql);

            if ($result === false) {
                echo '<tr><td colspan="6">Error executing query: ' . $conn->error . '</td></tr>';
            } else {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['roomnum']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['arrival']); ?></td>
                            <td><?php echo htmlspecialchars($row['depart']); ?></td>
                            <td><?php echo htmlspecialchars($row['person']); ?></td>
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="checkinID" value="<?php echo htmlspecialchars($row['checkinID']); ?>">
                                    <input type="hidden" name="roomNumber" value="<?php echo htmlspecialchars($row['roomnum']); ?>">
                                    <input type="submit" class="btn" name="service" value="Add Service">
                                    <input type="submit" class="btn" name="viewService" value="View">
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="6">No records found for the search term</td></tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>






<?php
if (isset($_POST['confirmFinalOrder'])) {
    // Collect POST data
    $checkinID = $_POST['checkinID'];
    $serviceDetailsStr = $_POST['serviceDetails'];
    $totalAmount = $_POST['totalAmount'];
    $amount = $_POST['amount'];
    $roomNumber = $_POST['roomnum'];
    $revenueType = 'daily';
    
    
    
    if ($amount >= $totalAmount || $amount <= 0) {
        $stmt = $conn->prepare("INSERT INTO revenue (checkinID, payment_amount, payment_date, revenue_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $checkinID, $totalAmount, $paymentDate, $revenueType);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting daily revenue.");
        }
        
        $stmt = $conn->prepare("INSERT INTO service (checkinID, serviceDetails, totalAmount) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $checkinID, $serviceDetailsStr, $totalAmount);

        if ($stmt->execute()) {
            // Success: Trigger modal visibility through JavaScript
            echo "<script>
                window.onload = function() {
                    document.getElementById('receiptModal').style.display = 'block';
                    window.print();
                };
            </script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid Number! Amount is bigger than balance');</script>";
    }
}
?>




<!-- Modal for displaying the receipt -->
<div id="receiptModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 350px; font-family: arial; font-size: 20px; line-height: 1.6; text-align: left; padding:0;">
        <span class="close" onclick="closeModal()" style="display: none;">&times;</span>

        <strong><p style="text-align: center; margin: 0;">Golden Grain Hotel</p></strong>
        <p style="text-align: center; margin: 0;">----------------------------------------</p>

        <p style="margin: 5px 0;">ID: 000<?= htmlspecialchars($checkinID) ?></p>
        <p style="margin: 5px 0;">Room Number: <?= htmlspecialchars($roomNumber) ?></p>        
        <p style="text-align: center; margin: 0;">----------------------------------------</p>

        <!-- Display the selected services here -->
        <p style="margin: 5px 0;"><strong>Selected Services:</strong></p>
        <pre style="margin: 5px 0;"><?= nl2br(htmlspecialchars($serviceDetailsStr)) ?></pre>

        <p style="text-align: center; margin: 0;">----------------------------------------</p>

        <p style="margin: 5px 0;">Total: <span style="float: right;">₱ <?= number_format($totalAmount, 2) ?></span></p>
        <p style="margin: 5px 0;">Amount Paid: <span style="float: right;">- ₱ <?= number_format($amount, 2) ?></span></p>
        <p style="text-align: center; margin: 0;">----------------------------------------</p>
        <strong><p style="float: right;">Change: <span style="float: right; margin-left: 5px;">  ₱<?= number_format($amount - $totalAmount, 2) ?></span></p><br><br><br>

        <p style="text-align: center; margin: 0;">---------------------------------</p>
        <strong><p style="margin: 10px 0; font-size: 14px; padding-left: 30px; padding-bottom: 20px;">Thank you for choosing Golden Grain Hotel!</p></strong>
    </div>
</div>



<!-- View Service Details Modal -->
<?php if (isset($viewServiceDetails)): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var serviceDetailsModal = document.getElementById("serviceDetailsModal");
            serviceDetailsModal.style.display = "block";
        });
    </script>
    <div id="serviceDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('serviceDetailsModal').style.display='none'">&times;</span>
            <h3>GoldenGrain Hotel</h3>
            <p>---------------------------------------------</p>
            <div><?php echo $viewServiceDetails; ?></div>
        </div>
    </div>
<?php endif; ?>





<script>
    var modal = document.getElementById("serviceModal");
    var closeBtn = document.querySelector(".close");

    closeBtn.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }

    // Close the modal when clicking outside the modal content
window.onclick = function(event) {
    var modal = document.getElementById('receiptModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

</script>

</body>
</html>
