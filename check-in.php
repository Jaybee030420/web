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


date_default_timezone_set('Asia/Manila');

$checkinData = null; 



$showModal = false;
$personCount = 0;
if (isset($_POST['setPersonCount'])) {
    $discount = $_POST['discount'];
    $promo = $_POST['promo'];
    $roomnumber = $_POST['roomnumber'];
    $personCount = (int)$_POST['person'];
    $guest = (int)$_POST['guest'];
    $id = (int)$_POST['discountid'];


    if ($guest != 0) {
        $guestCheckSql = "SELECT * FROM guest WHERE id = '$guest'";
        $guestCheckResult = $conn->query($guestCheckSql);
        
        if ($guestCheckResult->num_rows == 0) {
            echo "<script>alert('Guest ID does not exist.'); window.history.window.location.href = 'check-in.php';</script>";
            $showModal = false;
            exit; 

        }
    }



    $showModal = true; 

    
}


$checkinDetails = null; 

if (isset($_POST['view'])) {
    $checkinId = (int)$_POST['checkinId'];
    $detailsSql = "SELECT checkin.*, roomcat.name AS room_type, 
                          GROUP_CONCAT(guests.name SEPARATOR ', ') AS guest_names, 
                          GROUP_CONCAT(guests.contact SEPARATOR ', ') AS guest_contacts,
                          mop.name AS mop_name, promo.name AS promo_name, discount.name AS discount_name
                   FROM checkin 
                   INNER JOIN roomcat ON checkin.roomnum = roomcat.roomnumber 
                   LEFT JOIN guests ON checkin.checkinID = guests.checkinID 
                   LEFT JOIN mop ON checkin.mop = mop.ID 
                   LEFT JOIN promo ON checkin.promo = promo.ID 
                   LEFT JOIN discount ON checkin.discount = discount.ID 
                   WHERE checkin.checkinID = $checkinId 
                   GROUP BY checkin.checkinID";
    
    $detailsResult = $conn->query($detailsSql);
    
    if ($detailsResult->num_rows > 0) {
        $checkinDetails = $detailsResult->fetch_assoc();
    } else {
        echo "<script>alert('No details found.');</script>";
    }
}






$void = null; 

if (isset($_POST['void'])) {
    $checkinId = (int)$_POST['checkinId'];
    $detailsSql = "SELECT checkin.*, roomcat.name AS room_type, 
                          GROUP_CONCAT(guests.name SEPARATOR ', ') AS guest_names, 
                          GROUP_CONCAT(guests.contact SEPARATOR ', ') AS guest_contacts,
                          mop.name AS mop_name, promo.name AS promo_name, discount.name AS discount_name
                   FROM checkin 
                   INNER JOIN roomcat ON checkin.roomnum = roomcat.roomnumber 
                   LEFT JOIN guests ON checkin.checkinID = guests.checkinID 
                   LEFT JOIN mop ON checkin.mop = mop.ID 
                   LEFT JOIN promo ON checkin.promo = promo.ID 
                   LEFT JOIN discount ON checkin.discount = discount.ID 
                   WHERE checkin.checkinID = $checkinId 
                   GROUP BY checkin.checkinID";
    
    $detailsResult = $conn->query($detailsSql);
    
    if ($detailsResult->num_rows > 0) {
        $void = $detailsResult->fetch_assoc();
    } else {
        echo "<script>alert('No details found.');</script>";
    }
}






// Sanitize form data
if (isset($_POST['book'])) {
    $arrival = date('Y-m-d H:i:s');
    $depart = date('Y-m-d H:i:s', strtotime('+1 day'));
    $discount = isset($_POST['discount']) ? (int)$_POST['discount'] : null;
    $promo = isset($_POST['promo']) ? (int)$_POST['promo'] : null;
    $roomnumber = isset($_POST['roomnumber']) ? $conn->real_escape_string($_POST['roomnumber']) : '';
    $personCount = isset($_POST['person']) ? (int)$_POST['person'] : 1;
    $guest = isset($_POST['guest']) ? (int)$_POST['guest'] : 0;
    $discountid = isset($_POST['discountid']) ? (int)$_POST['discountid'] : 0;

    // Check room availability
    $roomCheckSql = "SELECT * FROM roomcat WHERE roomnumber = '$roomnumber' AND roombit = 0 AND bit = 1";
    $roomCheckResult = $conn->query($roomCheckSql);

   
    

    if ($roomCheckResult->num_rows == 0) {
        echo "<script>alert('Selected room is not available for check-in.'); window.history.back();</script>";
        exit;
    }

    if ($guest == 0) {
        $sql = "INSERT INTO checkin (roomnum, arrival, depart, discount, promo, person, discountid) 
                VALUES ('$roomnumber', '$arrival', '$depart', '$discount', '$promo', '$personCount', '$discountid')";
    } else {
        $sql = "INSERT INTO checkin (roomnum, arrival, depart, discount, promo, person, guest_id, discountid) 
                VALUES ('$roomnumber', '$arrival', '$depart', '$discount', '$promo', '$personCount', '$guest', '$discountid')";
    }

    if ($conn->query($sql) === TRUE) {
        $checkinID = $conn->insert_id;

        $updateRoomSql = "UPDATE roomcat SET roombit = 1 WHERE roomnumber = '$roomnumber'";
        if (!$conn->query($updateRoomSql)) {
            echo "Error updating room status: " . $conn->error;
            exit;
        }

        for ($i = 1; $i <= $personCount; $i++) {
            if (isset($_POST["name_$i"]) && isset($_POST["contact_$i"])) {
                $name = $conn->real_escape_string($_POST["name_$i"]);
                $contact = $conn->real_escape_string($_POST["contact_$i"]);

                $guestSql = "INSERT INTO guests (checkinID, name, contact) 
                             VALUES ('$checkinID', '$name', '$contact')";

                if (!$conn->query($guestSql)) {
                    echo "Error: " . $conn->error;
                    exit;
                }
            }
        }

        echo "<script>alert('Check-in successful!'); window.location.href = 'check-in.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}




if (isset($_POST['reserve'])) {
    $arrival = $conn->real_escape_string($_POST['arrival']);
    $guest_id = $conn->real_escape_string($_POST['id']); 
    $depart = $conn->real_escape_string($_POST['depart']);
    $roomtype = $conn->real_escape_string($_POST['roomtype']);
    $amount = $conn->real_escape_string($_POST['amount']);
    $promo = $conn->real_escape_string($_POST['promo']);

    $mop = (int)$_POST['mop'];  
    $reference = $conn->real_escape_string($_POST['reference']);

    $currentDate = date("Y-m-d");

    if ($arrival < $currentDate) {
        echo "<script>alert('Check-in date cannot be in the past.'); window.history.back();</script>";
        exit;
    }

    if ($depart <= $arrival) {
        echo "<script>alert('Check-out date must be after the check-in date.'); window.history.back();</script>";
        exit;
    }

    $guestCheckSql = "SELECT id FROM guest WHERE id = ?";
    if ($stmt = $conn->prepare($guestCheckSql)) {
        $stmt->bind_param("i", $guest_id);  
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            echo "<script>alert('Guest ID does not exist. Please check the ID.'); window.history.back();</script>";
            exit;
        }

        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
        exit;
    }

    // Now proceed with the reservation insert
    $reservationSql = "INSERT INTO bookings (guest_id, roomtype, arrival, depart, amount, mop, reference, status, promo) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, 'success', ?)";

    if ($stmt = $conn->prepare($reservationSql)) {
        // Bind parameters to the query
        $stmt->bind_param("isssisis", $guest_id, $roomtype, $arrival, $depart, $amount, $mop, $reference, $promo);

        // Execute the query
        if ($stmt->execute()) {
            echo "<script>alert('Reservation successful!'); window.location.href = 'Book.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}




if (isset($_POST['newguest'])) {
    @include 'config.php';


    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];  
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password']; 
    $guest_id = $_POST['guest_id'];
    $bit= 1;

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO guest (id, first_name, last_name, gender, birthday, address, email, phone, password, bit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssssi", $guest_id, $first_name, $last_name, $gender, $birthday, $address, $email, $phone, $hashed_password, $bit);

        if ($stmt->execute()) {
            echo "<script>alert('Created Successfully'); window.location.href = 'check-in.php';</script>";
        } else {
            echo "<script>alert('Something went wrong'); window.location.href = 'check-in.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Something went wrong'); window.location.href = 'check-in.php';</script>";
    }

  
}





if (isset($_POST['void1'])) {  // Check if the button 'void1' was pressed
    $idNumber = $_POST['key'];
    $roomnum = $_POST['roomnum'];

    $idNumber = htmlspecialchars($idNumber, ENT_QUOTES, 'UTF-8');

    try {
        $sql_check_user = "SELECT position FROM users WHERE voidid = ?";
        $stmt_check_user = $conn->prepare($sql_check_user);
        $stmt_check_user->bind_param("i", $idNumber); 

        if ($stmt_check_user->execute()) {
            $result = $stmt_check_user->get_result();

            if ($result->num_rows > 0) {

                $user = $result->fetch_assoc();
                $position = $user['position'];

                if ($position === 'Manager') {


                    // Update check-in status
                    $sql_checkin = "UPDATE checkin SET status = 'void' WHERE roomnum = ? AND status = 'checkin'";
                    $stmt_checkin = $conn->prepare($sql_checkin);
                    $stmt_checkin->bind_param("i", $roomnum);
                    if (!$stmt_checkin->execute()) {
                        throw new Exception('Error updating check-in status: ' . $stmt_checkin->error);
                    }

                    // Update room availability
                    $sql_room = "UPDATE roomcat SET roombit = 0 WHERE roomnumber = ?";
                    $stmt_room = $conn->prepare($sql_room);
                    $stmt_room->bind_param("i", $roomnum);
                    if (!$stmt_room->execute()) {
                        throw new Exception('Error updating room availability: ' . $stmt_room->error);
                    }

                    echo "<script>alert('Status updated to \"void\" and room marked as unavailable (roombit = 0) successfully.');</script>";

                    
                } else {
                    // If the user is not a manager, show an error message
                    echo "<script>alert('You are not allowed to proceed. Only managers can perform this action.');</script>";
                }
            } else {
                // Use JavaScript alert to show error message if ID is not found
                echo "<script>alert('Incorrect ID number. Please try again.');</script>";
            }
        } else {
            // If the SQL query failed
            throw new Exception('Error executing query. Please try again later.');
        }
    } catch (Exception $e) {
        // Catch and display the exception
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
            <li><a href="Dashboard.php" class=""><span class='icon-field'><i class="fa fa-book"></i>Dashboard</span></a></li>
            <li><a href="Book.php" class=""><span class='icon-field'><i class="fa fa-book"></i>Booking List</span></a></li>
            <li><a href="extra.php" class=""><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Service</span></a></li>
            <li><a href="check-in.php" class="cat"><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Check - in</span></a></li>
            <li><a href="check-out.php" class=""><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="room.php" class=""><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="card">
        <h2>Check - in List</h2>

        
<!------------------Button for transaction -->
        <button id="checkinBtn" class="btn" onclick="openModal('myModal')">Check-In</button>
        <button id="checkinBtn" class="btn" onclick="openModal('myModal1')">Reserve</button>
        <button id="checkinBtn" class="btn" onclick="openModal('myModal2')">Guest</button>
<!-------------------->


<!------------------Search -->
        <div class="search-container" style="margin-left: 115vh;">
            <form action="" method="post">
                <label for="month">Month:</label>
                <select name="month" required >
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $monthName = date("F", mktime(0, 0, 0, $m, 1));
                        echo "<option value='$m'>$monthName</option>";
                    }
                    ?>
                </select>
                
                <label for="year">Year:</label>
                <select name="year" required>
                    <?php
                    $currentYear = date("Y");
                    for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
                        echo "<option value='$y'>$y</option>";
                    }
                    ?>
                </select>
        
        <input type="submit" class="btn" name="searchBtn" value="Search">
    </form>
</div>
<!-------------------->














<!------------------Table---->
<table class="room-table">
    <thead>
        <tr>
            <th>Room Number</th>
            <th>Room Type</th>
            <th>Check-in Date</th>
            <th>Check-out Date</th>
            <th>No. of Persons</th>
            <th>Operations</th>
        </tr>
    </thead>
    <tbody>
    <?php
    @include 'config.php';

    $sql = "SELECT checkin.*, roomcat.name AS room_type, roomcat.roomnumber, 
                   COUNT(guests.guestID) AS num_persons 
            FROM checkin 
            INNER JOIN roomcat ON checkin.roomnum = roomcat.roomnumber 
            LEFT JOIN guests ON checkin.checkinID = guests.checkinID 
            WHERE roomcat.roombit != 0 and checkin.status = 'checkin'";

    if (isset($_POST['searchBtn'])) {
        $month = (int)$_POST['month'];
        $year = (int)$_POST['year'];
        $sql .= " AND (MONTH(arrival) = $month AND YEAR(arrival) = $year)";
    }

    $sql .= " GROUP BY checkin.checkinID ORDER BY checkin.checkinID DESC LIMIT 10";
    $result = $conn->query($sql);

    if ($result === false) {
        echo '<tr><td colspan="6">Error executing query: ' . $conn->error . '</td></tr>';
    } else {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['roomnumber']); ?></td>
                    <td><?php echo htmlspecialchars($row['room_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['arrival']); ?></td>
                    <td><?php echo htmlspecialchars($row['depart']); ?></td>
                    <td><?php echo htmlspecialchars($row['num_persons']); ?></td>
                    <td>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                            <input type="hidden" name="checkinId" value="<?php echo htmlspecialchars($row['checkinID']); ?>">
                            <input type="submit" class="btn" name="void" value="edit" onclick="openModal('viewModal1')">
                            <input type="submit" class="btn" name="view" value="View" onclick="openModal('viewModal')">
                        </form>
                    </td>


                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="6">No records found for the selected month and year</td></tr>';
        }
    }
    ?>
    </tbody>
</table>

    </div>
</div>









<!------------------Person----->
<div id="personModal" class="modal" style="<?php echo $showModal ? 'display: block;' : 'display: none;'; ?>">
    <?php if ($personCount > 0): ?>
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Golden Grain Hotel</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

            <input type="hidden" name="discount" value="<?php echo htmlspecialchars($discount); ?>">
            <input type="hidden" name="promo" value="<?php echo htmlspecialchars($promo); ?>">
            <input type="hidden" name="roomnumber" value="<?php echo htmlspecialchars($roomnumber); ?>">
            <input type="hidden" name="person" value="<?php echo $personCount; ?>">
            <input type="hidden" name="guest" value="<?php echo $guest; ?>">
            <input type="hidden" name="discountid" value="<?php echo $discountid; ?>">


            <?php for ($i = 1; $i <= $personCount; $i++): ?>
                <h3>Person <?php echo $i; ?></h3>
                <label>Name:</label><br>
                <input type="text" name="name_<?php echo $i; ?>" oninput="validateLettersOnly(this)" pattern="[A-Za-z]+" title="Only letters are allowed." required ><br>

                

                <label>Contact:</label><br>
                <input type="number" name="contact_<?php echo $i; ?>" oninput="validateNumbersOnly(this)" required ><br><br>
                <?php endfor; ?>

            <input type="submit" class="btn" name="book" value="Check-in">
        </form>
    </div>
    <?php endif; ?>
</div>



















<!------------------Check-in -->
<div id="myModal" class="modal" style="display: none;">
<div class="modal-content">
    
    <span class="close" style="margin-left: 165vh;" onclick="closeModal()">&times;</span>

        <h2>Check-In Form</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">

        <label>User ID:</label><br>
        <input type="number" name="guest" placeholder="Optional for checkin"><br><br>
            
            <label>Discount:</label><br>
            <select class="jb" id="room" name="discount">
                <option value="0">Select</option>
                <?php
        
                    @include 'config.php';

              
                    $sql = "SELECT * FROM discount where bit = 1";
                    $result = $conn->query($sql);

                    // Check if there are any rows returned
                    if ($result->num_rows > 0) {
                        // Loop through each row and output option tags
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $discount_id = htmlspecialchars($row['ID']);
                            echo "<option value='$discount_id'>$name</option>";
                        }
                    }
                ?>
            </select><br><br>
            <label>ID NO. </label><br>
            <input type="number" name="discountid" placeholder="PWD ID, SENIOR ID....." min="1" ><br><br>

            <label>Promo:</label><br>            
            <select class="jb" id="room" name="promo">
            <option value="0">Select</option>
                <?php
                    // Include database configuration
                    @include 'config.php';

                    // Query to fetch unique names from database
                    $sql = "SELECT * FROM promo where bit = 1";
                    $result = $conn->query($sql);

                    // Check if there are any rows returned
                    if ($result->num_rows > 0) {
                        // Loop through each row and output option tags
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $promo_id = htmlspecialchars($row['ID']);
                            echo "<option value='$promo_id'>$name</option>";
                        }
                    }else{
                        echo "<option value = '0' >No Promo Available</option>";
                    }
                ?>
            </select><br><br>


            <label>Room number:</label><br>            
            <select class="jb" id="room" name="roomnumber" required>
            <option value="">Select</option>
                <?php
                    
                    @include 'config.php';

                    $sql = "SELECT * FROM roomcat where bit = 1 and roombit = 0";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $roomnumber = htmlspecialchars($row['roomnumber']);
                            echo "<option value='$roomnumber'>$name( $roomnumber)</option>";
                        }
                    }
                ?>
            </select><br><br>

            <label>Person Number</label><br>
            <input type="number" name="person" min="1" required placeholder="Enter no. of person"><br><br>

            <input type="submit" class="btn" name="setPersonCount" value="Submit">

        </form>
    </div>
</div>




















<!-- Reservation Form -->
<div id="myModal1" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Reservation Form</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

            <label>User ID:</label><br>
            <input type="number" name="id" required ><br><br>
            
            <label>Check In:</label><br>
            <input type="date" name="arrival" required><br><br>

            <label>Check Out:</label><br>
            <input type="date" name="depart" required><br><br>

            <label>Mode of Payment:</label><br>            
            <select class="jb" id="mop" name="mop" required>
            <option value="">Select</option>
                <?php
                    include 'config.php';
                    $sql = "SELECT * FROM mop WHERE bit = 1";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $mop_id = htmlspecialchars($row['ID']);
                            echo "<option value='$mop_id'>$name</option>";
                        }
                    }
                ?>
            </select><br><br>

            <label>Room Type:</label><br>            
            <select class="jb" id="roomtype" name="roomtype" required>
            <option value="">Select</option>
                <?php
                    include 'config.php';
                    $sql = "SELECT * FROM roomcat WHERE bit = 1";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $price = htmlspecialchars($row['price']);
                            echo "<option value='$name'>$name (PHP $price)</option>";
                        }
                    }
                ?>
            </select><br><br>

            <label>Promo:</label><br>            
            <select class="jb" id="room" name="promo">
            <option value="">Select</option>
                <?php
                    // Include database configuration
                    @include 'config.php';

                    // Query to fetch unique names from database
                    $sql = "SELECT * FROM promo where bit = 1";
                    $result = $conn->query($sql);

                    // Check if there are any rows returned
                    if ($result->num_rows > 0) {
                        // Loop through each row and output option tags
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['name']);
                            $promo_id = htmlspecialchars($row['ID']);
                            echo "<option value='$promo_id'>$name</option>";
                        }
                    }else{
                        echo "<option value = '0' >No Promo Available</option>";
                    }
                ?>
            </select><br><br>

            <label>Reference Number:</label><br>
            <input type="number" name="reference"><br><br>

            <label>Amount:</label><br>
            <input type="number" name="amount" required ><br><br>


            <input type="submit" class="btn" name="reserve" value="Submit">
        </form>
    </div>
</div>












<!------------------GUest info (panagalan sang mga magbook or checkin)-->
<div id="myModal2" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Guest Account Registration</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            

            <label>First Name:</label><br>
            <input type="text" name="first_name" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" required><br><br>

            <label>Gender:</label><br>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select><br><br>

            <label>Birthday:</label><br>
            <input type="date" name="birthday" required><br><br>

            <label>Address:</label><br>
            <input type="text" name="address" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Phone Number:</label><br>
            <input type="tel" name="phone" required><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>

            <input type="hidden" name="guest_id" value="<?php echo uniqid('guest_'); ?>">

            <input type="submit" class="btn" name="newguest" value="Submit">
        </form>
    </div>
</div>





















<!------------------view-->
<div id="viewModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Check-In Details</h2>
        <?php if ($checkinDetails): ?>
            <table>
                <tr>
                    <th>Check-In</th>
                    <td><?php echo htmlspecialchars($checkinDetails['arrival']); ?></td>
                </tr>
                <tr>
                    <th>Check-Out</th>
                    <td><?php echo htmlspecialchars($checkinDetails['depart']); ?></td>
                </tr>
                <tr>
                    <th>Room Number</th>
                    <td><?php echo htmlspecialchars($checkinDetails['roomnum']); ?></td>
                </tr>
                <tr>
                    <th>Room Type</th>
                    <td><?php echo htmlspecialchars($checkinDetails['room_type']); ?></td>
                </tr>
                <tr>
                    <th>Guest Information</th>
                    <td>
                        <?php 
                        $names = explode(', ', $checkinDetails['guest_names']);
                        $contacts = explode(', ', $checkinDetails['guest_contacts']);
                        foreach ($names as $index => $name) {
                            echo  "Name:  " . htmlspecialchars($name) . " <br> Contact:  " . htmlspecialchars($contacts[$index]) . "<br> <br>";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Mode of Payment</th>
                    <td><?php echo htmlspecialchars($checkinDetails['mop_name']); ?></td>
                </tr>
                <tr>
                    <th>Promo</th>
                    <td><?php echo htmlspecialchars($checkinDetails['promo_name']); ?></td>
                </tr>
                <tr>
                    <th>Discount</th>
                    <td><?php echo htmlspecialchars($checkinDetails['discount_name']); ?></td>
                </tr>
                <tr>
                    <th>Downpayment</th>
                    <td><?php echo htmlspecialchars($checkinDetails['downpayment']); ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p>No check-in details available.</p>
        <?php endif; ?>
    </div>
</div>





<div id="voidModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Check-In Details</h2>
        <?php if ($void): ?>
            <table>
                <tr>
                    <th>Check-In</th>
                    <td><?php echo htmlspecialchars($void['arrival']); ?></td>
                </tr>
                <tr>
                    <th>Check-Out</th>
                    <td><?php echo htmlspecialchars($void['depart']); ?></td>
                </tr>
                <tr>
                    <th>Room Number</th>
                    <td><?php echo htmlspecialchars($void['roomnum']); ?></td>
                </tr>
                <tr>
                    <th>Room Type</th>
                    <td><?php echo htmlspecialchars($void['room_type']); ?></td>
                </tr>
                <tr>
                    <th>Guest Information</th>
                    <td>
                        <?php 
                        $names = explode(', ', $void['guest_names']);
                        $contacts = explode(', ', $void['guest_contacts']);
                        foreach ($names as $index => $name) {
                            echo  "Name:  " . htmlspecialchars($name) . " <br> Contact:  " . htmlspecialchars($contacts[$index]) . "<br> <br>";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Mode of Payment</th>
                    <td><?php echo htmlspecialchars($void['mop_name']); ?></td>
                </tr>
                <tr>
                    <th>Promo</th>
                    <td><?php echo htmlspecialchars($void['promo_name']); ?></td>
                </tr>
                <tr>
                    <th>Discount</th>
                    <td><?php echo htmlspecialchars($void['discount_name']); ?></td>
                </tr>
                <tr>
                    <th>Downpayment</th>
                    <td><?php echo htmlspecialchars($void['downpayment']); ?></td>
                </tr>
            </table>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="hidden" name="roomnum" value="<?php echo htmlspecialchars($void['roomnum']); ?>">

            <label> Enter ID Number:</label>
            <input type="number" name="key" required>

            <input type="submit" class="btn" name="void1" value="Submit">
        </form>
        <?php else: ?>
            <p>No check-in details available.</p>
        <?php endif; ?>
    </div>
</div>











<!------------------Person modal (script)-->
<script>

function closeModal() {
    document.getElementById('personModal').style.display = 'none';
}
<?php if ($showModal): ?>
    document.getElementById('personModal').style.display = 'block';
<?php endif; ?>
</script>



<!------------------Modal(script)-->
<script>
    document.getElementById("checkinBtn").onclick = function() {
    openModal('myModal');
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}

function closeModal() {
    var modals = document.querySelectorAll('.modal');
    modals.forEach(modal => modal.style.display = "none");
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal();
    }
}

// ----------------------View (script)
<?php if ($checkinDetails): ?>
    openModal('viewModal');
<?php endif; ?>

<?php if ($void): ?>
    openModal('voidModal');
<?php endif; ?>


</script>


</body>
</html>