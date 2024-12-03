
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
?>
<?php

@include 'config.php';
if(isset($_POST['add'])){
    
   $name = $_POST['name'];
   $bit = 1;
   $amount = $_POST['amount'];

    $check_room_query = "SELECT * FROM discount WHERE name = '$name'";
    $check_room_result = mysqli_query($conn, $check_room_query);

    if(mysqli_num_rows($check_room_result) > 0) {
        echo "<script>window.location.href = 'discount.php'; alert(\"this discount is already existed!!\"); </script>";
    } else {
        // Insert new product
        $insert = "INSERT INTO discount(name, bit, amount) VALUES('$name', '$bit', '$amount')";
        mysqli_query($conn, $insert);

        echo "<script>window.location.href = 'discount.php'; alert(\" New dicount successfully added!!\"); </script>";
    }


}


if(isset($_POST['update'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];

    $update_data = "UPDATE amenities SET name ='$name', price ='$price', images ='$image'  WHERE name = '$name'";
    mysqli_query($conn, $update_data);
    echo "<script>window.location.href = 'Amenities.php'; alert(\"Amenities successfully updated!!\"); </script>";

};


?>


<?php
$edit_user = null; 


@include 'config.php';
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $edit_query = "SELECT * FROM discount WHERE ID = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);

    // Open the modal
    echo "<script>document.getElementById('editModal').style.display='block';</script>";
}


if (isset($_POST['activate'])) {
    $id = $_POST['id'];
    $update_bit = "UPDATE discount SET bit = 1 WHERE ID = '$id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'discount.php'; alert('Discount successfully activated!'); </script>";
}

// Handle deactivation
if (isset($_POST['deactivate'])) {
    $id = $_POST['id'];
    $update_bit = "UPDATE discount SET bit = 0 WHERE ID = '$id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'discount.php'; alert('Discount successfully deactivated!'); </script>";
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
    <link rel="stylesheet" href="RoomCat.css">
    
</head>
<body>

<nav id="sidebar" class='mx-lt-5'>
	<div class="title">
		<h1>GoldenGrainHotel</h1>
	</div>
    <div class="sidebar-list">
        <ul>
            
            <li><a href="Profiles.php" class="nav-item nav-check_in"><span classstyles='icon-field'><i class="fa fa-sign-in-alt"></i></span>Profiles</a></li>
            <li><a href="Inbox.php" class="nav-item nav-check_out"><span class='icon-field'><i class="fa fa-sign-out-alt"></i>Inbox</span></a></li>
            <li><a href="RoomCat.php" class=""><span class='icon-field'><i class="fa fa-list"></i></span> Room Editor</a></li>
            <li><a href="About.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Promo Editor</a></li>
            <li><a href="Amenities.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Amenities Editor</a></li>
            <li><a href="mop.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class="cat"><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="card">
        <h2>Discount List</h2>
        <button id="checkinBtn" class="btn" onclick="openModal()">Add New</button>
        <table class="table">
            <thead>
                <tr>
                    <th>Person Type</th>
                    <th>Discount</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
            <?php
            @include 'config.php';
            $sql = "SELECT * FROM discount order by ID desc" ;

            // Perform the query
            $result = $conn->query($sql);

            // Check if query execution was successful
            if ($result === false) {
                // Handle query error
                echo '<tr><td colspan="5">Error executing query: ' . $conn->error . '</td></tr>';
            } else {
                // Check if there are results
                if ($result->num_rows > 0) {
                    // Iterate through results and display each row
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['amount']); ?>%</td>
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ID']); ?>">
                                    <input type="submit" class="btn" name="edit" value="Edit">
                                </form>
            
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['ID']); ?>">
                                    <?php if ($row['bit'] == 0): ?>
                                        <input type="submit" class="btn btn-green" name="activate" value="Activate">
                                    <?php else: ?>
                                        <input type="submit" class="btn btn-red" name="deactivate" value="Deactivate">
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    // No records found
                    echo '<tr><td colspan="5">No records found where roomnum is not null</td></tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>



<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Discounts Editor</h2>

        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
        <label class= "control-label">Person Type: <br></label>
        <input type="text" class="form-control"  autocomplete="off" name="name" step="any"><br><br>

        <label class= "control-label">Discount: <br></label>
        <input type="number" class="form-control" autocomplete="off" name="amount" step="any"><br><br>

            <input type="submit" class="btn" name="add" value="Confirm">
        </form>
    </div>
</div>


<div id="editModal" class="modal">
    <div class="modal-content">
    <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
    <h2>Mode of Payment Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?php echo isset($edit_user['ID']) ? $edit_user['ID'] : ''; ?>">

        <label class= "control-label">Name: <br></label>
        <input type="text" class="form-control" value="<?php echo isset($edit_user['name']) ? $edit_user['name'] : ''; ?>" autocomplete="off" name="name" step="any"><br><br>

        
        <label class= "control-label">Discount: <br></label>
        <input type="number" class="form-control" value="<?php echo isset($edit_user['amount']) ? $edit_user['amount'] : ''; ?>" autocomplete="off" name="name" step="any"><br><br>

            <input type="submit" class="btn" name="update" value="Confirm">
        </form>
    </div>
</div>

<script>
// Modal Trigger on Edit Action
<?php if (isset($_POST['edit'])): ?>
    document.getElementById('editModal').style.display = 'block';
<?php endif; ?>

function openModal() {
    document.getElementById('myModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

var editModal = document.getElementById('editModal');
window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}
</script>

</body>
</html>
