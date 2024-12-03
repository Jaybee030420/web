

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenGrainHotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
    <link rel="stylesheet" href="room.css">
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
            <li><a href="check-in.php" class=""><span classstyles='icon-field'><i class="fa fa-sign-in-alt"></i>Check - in</span></a></li>
            <li><a href="check-out.php" class=""><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="room.php" class="cat"><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>Room Availability</h2>
        <div style="overflow-x: auto;">
            <table class="room-table">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Availability</th>
                        <th>Ratings (%)</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    @include 'config.php';
    
    // Step 1: Get total ratings for all rooms
$totalRatingsQuery = "SELECT SUM(rating) AS total_ratings FROM roomcat";
$totalRatingsResult = mysqli_query($conn, $totalRatingsQuery);
$totalRatingsRow = mysqli_fetch_assoc($totalRatingsResult);

$totalRatings = $totalRatingsRow['total_ratings'] ?? 0; // Get total ratings for all rooms

// Step 2: Get the individual room details
$sql = "SELECT rc.roomnumber, rc.name, rc.roombit, rc.rating, rc.bit
        FROM roomcat rc
        ORDER BY rc.roomnumber";

$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>Room ' . $row['roomnumber'] . '</td>';
        echo '<td>' . $row['name'] . '</td>';

        // Determine room availability status based on roombit and bit
        $availability = ''; // Initialize availability variable
        $color = ''; // Initialize color variable

        // Logic to determine availability
        if ($row['bit'] == 0) {
            // Maintenance status
            $availability = '-Maintenance-';
            $color = 'gray black';
        } elseif ($row['roombit'] == 0 && $row['bit'] == 1) {
            // Available
            $availability = 'Available';
            $color = 'green';
        } elseif ($row['roombit'] == 1 && $row['bit'] == 1) {
            // Not Available
            $availability = 'Not Available';
            $color = 'red';
        } else {
            // Default for unexpected values
            $availability = 'Unknown';
            $color = 'black';
        }

            // Output availability status with appropriate color
            echo '<td style="color: ' . $color . ';">' . $availability . '</td>';

            // Step 3: Calculate the percentage for the room based on total ratings
            if ($totalRatings > 0) {
                $percentage = ($row['rating'] / $totalRatings) * 100;
                echo '<td>' . round($percentage, 2) . '%</td>'; // Display calculated percentage
            } else {
                echo '<td>0%</td>'; // Handle case where there are no ratings at all
            }

            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No room categories found</td></tr>';
    }

    // Close connection
    $conn->close();
    ?>
</tbody>

            </table>
        </div>
    </div>

</body>
</html>
