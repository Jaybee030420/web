
<?php
session_name('Owner'); 
session_start(); 

@include 'config.php';




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

if (isset($_POST['logout'])) {
    
    session_unset();  
    session_destroy();  
    header("Location: login.php"); 
    exit(); 
}


@include 'config.php';

if (isset($_POST['add'])) {
    // Add new user
    $fname = $_POST['fname'];
    $mname = $_POST['mname']; 
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $cno = $_POST['cno'];
    $username = $_POST['username'];
    $position = $_POST['position'];
    $password = $_POST['password'];
    $key = $_POST['key'];

  
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_user_query = "SELECT * FROM users WHERE username = '$username'";
    $check_user_result = mysqli_query($conn, $check_user_query);

    if (mysqli_num_rows($check_user_result) > 0) {
        echo "<script>alert('Username already exists.'); window.location.href = 'Profiles.php';</script>";
    } else {
        
        $insert_query = "INSERT INTO users (fname, mname, lname, email, cno, username, position, password, voidid) 
                         VALUES ('$fname', '$mname', '$lname', '$email', '$cno', '$username', '$position', '$hashed_password', '$key')";

        if (mysqli_query($conn, $insert_query)) {
            echo "<script>alert('User added successfully!'); window.location.href = 'Profiles.php';</script>";
        } else {
            echo "<script>alert('Error adding user.'); window.location.href = 'Profiles.php';</script>";
        }
    }

}


$edit_user = null;

if (isset($_POST['edit'])) {
    // Fetch the user to be edited
    $id = $_POST['id'];
    $edit_query = "SELECT * FROM users WHERE id = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);

    // Open the modal
    echo "<script>document.getElementById('editmyModal').style.display='block';</script>";
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $cno = $_POST['cno'];
    $username = $_POST['username'];
    $position = $_POST['position'];
    $key = $_POST['key'];


    $new_password = $_POST['new_password'];
    $reenter_password = $_POST['reenter_password'];

    if (!empty($new_password) && $new_password === $reenter_password) {
  
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
   
        $update_query = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', cno = '$cno', username = '$username', position = '$position', password = '$hashed_password', voidid = '$key' WHERE id = '$id'";
    } else {

        $update_query = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email', cno = '$cno', username = '$username', position = '$position', voidid = '$key' WHERE id = '$id'";
    }

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('User updated successfully!'); window.location.href = 'Profiles.php';</script>";
    } else {
        echo "<script>alert('Error updating user.'); window.location.href = 'Profiles.php';</script>";
    }
}


if (isset($_POST['activate'])) {
    // Activate user (set bit = 1)
    $id = $_POST['id'];
    $activate_query = "UPDATE users SET bit = 1 WHERE id = '$id'";
    if (mysqli_query($conn, $activate_query)) {
        echo "<script>alert('User activated successfully!'); window.location.href = 'Profiles.php';</script>";
    } else {
        echo "<script>alert('Error activating user.'); window.location.href = 'Profiles.php';</script>";
    }
}

if (isset($_POST['deactivate'])) {
    // Deactivate user (set bit = 0)
    $id = $_POST['id'];
    $deactivate_query = "UPDATE users SET bit = 0 WHERE id = '$id'";
    if (mysqli_query($conn, $deactivate_query)) {
        echo "<script>alert('User disconnected successfully!'); window.location.href = 'Profiles.php';</script>";
    } else {
        echo "<script>alert('Error disconnecting user.'); window.location.href = 'Profiles.php';</script>";
    }
}


@include 'config.php';
$current_date = date('Y-m-d'); 

$query_daily = "
    SELECT SUM(payment_amount) AS total_daily_income
    FROM revenue
    WHERE DATE(payment_date) = '$current_date'
";

// SQL to get weekly income (sum of payments from this week)
$query_weekly = "
    SELECT SUM(payment_amount) AS total_weekly_income
    FROM revenue
    WHERE WEEK(payment_date, 1) = WEEK(CURRENT_DATE, 1)
    AND YEAR(payment_date) = YEAR(CURRENT_DATE)
";

// SQL to get monthly income (sum of payments from this month)
$query_monthly = "
    SELECT SUM(payment_amount) AS total_monthly_income
    FROM revenue
    WHERE MONTH(payment_date) = MONTH(CURRENT_DATE)
    AND YEAR(payment_date) = YEAR(CURRENT_DATE)
";

// SQL to get yearly income (sum of payments from this year)
$query_yearly = "
    SELECT SUM(payment_amount) AS total_yearly_income
    FROM revenue
    WHERE YEAR(payment_date) = YEAR(CURRENT_DATE)
";

$result_daily = mysqli_query($conn, $query_daily);
$row_daily = mysqli_fetch_assoc($result_daily);
$daily_income = $row_daily['total_daily_income'] ? $row_daily['total_daily_income'] : 0;

// For weekly income (resets at the start of every week)
$result_weekly = mysqli_query($conn, $query_weekly);
$row_weekly = mysqli_fetch_assoc($result_weekly);
$weekly_income = $row_weekly['total_weekly_income'] ? $row_weekly['total_weekly_income'] : 0;

// For monthly income (resets at the start of every month)
$result_monthly = mysqli_query($conn, $query_monthly);
$row_monthly = mysqli_fetch_assoc($result_monthly);
$monthly_income = $row_monthly['total_monthly_income'] ? $row_monthly['total_monthly_income'] : 0;

// For yearly income (resets at the start of every year)
$result_yearly = mysqli_query($conn, $query_yearly);
$row_yearly = mysqli_fetch_assoc($result_yearly);
$yearly_income = $row_yearly['total_yearly_income'] ? $row_yearly['total_yearly_income'] : 0;
?>



<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenGrainHotel</title>
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
    <link rel="stylesheet" href="Profiles.css">
</head>
<body>

<nav id="sidebar" class='mx-lt-5'>
	<div class="title">
		<h1>GoldenGrainHotel</h1>
	</div>
    <div class="sidebar-list">
        <ul>
            
            <li><a href="Profiles.php" class="cat"><span classstyles='icon-field'><i class="fa fa-sign-in-alt"></i></span>Profiles</a></li>
            <li><a href="Inbox.php" class="nav-item nav-check_out"><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Inbox</span></a></li>
            <li><a href="RoomCat.php" class=""><span class='icon-field'><i class="fa fa-list"></i></span> Room Editor</a></li>
            <li><a href="About.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Promo Editor</a></li>
            <li><a href="Amenities.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Amenities Editor</a></li>
            <li><a href="mop.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
            <li><form action="<?php $_SERVER['PHP_SELF'] ?>" method="post"><input type="submit" class="btn" name="logout" value="Log-out"></form></li>
        </ul>
    </div>
</nav>

<div class="container1">
<div class="sidebar-user-info">
            <p style="color: orange; font-size: 24px; margin-left: 1200px;">
                <strong><?php echo htmlspecialchars($name); ?></strong>
            </p>
    </div>


    <div class="income-card-container">
        <div class="income-card">
            <h3>Daily Income</h3>
            <p class="income-amount">₱ <?php echo number_format($daily_income, 2); ?></p>
        </div>

        <div class="income-card">
            <h3>Weekly Income</h3>
            <p class="income-amount">₱ <?php echo number_format($weekly_income, 2); ?></p>
        </div>

        <div class="income-card">
            <h3>Monthly Income</h3>
            <p class="income-amount">₱ <?php echo number_format($monthly_income, 2); ?></p>
        </div>
        
        <div class="income-card">
            <h3>Yearly Income</h3>
            <p class="income-amount">₱ <?php echo number_format($yearly_income, 2); ?></p>
        </div>
       
        
    </div>



    <div class="card">
        <h2>Admin Account List</h2>
        <button id="checkinBtn" class="btn" onclick="openModal()">Add New</button>
        <!-- Search Bar Form -->
        <div class="search-bar" style="margin-left: 900px;">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="text" name="search" placeholder="Search by Name or Username" value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>" style="padding: 5px;">
                <input type="submit" class="btn" value="Search">
            </form>
        </div>
        <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Username</th>
                <th>Position</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Apply search query
        $search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

        if (!empty($search)) {
            // Search query with wildcard
            $sql = "SELECT * FROM users WHERE position != 'Owner' 
                    AND (username LIKE '%$search%' 
                    OR lname LIKE '%$search%' 
                    OR fname LIKE '%$search%' 
                    OR mname LIKE '%$search%') order by id desc";
        } else {
            // Default query
            $sql = "SELECT * FROM users WHERE position != 'Owner' order by id desc";
        }

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['fname']); ?> <?php echo htmlspecialchars($row['mname']); ?> <?php echo htmlspecialchars($row['lname']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['cno']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['position']); ?></td>
                <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <input type="submit" class="btn" name="edit" value="Edit">
                    </form>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <?php if ($row['bit'] == 1): ?>
                            <input type="submit" class="btn btn-red" name="deactivate" value="Deactivate">
                        <?php else: ?>
                            <input type="submit" class="btn btn-green" name="activate" value="Activate">
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="7">No records found matching your search.</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>
</div>


<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('myModal').style.display='none'">&times;</span>
        <h2>Admin Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
            <input type="hidden" name="id">
            
            <label for="fname">First Name:</label><br>
            <input type="text" name="fname" required><br><br>

            <label for="mname">Middle Name:</label><br>
            <input type="text" name="mname" required><br><br>

            <label for="lname">Last Name:</label><br>
            <input type="text" name="lname" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label for="cno">Phone Number:</label><br>
            <input type="text" name="cno" required><br><br>

            <label for="username">Username:</label><br>
            <input type="text" name="username" required><br><br>

            <label for="position">Position:</label><br>
            <select name="position" required>
                <option value="frontdesk">Front Desk</option>
                <option value="manager">Manager</option>
                <option value="Owner">Owner</option>
            </select><br><br>

            <label for="password">Password:</label><br>
            <input type="password" name="password" required><br><br>

            <label for="password">Void Key:</label><br>
            <input type="number" name="key" placeholder="Enter key for voiding..."><br><br>

            <input type="submit" name="add" class="btn">
        </form>
    </div>
</div>


<div id="editmyModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editmyModal').style.display='none'">&times;</span>
        <h2>Admin Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
            <input type="hidden" name="id" value="<?php echo isset($edit_user['id']) ? $edit_user['id'] : ''; ?>">
            
            <label for="fname">First Name:</label><br>
            <input type="text" name="fname" value="<?php echo isset($edit_user['fname']) ? htmlspecialchars($edit_user['fname']) : ''; ?>" required><br><br>

            <label for="lname">Last Name:</label><br>
            <input type="text" name="lname" value="<?php echo isset($edit_user['lname']) ? htmlspecialchars($edit_user['lname']) : ''; ?>" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" name="email" value="<?php echo isset($edit_user['email']) ? htmlspecialchars($edit_user['email']) : ''; ?>" required><br><br>

            <label for="cno">Phone Number:</label><br>
            <input type="text" name="cno" value="<?php echo isset($edit_user['cno']) ? htmlspecialchars($edit_user['cno']) : ''; ?>" required><br><br>

            <label for="username">Username:</label><br>
            <input type="text" name="username" value="<?php echo isset($edit_user['username']) ? htmlspecialchars($edit_user['username']) : ''; ?>" required><br><br>

            <label for="position">Position:</label><br>
            <select name="position" required>
                <option value="<?php echo isset($edit_user['position']) ? htmlspecialchars($edit_user['position']) : ''; ?>"><?php echo isset($edit_user['position']) ? htmlspecialchars($edit_user['position']) : ''; ?></option>
                <option value="frontdesk">Front Desk</option>
                <option value="manager">Manager</option>
                <option value="Owner">Owner</option>
            </select><br><br>

            <label for="new_password">New Password:</label><br>
            <input type="password" name="new_password" placeholder="Enter New Password"><br><br>

            <label for="reenter_password">Re-enter Password:</label><br>
            <input type="password" name="reenter_password" placeholder="Re-enter Password"><br><br>

            <label for="password">Void Key:</label><br>
            <input type="number" name="key" placeholder="Enter key for voiding..."><br><br>



            <input type="submit" name="update" class="btn">
        </form>
    </div>
</div>
<script>

<?php if (isset($_POST['edit'])): ?>
    document.getElementById('editmyModal').style.display = 'block';
<?php endif; ?>

function openModal() {
    document.getElementById('myModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

var editModal = document.getElementById('editmyModal');
window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}
</script>

<script>
    // Function to check password match
    function checkPasswordMatch() {
        var newPassword = document.getElementsByName('new_password')[0];
        var reenterPassword = document.getElementsByName('reenter_password')[0];
        var submitButton = document.getElementsByName('update')[0];  // Get the submit button

        // Check if the passwords match
        if (newPassword.value !== reenterPassword.value) {
            reenterPassword.style.borderColor = 'red';
            reenterPassword.style.backgroundColor = '#f8d7da';
            submitButton.disabled = true;  // Disable submit if passwords don't match
        } else {
            newPassword.style.borderColor = 'green';
            newPassword.style.backgroundColor = '#d4edda';  // Light green background for valid input
            
            reenterPassword.style.borderColor = 'green';
            reenterPassword.style.backgroundColor = '#d4edda';  // Light green background for valid input
            submitButton.disabled = false;  // Enable submit if passwords match
        }
    }

    // Attach the function to input events on both password fields
    window.onload = function() {
        document.getElementsByName('new_password')[0].addEventListener('input', checkPasswordMatch);
        document.getElementsByName('reenter_password')[0].addEventListener('input', checkPasswordMatch);
    }
</script>

</body>
</html>
