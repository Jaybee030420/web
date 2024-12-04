
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
if (isset($_POST['add_room'])) {
    $name = $_POST['name'];
    $roomNum = $_POST['roomNum'];
    $price = $_POST['price'];
    $breakfast = $_POST['breakfast'];
    $person = $_POST['person'];
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

   
    if (empty($name) || empty($price) || empty($roomNum) || empty($image) || empty($person)) {
        $message[] = 'Please fill out all fields.';
    } else {
        
        if ($_FILES['image']['error'] == 0) {
           
            $image_extension = pathinfo($image, PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($image_extension, $allowed_extensions)) {
               
                move_uploaded_file($image_tmp_name, $image_folder);
                
                
                $check_room_query = "SELECT * FROM roomCat WHERE roomnumber = '$roomNum'";
                $check_room_result = mysqli_query($conn, $check_room_query);

                if (mysqli_num_rows($check_room_result) > 0) {
                    echo "<script>window.location.href = 'RoomCat.php'; alert('Room Number already exists!'); </script>";
                } else {
                   
                    $insert = "INSERT INTO roomCat(name, roomnumber, price, img, breakfast, person) 
                               VALUES('$name', '$roomNum', '$price', '$image', '$breakfast', '$person')";
                    $upload = mysqli_query($conn, $insert);

                    if ($upload) {
                        echo "<script>window.location.href = 'RoomCat.php'; alert('Room successfully added!'); </script>";
                    } else {
                        echo "<script>window.location.href = 'RoomCat.php'; alert('Failed to add room.'); </script>";
                    }
                }
            } else {
                echo "<script>window.location.href = 'RoomCat.php'; alert('Invalid image file type. Only JPG, JPEG, PNG, GIF are allowed.'); </script>";
            }
        } else {
            echo "<script>window.location.href = 'RoomCat.php'; alert('Error uploading image.'); </script>";
        }
    }
}



@include 'config.php';
if (isset($_POST['update_room'])) {
    $name = $_POST['name'];
    $roomNum = $_POST['roomNum'];
    $price = $_POST['price'];
    $breakfast = $_POST['breakfast'];
    $person = $_POST['person']; 
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    
    if (empty($image)) {
        $image = $_POST['old_image'];  
    } else {
    
        if ($_FILES['image']['error'] == 0) {
            $image_extension = pathinfo($image, PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($image_extension, $allowed_extensions)) {
                
                move_uploaded_file($image_tmp_name, $image_folder);
            } else {
                echo "<script>window// Move the file to the folder.location.href = 'RoomCat.php'; alert('Invalid image file type. Only JPG, JPEG, PNG, GIF are allowed.'); </script>";
                exit();
            }
        } else {
            echo "<script>window.location.href = 'RoomCat.php'; alert('Error uploading image.'); </script>";
            exit();
        }
    }

    
    $update_data = "UPDATE roomcat SET name = '$name', price = '$price', img = '$image', breakfast = '$breakfast', person = '$person' 
                    WHERE roomnumber = '$roomNum'";
    $upload = mysqli_query($conn, $update_data);

    if ($upload) {
        echo "<script>window.location.href = 'RoomCat.php'; alert('Room successfully updated!'); </script>";
    } else {
        echo "<script>window.location.href = 'RoomCat.php'; alert('Failed to update room.'); </script>";
    }
}


@include 'config.php';
if (isset($_POST['activate'])) {
    $room_id = $_POST['id'];
    
    $update_bit = "UPDATE roomcat SET bit = 1 WHERE roomCatid = '$room_id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'RoomCat.php'; alert('Room successfully activated!'); </script>";
}


@include 'config.php';
if (isset($_POST['deactivate'])) {
    $room_id = $_POST['id'];
    
    $update_bit = "UPDATE roomcat SET bit = 0 WHERE roomCatid = '$room_id'";
    mysqli_query($conn, $update_bit);
    echo "<script>window.location.href = 'RoomCat.php'; alert('Room successfully deactivated!'); </script>";
}

@include 'config.php';
if (isset($_POST['edit'])) {
   
    $id = $_POST['id'];
    $edit_query = "SELECT * FROM roomcat WHERE roomCatid = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);

    
    echo "<script>document.getElementById('editRoomModal').style.display='block';</script>";
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
            <li><a href="RoomCat.php" class="cat"><span class='icon-field'><i class="fa fa-list"></i></span> Room Editor</a></li>
            <li><a href="About.php" class="nav-item nav-rooms"><span class='icon-field'><i class="fa fa-bed"></i></span> Promo Editor </a></li>
            <li><a href="Amenities.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Amenities Editor</a></li>
            <li><a href="mop.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
        </ul>
    </div>
</nav>


<div class="container1">

    <div class="card">
        <h2>Room List</h2>
        <button id="checkinBtn" class="btn" onclick="openModal()">Add New</button>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room Image</th>
                    <th>Room Numer</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>action</th>
                </tr>

            </thead>
            <tbody>
            <?php
            @include 'config.php';

            $sql = mysqli_query($conn, "SELECT * FROM `roomcat` order by roomCatid desc");
            
            // Check if query execution was successful
            if ($sql === false) {
                // Handle query error
                echo '<tr><td colspan="5">Error executing query: ' . mysqli_error($conn) . '</td></tr>';
            } else {
                // Check if there are results
                if (mysqli_num_rows($sql) > 0) {
                    // Iterate through results and display each row
                    while ($row = mysqli_fetch_assoc($sql)) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['roomCatid']); ?></td>
                            <td><img src="uploaded_img/<?php echo htmlspecialchars($row['img']); ?>" alt="Room Image"></td>
                            <td><?php echo htmlspecialchars($row['roomnumber']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['roomCatid']); ?>">
                                    <input type="submit" class="btn" name="edit" value="Edit">
                                </form>
            
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['roomCatid']); ?>">
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
                    echo '<tr><td colspan="5">No records found.</td></tr>';
                }
            }
            
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Room Modal -->
<div id="addRoomModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add New Room</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <label for="name">Room Name:</label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="roomNum">Room Number:</label>
            <input type="number" id="roomNum" name="roomNum" required><br><br>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" required><br><br>

            <label for="breakfast">Breakfast:</label>
            <input type="number" id="bf" name="breakfast" required><br><br>

            <label for="person">No. of Person:</label>
            <input type="number" id="person" name="person" required><br><br>

            <label for="image">Room Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required><br><br>

            <input type="submit" name="add_room" value="Add Room" class="btn">
        </form>
    </div>
</div>

<!-- Edit Room Modal -->
<div id="editRoomModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editRoomModal').style.display='none'">&times;</span>
        <h2>Edit Room</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo isset($edit_user['roomCatid']) ? $edit_user['roomCatid'] : ''; ?>">

            <label for="name">Room Name:</label>
            <input type="text" id="name" name="name" value="<?php echo isset($edit_user['name']) ? htmlspecialchars($edit_user['name']) : ''; ?>" required><br><br>

            <label for="roomNum">Room Number:</label>
            <input type="number" id="roomNum" name="roomNum" value="<?php echo isset($edit_user['roomnumber']) ? htmlspecialchars($edit_user['roomnumber']) : ''; ?>" required><br><br>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" value="<?php echo isset($edit_user['price']) ? htmlspecialchars($edit_user['price']) : ''; ?>" required><br><br>

            <label for="breakfast">Breakfast:</label>
            <input type="number" id="bf" name="breakfast" value="<?php echo isset($edit_user['breakfast']) ? htmlspecialchars($edit_user['breakfast']) : ''; ?>" required><br><br>

            <label for="person">No. of Person:</label>
            <input type="number" id="person" name="person" value="<?php echo isset($edit_user['person']) ? htmlspecialchars($edit_user['person']) : ''; ?>" required><br><br>

            <label for="image">Room Image:</label>
            <input type="file" id="image" name="image" accept="image/*"><br><br>

            <input type="submit" name="update_room" value="Update Room" class="btn">
        </form>
    </div>
</div>


<script>
// Modal Trigger on Edit Action
<?php if (isset($_POST['edit'])): ?>
    document.getElementById('editRoomModal').style.display = 'block';
<?php endif; ?>

function openModal() {
    document.getElementById('addRoomModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addRoomModal').style.display = 'none';
}

var editModal = document.getElementById('editRoomModal');
window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}
</script>

</body>
</html>
