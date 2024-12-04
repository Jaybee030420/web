
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


@include 'config.php';
$edit_user = null; 


if (isset($_POST['edit'])) {
    // Fetch the user to be edited
    $id = $_POST['id'];
    $edit_query = "SELECT * FROM promo WHERE ID = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);

    // Open the modal
    echo "<script>document.getElementById('editModal').style.display='block';</script>";
}

?>

<?php
@include 'config.php';

if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $discount = $_POST['discount'];
    $bit = 1; 

    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileType = $_FILES['image']['type'];

        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];


        if (in_array($fileType, $allowedTypes)) {

            $target = "uploaded_img/";
            if (!is_dir($target)) {
                mkdir($target, 0777, true);
            }


            $sanitizedFileName = basename($fileName);
            $targetFile = $target. $sanitizedFileName;

            if (move_uploaded_file($fileTmpName, $targetFile)) {

                echo "File uploaded successfully!";

                $image = $sanitizedFileName;

                $insert_stmt = $conn->prepare("INSERT INTO promo (name, bit, img, discount) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("siss", $name, $bit, $image, $discount);
                $insert_stmt->execute();

                echo "<script>window.location.href = 'About.php'; alert('Promo event successfully added!'); </script>";
            } else {
               
                echo "<script>window.location.href = 'About.php'; alert('Failed to upload image!'); </script>";
            }
        } else {
            echo "<script>window.location.href = 'About.php'; alert('Invalid file type! Only JPEG, PNG, and JPG are allowed.'); </script>";
        }
    } else {
        echo "<script>window.location.href = 'About.php'; alert('No file uploaded or there was an error!'); </script>";
    }
}



@include 'config.php';
if (isset($_POST['update'])) {
    $id = $_POST['id']; 
    $name = $_POST['name'];
    $discount = $_POST['discount'];

    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
      
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileType = $_FILES['image']['type'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (in_array($fileType, $allowedTypes)) {

            $target = "uploaded_img/";
            if (!is_dir($target)) {
                mkdir($target, 0777, true);
            }

            $sanitizedFileName = basename($fileName);
            $targetFile = $target . $sanitizedFileName;

            if (move_uploaded_file($fileTmpName, $targetFile)) {
                echo "File uploaded successfully!";
                
                $image = $sanitizedFileName;

                // Update the promo event with the new image
                $update_stmt = $conn->prepare("UPDATE promo SET name = ?, img = ?, discount = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $name, $image, $discount, $id);
                $update_stmt->execute();

                echo "<script>window.location.href = 'About.php'; alert('Promo event successfully updated!'); </script>";
            } else {
                echo "<script>window.location.href = 'About.php'; alert('Failed to upload image!'); </script>";
            }
        } else {
            echo "<script>window.location.href = 'About.php'; alert('Invalid file type! Only JPEG, PNG, and JPG are allowed.'); </script>";
        }
    } else {
        
        $update_stmt = $conn->prepare("UPDATE promo SET name = ?, discount = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $name,  $discount, $id);
        $update_stmt->execute();

        echo "<script>window.location.href = 'About.php'; alert('Promo event successfully updated!'); </script>";
    }
}



@include 'config.php';
 if (isset($_POST['activate'])) {
    $room_id = $_POST['id'];
    // Update bit to 1 (activated)
    $update_bit = "UPDATE promo SET bit = 1 WHERE ID = '$room_id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'About.php'; alert('Promo successfully activated!'); </script>";
}

@include 'config.php';
// Handle deactivation
if (isset($_POST['deactivate'])) {
    $room_id = $_POST['id'];
    // Update bit to 0 (deactivated)
    $update_bit = "UPDATE promo SET bit = 0 WHERE ID = '$room_id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'About.php'; alert('Promo successfully deactivated!'); </script>";
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
    <style>



    </style>
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
            <li><a href="About.php" class="cat"><span class='icon-field'><i class="fa fa-bed"></i></span> Promo Editor</a></li>
            <li><a href="Amenities.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Amenities Editor</a></li>
            <li><a href="mop.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="card">
        <h2>Promo Even List</h2>
        <button id="checkinBtn" class="btn" onclick="openModal()">Add New</button>
        <table class="table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Image</th>
                    <th>Amount less</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
            <?php
            @include 'config.php';
            $sql = "SELECT * FROM promo order by ID desc" ;

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
                            <td><img src="uploaded_img/<?php echo $row['img']; ?>" height="100" alt=""></td>
                            <td>₱ <?php echo htmlspecialchars(number_format($row['discount'], 2)); ?></td>
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
        <h2>Event Promo Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">

        <label class= "control-label">Event Name: <br></label>
        <input type="text" class="form-control" autocomplete="off" name="name" step="any"><br><br>

        <label class= "control-label">Amount: <br></label>
        <input type="number" class="form-control" autocomplete="off" name="discount" step="any"><br><br>

        <label for="" class="control-label">Image: </label><br>
        <input type="file" name="image" accept="image/png, image/jpeg, image/jpg" required><br><br>
        
            <input type="submit" class="btn" name="add" value="Add">
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
    <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Event Promo Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo isset($edit_user['ID']) ? $edit_user['ID'] : ''; ?>">

        <label class= "control-label">Event Name: <br></label>
        <input type="text" class="form-control" autocomplete="off" name="name" value="<?php echo isset($edit_user['name']) ? htmlspecialchars($edit_user['name']) : ''; ?>" step="any"><br><br>

        <label class= "control-label">Amount: <br></label>
        <input type="number" class="form-control" autocomplete="off" name="discount" value="<?php echo isset($edit_user['discount']) ? htmlspecialchars($edit_user['discount']) : ''; ?>"step="any"><br><br>

        <label for="" class="control-label">Image: </label><br>
        <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" value="<?php echo isset($edit_user['img']) ? htmlspecialchars($edit_user['img']) : ''; ?>"  class="box"><br><br>

            <input type="submit" class="btn" name="update" value="Update">
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
