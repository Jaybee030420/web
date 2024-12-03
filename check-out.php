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

$revenue = $checkinID = 0;
$paymentDate = date('Y-m-d H:i:s');


if (isset($_POST['confirm'])) {

    $revenue = isset($_POST['revenue']) ? (float) $_POST['revenue'] : 0; 
    $balance = isset($_POST['balance']) ? (float) $_POST['balance'] : 0; 
    $checkinID = isset($_POST['checkinID']) ? (int) $_POST['checkinID'] : 0;  
    
 
    $conn->begin_transaction();
    
    try {
 
        $checkinSql = "SELECT checkinID FROM checkin WHERE checkinID = ?";
        if ($checkinStmt = $conn->prepare($checkinSql)) {
            $checkinStmt->bind_param("i", $checkinID);
            $checkinStmt->execute();
            $checkinStmt->store_result();
            
            if ($checkinStmt->num_rows == 0) {
                throw new Exception('Error: Invalid checkinID. It does not exist in the checkin table.');
            }
            $checkinStmt->close();
        } else {
            throw new Exception('Error retrieving checkinID from the checkin table.');
        }

        if ($balance != 0) {
            echo "<script>alert('Please pay the remaining balance');</script>";
            echo "<script>window.location.href = 'check-out.php';</script>";
            exit;
        }

        $revenueType = 'daily';  
        $revenueSql = "INSERT INTO revenue (checkinID, payment_amount, payment_date, revenue_type)
                        VALUES (?, ?, ?, ?)";
        if ($revenueStmt = $conn->prepare($revenueSql)) {
            $revenueStmt->bind_param("idss", $checkinID, $revenue, $paymentDate, $revenueType);
            if (!$revenueStmt->execute()) {
                throw new Exception("Error inserting daily revenue.");
            }
            $revenueStmt->close();
        } else {
            throw new Exception('Error preparing the SQL statement for daily revenue.');
        }

        $updateRoomCatSql = "UPDATE roomcat SET roombit = 0 WHERE roomnumber = (SELECT roomnum FROM checkin WHERE checkinID = ?)";
        if ($roomCatStmt = $conn->prepare($updateRoomCatSql)) {
            $roomCatStmt->bind_param("i", $checkinID);
            if (!$roomCatStmt->execute()) {
                throw new Exception('Error updating room availability.');
            }
            $roomCatStmt->close();
        } else {
            throw new Exception('Error preparing SQL for room status update.');
        }

        $updateCheckinStatusSql = "UPDATE checkin SET status = 'checkout' WHERE checkinID = ?";
        if ($checkinStatusStmt = $conn->prepare($updateCheckinStatusSql)) {
            $checkinStatusStmt->bind_param("i", $checkinID);
            if (!$checkinStatusStmt->execute()) {
                throw new Exception('Error updating checkin status.');
            }
            $checkinStatusStmt->close();
        } else {
            throw new Exception('Error preparing SQL for checkin status update.');
        }

        // Commit transaction
        $conn->commit();
        
        
        echo "<script>alert('Thank You! ID: $checkinID checkout successfully');</script>";
        echo "<script>window.location.href = 'check-out.php';</script>";


    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
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
            <li><a href="check-out.php" class="cat"><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="room.php"><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
        </ul>
    </div>
</nav>






<!-- Table -->
<div class="container1">
    <div class="card">
        <h2>Golden Grain Hotel</h2>
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
                                    <input type="submit" class="btn" name="view" value="Payment">

                                </form>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="checkinID" value="<?php echo htmlspecialchars($row['checkinID']); ?>">
                                    <input type="hidden" name="roomNumber" value="<?php echo htmlspecialchars($row['roomnum']); ?>">
                                    <input type="submit" class="btn" name="view1" value="Checkout">
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
$view1 = null; 

if (isset($_POST['view1'])) {
    $checkinID = (int)$_POST['checkinID'];
    $detailsSql = "SELECT c.*, c.person as person1, r.*, r.person as personcount, s.*, p.discount as promo_name, d.amount as discount_value, g.* 
                   FROM checkin c 
                   LEFT JOIN roomcat r ON c.roomnum = r.roomnumber
                   LEFT JOIN service s ON c.checkinID = s.checkinID
                   LEFT JOIN promo p on c.promo = p.ID
                   LEFT JOIN discount d on c.discount = d.ID
                   LEFT JOIN guest g on c.guest_id = g.id
                   WHERE c.checkinID = $checkinID";
    
    $detailsResult = $conn->query($detailsSql);
    
    if ($detailsResult->num_rows > 0) {
        $view1 = $detailsResult->fetch_assoc();
    } else {
        echo "<script>alert('No details found.');</script>";
    }
}
?>

<div id="view1" class="modal" style="display: none;">
    <div class="modal-content">
        <span id="close" class="close" onclick="closeModal('view1')" >&times;</span>
        <h2>Golden Grain Hotel</h2>
        <p>----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</p><br><br>

        <p><strong>---------Room Details------------</strong></p>
        <?php if ($view1): ?>
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($view1['roomnum']); ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y'); ?></p>
            <p><strong>Guest Name:</strong> <?php echo ($view1['first_name'] ?? 'Not') . ' ' . ($view1['last_name'] ?? 'Available'); ?></p><br>

            <p><strong>-------Summary Report ---------------</strong></p>
            <p><strong>Check-In:</strong> <?php echo htmlspecialchars($view1['arrival']); ?></p>
            <p><strong>Check-Out:</strong> <?php echo htmlspecialchars($view1['depart']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($view1['name']); ?></p>
            <p><strong>Person number:</strong> <?php echo htmlspecialchars($view1['person1']); ?></p>

            <?php
            // Assuming $view1 is available and contains 'arrival' and 'depart'
            $arrivalDate = strtotime($view1['arrival']); 
            $personcount = (int)htmlspecialchars($view1['personcount']); 
            $person1 = (int)htmlspecialchars($view1['person1']); 
            $todayDate = date('Y-m-d'); 
            $checkoutDate = strtotime($view1['depart']); 

            // Convert today's date and depart date to DateTime objects for comparison
            $todayDateWithTime = new DateTime('now', new DateTimeZone('Asia/Manila')); // Use Asia timezone
            $checkoutDateWithTime = new DateTime($view1['depart'], new DateTimeZone('Asia/Manila'));

            // Determine the correct checkout date
            if ($todayDateWithTime > $checkoutDateWithTime) {
                // If today's date is later than departure date, use today's date for checkout date
                $checkoutDate = $todayDateWithTime;
            } else {
                // Otherwise, use the original depart date from $view1
                $checkoutDate = $checkoutDateWithTime;
            }

            // Calculate the number of nights from arrival to checkout
            $checkinDate = new DateTime($view1['arrival'], new DateTimeZone('Asia/Manila')); // Arrival date
            $interval = $checkinDate->diff($checkoutDate); // Get the difference in days
            $nights = $interval->days; // Number of nights

            // Check if the checkout time is after 13:00 (1 PM) and calculate the extra charge
            $extraCharge = 0;
            $checkoutHour = $checkoutDate->format('H'); // Get the hour of checkout time

            if ($checkoutHour >= 13) {
                // Calculate additional charge for extended time
                $extraCharge = ($checkoutHour - 12) * 150; // 150 per hour after 12:00 noon
            }

            // Format the output charges
            $extraGuests = $person1 - $personcount;

            // If there are extra guests, charge ₱500 per extra person
            if ($extraGuests > 0) {
                $personCharge = $extraGuests * 500; // ₱500 per extra guest
            } else {
                $personCharge = 0; // No charge if no extra guests
            }            
            
            $extendedTimeCharge = number_format($extraCharge, 2);
            ?>

            <p><strong>Person Charge:</strong> ₱ <?php echo $personCharge; ?></p>
            <p><strong>Extended Time Charge:</strong> ₱ <?php echo $extendedTimeCharge; ?></p>

            <p><strong>Total Nights:</strong> <?php echo $nights; ?> night(s)</p>

            <p><strong>Room Price (per day):</strong> ₱ <?php echo htmlspecialchars(number_format($view1['price'], 2)); ?></p>

            <?php 
            $basePrice = ($view1['price'] * $nights) + $personCharge + $extendedTimeCharge;
            $promoDiscount = (float)($view1['promo_name'] ?? 0); 
            $discountValue = (float)($view1['discount_value'] ?? 0); 
            $downpayment = (float)($view1['downpayment'] ?? 0);
            
            // Apply discount if available
            $discountAmount = ($discountValue > 0) ? ($basePrice * $discountValue / 100) : 0;
            $balance = ($basePrice - $discountAmount) - ($downpayment + $promoDiscount);
            $revenue = ($basePrice - $discountAmount) - $promoDiscount;
            ?>

            <p>-----------------------------------------------------------------</p>
            <p><strong>Total:</strong> ₱ <?php echo htmlspecialchars(number_format($basePrice, 2)); ?></p><br><br>

            <p><strong>Discount:</strong> <?php echo htmlspecialchars($discountValue); ?>%</p>
            <p><strong>Promo:</strong> ₱ <?php echo htmlspecialchars(number_format($promoDiscount, 2)); ?></p>
            <p><strong>Downpayment:</strong> ₱ <?php echo htmlspecialchars(number_format($downpayment, 2)); ?></p>
            <p>-----------------------------------------------------------------</p>
            <p><strong>Total Balance:</strong> ₱ <?php echo htmlspecialchars(number_format($balance, 2)); ?></p><br> 



            <form method="POST" action="">
                <input type="hidden" name="revenue" value="<?php echo $downpayment?>">
                <input type="hidden" name="balance" value="<?php echo $balance?>">
                <input type="hidden" name="checkinID" value="<?php echo $checkinID ?>">
                <button type="submit" name="confirm" class="btn">checkout</button>
            </form>

        <?php else: ?>
            <p>No check-in details available.</p>
        <?php endif; ?>
    </div>
</div>


















<?php 
$checkinDetails = null; 

if (isset($_POST['view'])) {
    $checkinID = (int)$_POST['checkinID'];
    $detailsSql = "SELECT c.*, c.person as person1, r.*, r.person as personcount, s.*, p.discount as promo_name, d.amount as discount_value, g.* 
                   FROM checkin c 
                   LEFT JOIN roomcat r ON c.roomnum = r.roomnumber
                   LEFT JOIN service s ON c.checkinID = s.checkinID
                   LEFT JOIN promo p on c.promo = p.ID
                   LEFT JOIN discount d on c.discount = d.ID
                   LEFT JOIN guest g on c.guest_id = g.id
                   WHERE c.checkinID = $checkinID";
    
    $detailsResult = $conn->query($detailsSql);
    
    if ($detailsResult->num_rows > 0) {
        $checkinDetails = $detailsResult->fetch_assoc();
    } else {
        echo "<script>alert('No details found.');</script>";
    }
}
?>

<div id="viewModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span id="close" class="close" onclick="closeModal('viewModal')" >&times;</span>
        <h2>Golden Grain Hotel</h2>
        <p>----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</p><br><br>

        <p><strong>---------Room Details------------</strong></p>
        <?php if ($checkinDetails): ?>
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($checkinDetails['roomnum']); ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y'); ?></p>
            <p><strong>Guest Name:</strong> <?php echo ($checkinDetails['first_name'] ?? 'Not') . ' ' . ($checkinDetails['last_name'] ?? 'Available'); ?></p><br>

            <p><strong>-------Summary Report ---------------</strong></p>
            <p><strong>Check-In:</strong> <?php echo htmlspecialchars($checkinDetails['arrival']); ?></p>
            <p><strong>Check-Out:</strong> <?php echo htmlspecialchars($checkinDetails['depart']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($checkinDetails['name']); ?></p>
            <p><strong>Person number:</strong> <?php echo htmlspecialchars($checkinDetails['person1']); ?></p>

            <?php
            // Assuming $checkinDetails is available and contains 'arrival' and 'depart'
            $arrivalDate = strtotime($checkinDetails['arrival']); 
            $personcount = (int)htmlspecialchars($checkinDetails['personcount']); 
            $person1 = (int)htmlspecialchars($checkinDetails['person1']); 
            $todayDate = date('Y-m-d'); 
            $checkoutDate = strtotime($checkinDetails['depart']); 

            // Convert today's date and depart date to DateTime objects for comparison
            $todayDateWithTime = new DateTime('now', new DateTimeZone('Asia/Manila')); // Use Asia timezone
            $checkoutDateWithTime = new DateTime($checkinDetails['depart'], new DateTimeZone('Asia/Manila'));

            // Determine the correct checkout date
            if ($todayDateWithTime > $checkoutDateWithTime) {
                // If today's date is later than departure date, use today's date for checkout date
                $checkoutDate = $todayDateWithTime;
            } else {
                // Otherwise, use the original depart date from $checkinDetails
                $checkoutDate = $checkoutDateWithTime;
            }

            // Calculate the number of nights from arrival to checkout
            $checkinDate = new DateTime($checkinDetails['arrival'], new DateTimeZone('Asia/Manila')); // Arrival date
            $interval = $checkinDate->diff($checkoutDate); // Get the difference in days
            $nights = $interval->days; // Number of nights

            // Check if the checkout time is after 13:00 (1 PM) and calculate the extra charge
            $extraCharge = 0;
            $checkoutHour = $checkoutDate->format('H'); // Get the hour of checkout time

            if ($checkoutHour >= 13) {
                // Calculate additional charge for extended time
                $extraCharge = ($checkoutHour - 12) * 150; // 150 per hour after 12:00 noon
            }

            // Format the output charges
            $extraGuests = $person1 - $personcount;

            // If there are extra guests, charge ₱500 per extra person
            if ($extraGuests > 0) {
                $personCharge = $extraGuests * 500; // ₱500 per extra guest
            } else {
                $personCharge = 0; // No charge if no extra guests
            }            
            
            $extendedTimeCharge = number_format($extraCharge, 2);
            ?>

            <p><strong>Person Charge:</strong> ₱ <?php echo $personCharge; ?></p>
            <p><strong>Extended Time Charge:</strong> ₱ <?php echo $extendedTimeCharge; ?></p>

            <p><strong>Total Nights:</strong> <?php echo $nights; ?> night(s)</p>

            <p><strong>Room Price (per day):</strong> ₱ <?php echo htmlspecialchars(number_format($checkinDetails['price'], 2)); ?></p>

            <?php 
            $basePrice = ($checkinDetails['price'] * $nights) + $personCharge + $extendedTimeCharge;
            $promoDiscount = (float)($checkinDetails['promo_name'] ?? 0); 
            $discountValue = (float)($checkinDetails['discount_value'] ?? 0); 
            $downpayment = (float)($checkinDetails['downpayment'] ?? 0);
            
            // Apply discount if available
            $discountAmount = ($discountValue > 0) ? ($basePrice * $discountValue / 100) : 0;
            $balance = ($basePrice - $discountAmount) - ($downpayment + $promoDiscount);
            $revenue = ($basePrice - $discountAmount) - $promoDiscount;
            ?>

            <p>-----------------------------------------------------------------</p>
            <p><strong>Total:</strong> ₱ <?php echo htmlspecialchars(number_format($basePrice, 2)); ?></p><br><br>

            <p><strong>Discount:</strong> <?php echo htmlspecialchars($discountValue); ?>%</p>
            <p><strong>Promo:</strong> ₱ <?php echo htmlspecialchars(number_format($promoDiscount, 2)); ?></p>
            <p><strong>Downpayment:</strong> ₱ <?php echo htmlspecialchars(number_format($downpayment, 2)); ?></p>
            <p>-----------------------------------------------------------------</p>
            <p><strong>Total Balance:</strong> ₱ <?php echo htmlspecialchars(number_format($balance, 2)); ?></p><br> 

            <form method="POST" action="">
                <input type="hidden" name="revenue" value="<?php echo $revenue?>">
                <input type="hidden" name="baseprice" value="<?php echo $basePrice?>">
                <input type="hidden" name="checkout" value="<?php echo $checkoutDate->format('Y-m-d H:i:s')?>">
                <input type="hidden" name="night" value="<?php echo $nights?>">
                <input type="hidden" name="discount" value="<?php echo $discountValue?>">
                <input type="hidden" name="promo" value="<?php echo $promoDiscount?>">
                <input type="hidden" name="downpayment" value="<?php echo $downpayment?>">
                <input type="hidden" name="amount1" value="<?php echo $balance?>">
                <input type="hidden" name="roomnum" value="<?php echo htmlspecialchars($checkinDetails['roomnum']);?>">
                <input type="hidden" name="time" value="<?php echo $extendedTimeCharge?>">



                <strong><p>Mode of Payment:</p></strong>            
                <select class="jb" id="room" name="mop" required>
                    <option value="">Select</option>
                    <?php
                        @include 'config.php';

                        $sql = "SELECT * FROM mop where bit = 1";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $name = htmlspecialchars($row['name']);
                                $mop_id = htmlspecialchars($row['ID']);
                                echo "<option value='$mop_id'>$name</option>";
                            }
                        }
                    ?>
                </select>
                <p><strong>Enter Amount:</strong> <input type="number" name="amount" placeholder="₱" step="0.01" required></p>
                <p><strong>Reference Number:</strong> <input type="number" name="ref" placeholder=" -For Online Payment" step="0.01"></p>

                <input type="hidden" name="checkinID" value="<?php echo $checkinID ?>">
                <button type="submit" name="receipt" class="btn">Submit</button>
            </form>

        <?php else: ?>
            <p>No check-in details available.</p>
        <?php endif; ?>
    </div>
</div>






















<!-- RECEIPT -->
<?php
$receipt = null;   
if (isset($_POST['receipt'])) {
    $checkinID = (int)$_POST['checkinID'];
    $amounts = (int)$_POST['amount'];
    $checkout = (int)$_POST['checkout'];
    $night = (int)$_POST['night'];
    $revenue = (int)$_POST['revenue'];
    $discount = (int)$_POST['discount'];
    $promo = (int)$_POST['promo'];
    $downpayment = (int)$_POST['downpayment'] ?? 0;
    $baseprice = (int)$_POST['baseprice'];
    $ref = (int)$_POST['ref'];
    $mop = (int)$_POST['mop'];
    $amount1 = (int)$_POST['amount1'];
    $roomnum = (int)$_POST['roomnum'];
    $time = (int)$_POST['time'];


    if ($amount1 == 0) {
        echo "<script>alert('This Room $roomnum has $amount1 balance!'); window.location.back();</script>";
        exit;
    }

    if ($amount1 > $amounts || $amounts <= 0) {
        echo "<script>alert('Please enter a valid number'); window.location.back();</script>";
        exit;
    }

    
        
    

    $detailsSql = "SELECT c.*, c.person as person1, r.*, r.person as personcount, s.*, p.discount as promo_name, d.amount as discount_value, g.*
                   FROM checkin c
                   LEFT JOIN roomcat r ON c.roomnum = r.roomnumber
                   LEFT JOIN service s ON c.checkinID = s.checkinID
                   LEFT JOIN promo p ON c.promo = p.ID
                   LEFT JOIN discount d ON c.discount = d.ID
                   LEFT JOIN guest g ON c.guest_id = g.id
                   WHERE c.checkinID = $checkinID";

    $detailsResult = $conn->query($detailsSql);

    if ($detailsResult->num_rows > 0) {
        $total = ($downpayment + $amount1);
        $updateamount = "UPDATE checkin SET downpayment = '$total', reference = '$ref', mop = '$mop'  WHERE checkinID = '$checkinID'";
        $conn->query($updateamount);
        $receipt = $detailsResult->fetch_assoc();

    } else {
        echo "<script>alert('No details found.');</script>";
    }

}
?>

<div id="edit" class="modal" style="display: none;">
    <div class="modal-content" style="width: 300px; font-family: arial; font-size: 18px; line-height: 1.4; text-align: left;">
        <span class="close" onclick="closeModal()" style="display: none;">&times;</span>

        <strong><p style="text-align: center; margin: 0;">Golden Grain Hotel</p></strong>
        <p style="text-align: center; margin: 0;">-------------------------------</p>

        
        <p style="margin: 5px 0;">Room Number: <?php echo htmlspecialchars($receipt['roomnum']); ?></p>
        <p style="margin: 5px 0;">Date: <?php echo date('F j, Y'); ?></p>

        <p style="margin: 5px 0;">Guest: <?php echo htmlspecialchars($receipt['first_name'] . ' ' . $receipt['last_name']); ?></p>
        <p style="text-align: center; margin: 0;">--------------------------------</p>

        <p style="margin: 5px 0;">Check-In: <span style="float: right;"><?php echo date('m - d - Y', strtotime($receipt['arrival'])); ?></span></p>
        <p style="margin: 5px 0;">Check-Out: <span style="float: right;"><?php echo date('m - d - Y', strtotime($checkout)); ?></span></p>

        <p style="margin: 5px 0;">Room Type: <span style="float: right;"><?php echo htmlspecialchars($receipt['name']); ?></span></p>

        <p style="margin: 5px 0;">Person Extra Fee: <span style="float: right;">₱ <?php echo number_format($receipt['person1']*500, 2); ?></span></p>
        <p style="margin: 5px 0;">No. of Nights: <span style="float: right;"><?php echo $night > 0 ? $night : 0; ?></span></p>

        <p style="margin: 5px 0;">Extended Time(150/hr): <span style="float: right;">₱ <?php echo number_format($time, 2); ?></span></p>

       
        <p style="margin: 5px 0;">Room Price (per day): <span style="float: right;">₱ <?php echo number_format($receipt['price'], 2); ?></span></p>
        <p style="text-align: center; margin: 0;">---------------------------------</p>
        <p style="margin: 5px 0;">Total: <span style="float: right;">₱ <?php echo number_format($baseprice, 2); ?></span></p><br><br>

        <p style="margin: 5px 0;">Discount: <span style="float: right;">- <?php echo $discount; ?>%</span></p>
        <p style="margin: 5px 0;">Promo: <span style="float: right;">- ₱ <?php echo number_format($promo, 2); ?></span></p>
        <p style="margin: 5px 0;">Downpayment: <span style="float: right;">- ₱ <?php echo number_format($downpayment, 2); ?></span></p>
        <p style="text-align: center; margin: 0;">----------------------------------------</p>
        <strong><p style="margin: 5px 0;">Total Amount: </strong><span style="float: right;">₱ <?php echo number_format($amount1, 2); ?></span></p><br><br>

        <?php 
        $discountAmount = ($discount > 0) ? ($baseprice * $discount / 100) : 0;
        $balance = ($baseprice - $discountAmount) - ($downpayment + $promo +$amount1);
        $change = ($amounts - $amount1);
        ?>
        <p style="margin: 5px 0;">Cash: <span style="float: right;">₱ <?php echo number_format($amounts, 2); ?></span></p>
        <p style="margin: 5px 0;">Change: <span style="float: right;">₱ <?php echo number_format($change, 2); ?></span></p>
        <p style="text-align: center; margin: 0;">-------------------------------</p>
        <p style="margin: 5px 0;">Total Balance: <span style="float: right;">₱ <?php echo number_format($balance, 2); ?></span></p><br><br>

        <strong><p style="text-align: center; margin: 0;">Thank you for staying with us!</p></strong>
        <p style="text-align: center; margin: 0;">--------------------------------</p>
        
    </div>
</div>








<script>

// Function to open the modal
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

// Function to close the modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}


// Close the modal when clicking outside the modal content
window.onclick = function(event) {
    var modal = document.getElementById('edit');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}


// Automatically open modal when check-in details are available
<?php if ($checkinDetails): ?>
    openModal('viewModal');
<?php endif; ?>

<?php if ($view1): ?>
    openModal('view1');
<?php endif; ?>

<?php if ($receipt): ?>
    openModal('edit');
    window.print();
<?php endif; ?>

</script>

</body>
</html>
