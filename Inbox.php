

<?php
session_name('Owner'); 
session_start(); 

@include 'config.php';





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to the database
    // $conn = new mysqli('localhost:3306', 'root', '', 'system');

    // if ($conn->connect_error) {
    //     http_response_code(500);
    //     echo json_encode(['message' => 'Database connection failed']);
    //     exit;
    // }

    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $roomNumber = $data['roomNumber'];
    $description = $data['description'];



    // Validate data
    if (empty($roomNumber) || empty($description)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input']);
        exit;
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO issues (room_number, description) VALUES (?, ?)");
    $stmt->bind_param('ss', $roomNumber, $description);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Issue reported successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to report issue']);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}

if (isset($_SESSION['username']) && isset($_SESSION['position'])) {
    $username = $_SESSION['username'];
    $name = '';
    
    // Use $conn instead of $mysqli for the database connection
    $query = "SELECT fname, mname, lname FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);  // Changed $mysqli to $conn
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($fname, $mname, $lname);

    if ($stmt->fetch()) {
        // Combine and trim the name parts, ensuring no extra spaces
        $name = trim(implode(' ', array_filter([$fname, $mname, $lname])));
    }
}

 else {
    header("Location: login.php");
    exit();
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
    <link rel="stylesheet" href="inbox.css">
</head>
<body>

<nav id="sidebar" class='mx-lt-5'>
    <div class="title">
        <h1>GoldenGrainHotel</h1>
    </div>
    <div class="sidebar-list">
        <ul>
            
            <li><a href="Profiles.php" class="nav-item nav-check_in"><span class='icon-field'><i class="fa fa-sign-in-alt"></i></span>Profiles</a></li>
            <li><a href="Inbox.css" class="cat"><span class='icon-field'><i class="fa fa-sign-out-alt"></i></span>Inbox</a></li>
            <li><a href="RoomCat.php" class=""><span class='icon-field'><i class="fa fa-list"></i></span> Room Editor</a></li>
            <li><a href="About.php" class="nav-item nav-rooms"><span class='icon-field'><i class="fa fa-bed"></i></span> Promo Editor </a></li>
            <li><a href="Amenities.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Amenities Editor</a></li>
            <li><a href="mop.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="inbox">
        <div class="header">
            <h1>My Inbox</h1>
        </div>
        <div class="email-list">
            <?php
            @include 'config.php';

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Handle message deletion
            if (isset($_POST['delete_message'])) {
                $message_id = $_POST['delete_message'];
                $sql_delete = "DELETE FROM contact WHERE contactID = $message_id";
                if ($conn->query($sql_delete) === TRUE) {
                    echo '<div class="alert alert-success">Message deleted successfully.</div>';
                } else {
                    echo '<div class="alert alert-danger">Error deleting message: ' . $conn->error . '</div>';
                }
            }

            // SQL query to fetch messages
            $sql = "SELECT contactID, email, sender, message, DATE_FORMAT(timestamp, '%h:%i %p') AS formatted_timestamp FROM contact ORDER BY timestamp DESC";
            $result = $conn->query($sql);

            // Display messages
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="email-item" onclick="toggleMessageDetails(this)">';
                    echo '<div class="sender" style="color: white;>' . $row['email'] . '</div>';
                    echo '<div class="subject">' . $row['sender'] . '</div>';
                    echo '<div class="date">' . $row['formatted_timestamp'] . '</div> <br>';
                    echo '<button class="delete" onclick="deleteMessage(' . $row['contactID'] . ', event)">Delete</button>';
                    echo '<div class="message-details white-text">';
                    echo '<p style = "color: #White;"><strong>Message: <br></strong> </p><p style = "line-height: 1.5;">' . $row['message'] . '</p><br><br>';
                    echo '<p style = "color: #b36b00; font-size: 0.8em;">' . $row['formatted_timestamp'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No messages found.</p>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    function toggleMessageDetails(element) {

        var details = element.querySelector('.message-details');
        details.classList.toggle('show');
    }

    function deleteMessage(messageId, event) {
        event.stopPropagation(); 
        if (confirm('Are you sure you want to delete this message?')) {
            // AJAX request to delete message
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo $_SERVER["PHP_SELF"]; ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    location.reload(); // Refresh page after deletion
                } else {
                    console.log('Request failed. Status: ' + xhr.status);
                }
            };
            xhr.send('delete_message=' + messageId);
        }
    }
</script>

</body>
</html>
