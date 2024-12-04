
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



// Adding new mode of payment
if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_error = $_FILES['image']['error'];

    // Validate image upload
    if ($image_error === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $image_extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if (in_array($image_extension, $allowed_extensions)) {
            $target_directory = 'uploaded_img/';
            $new_image_name = uniqid() . '-' . basename($image);
            $new_image_path = $target_directory . $new_image_name;

            // Move uploaded image
            if (move_uploaded_file($image_tmp, $new_image_path)) {
                // Insert into database
                $insert_query = "INSERT INTO mop (name, img, bit) VALUES ('$name', '$new_image_name', 1)";
                if (mysqli_query($conn, $insert_query)) {
                    echo "<script>alert('Mode of payment added successfully!'); window.location.href = 'mop.php';</script>";
                } else {
                    echo "<script>alert('Error adding mode of payment: " . $conn->error . "'); window.location.href = 'mop.php';</script>";
                }
            } else {
                echo "<script>alert('Failed to upload image!'); window.location.href = 'mop.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid file type! Only jpg, jpeg, png allowed.'); window.location.href = 'mop.php';</script>";
        }
    } else {
        echo "<script>alert('Error uploading file.'); window.location.href = 'mop.php';</script>";
    }
}


@include 'config.php';
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_error = $_FILES['image']['error'];

    // Get current image path from the database
    $current_query = "SELECT * FROM mop WHERE ID = '$id'";
    $current_result = mysqli_query($conn, $current_query);
    $current_data = mysqli_fetch_assoc($current_result);
    $current_image_path = $current_data['img'];

    if ($image_error === UPLOAD_ERR_OK) {
        // Validate image upload
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $image_extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if (in_array($image_extension, $allowed_extensions)) {
            $target_directory = 'uploaded_img/';
            $new_image_name = uniqid() . '-' . basename($image);
            $new_image_path = $target_directory . $new_image_name;

            // Move uploaded image
            if (move_uploaded_file($image_tmp, $new_image_path)) {
                // Optionally delete old image
                if (!empty($current_image_path) && file_exists($target_directory . $current_image_path)) {
                    unlink($target_directory . $current_image_path);
                }
                // Update database with new image path
                $update_query = "UPDATE mop SET name = '$name', img = '$new_image_name' WHERE ID = '$id'";
            } else {
                echo "<script>alert('Failed to upload new image!'); window.location.href = 'mop.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file type! Only jpg, jpeg, png allowed.'); window.location.href = 'mop.php';</script>";
            exit();
        }
    } else {
        // Update without changing image
        $update_query = "UPDATE mop SET name = '$name' WHERE ID = '$id'";
    }

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Mode of payment updated successfully!'); window.location.href = 'mop.php';</script>";
    } else {
        echo "<script>alert('Error updating mode of payment: " . $conn->error . "'); window.location.href = 'mop.php';</script>";
    }
}


?>


<?php
$edit_user = null; 


@include 'config.php';
if (isset($_POST['edit'])) {
    // Fetch the user to be edited
    $id = $_POST['id'];
    $edit_query = "SELECT * FROM mop WHERE ID = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);

    // Open the modal
    echo "<script>document.getElementById('editModal').style.display='block';</script>";
}


@include 'config.php';
if (isset($_POST['activate'])) {

    $id = $_POST['id'];
    // Update bit to 1 (activated)
    $update_bit = "UPDATE mop SET bit = 1 WHERE ID = '$id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'mop.php'; alert('Mode of payment successfully activated!'); </script>";
}


@include 'config.php';
// Handle deactivation
if (isset($_POST['deactivate'])) {

    $id = $_POST['id'];
    // Update bit to 0 (deactivated)
    $update_bit = "UPDATE mop SET bit = 0 WHERE ID = '$id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'mop.php'; alert('Mode of payment successfully deactivated!'); </script>";
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
            <li><a href="mop.php" class="cat"><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="card">
        <h2>Mode of Payment List</h2>
        <button id="checkinBtn" class="btn" onclick="openModal()">Add New</button>
        <table class="table">
            <thead>
                <tr>
                    <th>Mode of Payment</th>
                    <th>Image</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
            <?php
            @include 'config.php';
            $sql = "SELECT * FROM mop order by ID desc" ;

           
            $result = $conn->query($sql);

          
            if ($result === false) {
                
                echo '<tr><td colspan="5">Error executing query: ' . $conn->error . '</td></tr>';
            } else {
                
                if ($result->num_rows > 0) {
                  
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><img src="uploaded_img/<?php echo $row['img']; ?>" height="100" alt=""></td>
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
        <h2>Mode of Payment Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
        <label class= "control-label">Name: <br></label>
        <input type="text" class="form-control" autocomplete="off" name="name" step="any"><br><br>

        <label for="" class="control-label">Image: </label><br>
        <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" class="box"><br><br>

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

        <label for="" class="control-label">Image: </label><br>
        <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" value="<?php echo isset($edit_user['img']) ? $edit_user['img'] : ''; ?>" class="box"><br><br>

            <input type="submit" class="btn" name="update" value="Confirm">
        </form>
    </div>
</div>

<script>
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
