
<?php
session_name('frontdesk');
session_start();

if (isset($_SESSION['username']) && isset($_SESSION['position'])) {

    $username = $_SESSION['username'];
    $position = $_SESSION['position'];
}else{
    header("Location: verify.php");  
}
?>



<?php


@include 'config.php';

if (isset($_POST['logout'])) {
    session_unset();  
    session_destroy();  
    header("Location: verify.php");  
    exit();  
}

// Function to calculate total available rooms
function getTotalAvailableRooms($conn) {
    $sql = "SELECT COUNT(*) AS total_available FROM roomcat WHERE roombit = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_available'];
}

// Function to calculate total not available rooms
function getTotalNotAvailableRooms($conn) {
    $sql = "SELECT COUNT(*) AS total_not_available FROM roomcat WHERE roombit = 1 OR roombit = 2";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_not_available'];
}

// Function to get room number with highest rating
function getTopRatedRoom($conn) {
    $sql = "SELECT roomnumber FROM roomcat ORDER BY rating DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['roomnumber'];
    } else {
        return 'N/A';
    }
}

function getTotalPending($conn) {
    $sql = "SELECT COUNT(*) AS totalpending FROM bookings where status = 'Pending'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['totalpending'];
}





// Calculate statistics
$totalAvailableRooms = getTotalAvailableRooms($conn);
$totalNotAvailableRooms = getTotalNotAvailableRooms($conn);
$topRatedRoom = getTopRatedRoom($conn);
$getTotalPending = getTotalPending($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenGrainHotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
    <link rel="stylesheet" href="Dashboard.css">
</head>
<body>  

<nav id="sidebar" class='mx-lt-5'>
    <div class="title">
        <h1 style="color:darkorange;">Golden Grain Hotel <p style="font-weight: 500; color:darkgoldenrod">frontdesk</p></h1>
    </div>
    <div class="sidebar-list">
        <ul>
            <li><a href="Dashboard.php" class="cat"><span class='icon-field'><i class="fa fa-book"></i>Dashboard</span></a></li>
            <li><a href="Book.php" class=""><span class='icon-field'><i class="fa fa-book"></i>Booking List</span></a></li>
            <li><a href="extra.php" class=""><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Service</span></a></li>
            <li><a href="check-in.php" class=""><span class='icon-field'><i class="fa fa-sign-in-alt"></i>Check - in</span></a></li>
            <li><a href="check-out.php" class=""><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Check - out</span></a></li>
            <li><a href="room.php" class=""><span class='icon-field'><i class="fa fa-list"></i>Room Availability</span></a></li>
            <li><form action="<?php $_SERVER['PHP_SELF'] ?>" method="post"><input type="submit" class="btn" name="logout" value="Log-out"></form></li>
        </ul>
    </div>
</nav>

<div class="container">


    <div class="cardBox">
        <div class="card">
            <div>
                <div class="numbers"><?php echo $totalAvailableRooms; ?></div>
                <div class="cardName">Available Room</div>
            </div>

            <div class="iconBx">
                <ion-icon name="eye-outline"></ion-icon>
            </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers"><?php echo $totalNotAvailableRooms; ?></div>
                <div class="cardName">Occupied Room</div>
            </div>

            <div class="iconBx">
                <ion-icon name="cart-outline"></ion-icon>
            </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers">Room <?php echo $topRatedRoom; ?></div>
                <div class="cardName">Top Rating Room</div>
            </div>

            <div class="iconBx">
                <ion-icon name="chatbubbles-outline"></ion-icon>
            </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers"><?php echo $getTotalPending; ?></div>
                <div class="cardName">Booking Pending</div>
            </div>

            <div class="iconBx">
                <ion-icon name="cash-outline"></ion-icon>
            </div>
        </div>
    </div>



    

    <?php
    @include 'config.php';

    $sql = "SELECT * FROM guest WHERE bit = 0 ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    ?>

    <div class="card">
        <h2>Guest Registration (Pending)</h2>
        <div style="overflow-x: auto;">
            <table class="room-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Birthday</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo "{$row['first_name']} {$row['last_name']} "; ?></td>
                            <td><?php echo $row['birthday']; ?></td>
                            <td><?php echo $row['address']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td>
                                <button class="btn">Accept</button>
                                <button class="btn">Decline</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>


    <?php
@include 'config.php';

// Check if a search term is provided
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// Modify the SQL query to include a WHERE clause based on the search term
$sql = "SELECT * FROM guest WHERE bit = 1";
if ($searchTerm != '') {
    $sql .= " AND (first_name LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%' OR last_name LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%')";
}
$sql .= " ORDER BY id DESC";

// Execute the query
$result = mysqli_query($conn, $sql);
?>

<!-- Search Form -->
<div class="card">
    <h2>Guest Information</h2>
    <form method="POST" action="">
        <input type="text" name="search" placeholder="Search by First or Last Name" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit" class="btn">Search</button>
    </form>

    <div style="overflow-x: auto;">
        <table class="room-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Birthday</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Gender</th>
                    <th>User ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo "{$row['first_name']} {$row['last_name']} "; ?></td>
                        <td><?php echo $row['birthday']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['id']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>


</div>

</body>
</html>
