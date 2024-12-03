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

// Add new amenities
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];

    // Validate inputs
    if (empty($name) || empty($price) || empty($image)) {
        echo "<script>alert('Please fill all fields!'); window.location.href = 'About.php';</script>";
        exit();
    }

    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileType = $_FILES['image']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (in_array($fileType, $allowedTypes)) {
            $target = "uploaded_img/";
            if (!is_dir($target)) {
                mkdir($target, 0777, true);
            }

            $sanitizedFileName = basename($image);
            $targetFile = $target . $sanitizedFileName;

            if (move_uploaded_file($fileTmpName, $targetFile)) {
                // Use prepared statement to prevent SQL injection
                $insert_stmt = $conn->prepare("INSERT INTO Amenities (name, images, price) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("ssi", $name, $sanitizedFileName, $price);
                
                if ($insert_stmt->execute()) {
                    echo "<script>alert('New amenities successfully added!'); window.location.href = 'Amenities.php';</script>";
                } else {
                    echo "<script>alert('Error adding amenity!'); window.location.href = 'Amenities.php';</script>";
                }
            } else {
                echo "<script>alert('Failed to upload image!'); window.location.href = 'Amenities.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid file type! Only JPEG, PNG, and JPG are allowed.'); window.location.href = 'Amenities.php';</script>";
        }
    } else {
        echo "<script>alert('No file uploaded or there was an error!'); window.location.href = 'Amenities.php';</script>";
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id']; 
    $name = $_POST['name'];
    $discount = $_POST['price'];

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
                $update_stmt = $conn->prepare("UPDATE Amenities SET name = ?, images = ?, price = ? WHERE amenitiesID = ?");
                $update_stmt->bind_param("sssi", $name, $image, $discount, $id);
                $update_stmt->execute();

                echo "<script>window.location.href = 'Amenities.php'; alert('Successfully updated!'); </script>";
            } else {
                echo "<script>window.location.href = 'Amenities.php'; alert('Failed to upload image!'); </script>";
            }
        } else {
            echo "<script>window.location.href = 'Amenities.php'; alert('Invalid file type! Only JPEG, PNG, and JPG are allowed.'); </script>";
        }
    } else {
        
        $update_stmt = $conn->prepare("UPDATE promo SET name = ?, price = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $name,  $discount, $id);
        $update_stmt->execute();

        echo "<script>window.location.href = 'Amenities.php'; alert('Promo event successfully updated!'); </script>";
    }
}



// Activate/Deactivate amenities
if (isset($_POST['activate']) || isset($_POST['deactivate'])) {
    $id = $_POST['id'];
    $isActive = isset($_POST['activate']) ? 1 : 0;
    $activate_query = $conn->prepare("UPDATE amenities SET bit = ? WHERE amenitiesID = ?");
    $activate_query->bind_param("ii", $isActive, $id);
    $activate_query->execute();
    echo "<script>alert('Amenities successfully " . ($isActive ? 'activated' : 'deactivated') . "!'); window.location.href = 'Amenities.php';</script>";
}

// Fetch and edit an amenity (for modal)
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $edit_query = "SELECT * FROM amenities WHERE amenitiesID = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);
    
    // Open the modal to show the existing data
    echo "<script>document.getElementById('editModal').style.display='block';</script>";
}



// Activate amenities
if (isset($_POST['activate'])) {
    $id = $_POST['id'];
    $activate_query = "UPDATE amenities SET bit = 1 WHERE amenitiesID = '$id'";
    mysqli_query($conn, $activate_query);
    echo "<script>window.location.href = 'Amenities.php'; alert('Amenities successfully activated!');</script>";
}

// Deactivate amenities
if (isset($_POST['deactivate'])) {
    $id = $_POST['id'];
    $deactivate_query = "UPDATE amenities SET bit = 0 WHERE amenitiesID = '$id'";
    mysqli_query($conn, $deactivate_query);
    echo "<script>window.location.href = 'Amenities.php'; alert('Amenities successfully deactivated!');</script>";
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
            <li><a href="Amenities.php" class="cat"><span class='icon-field'><i class="fa fa-bed"></i></span> Amenities Editor</a></li>
            <li><a href="mop.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> MOP Editor</a></li>
            <li><a href="discount.php" class=""><span class='icon-field'><i class="fa fa-bed"></i></span> Discount Editor</a></li>
        </ul>
    </div>
</nav>

<div class="container1">
    <div class="card">
        <h2>Amenities List</h2>
        <button id="checkinBtn" class="btn" onclick="openModal()">Add New</button>
        <table class="table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
            <?php
            @include 'config.php';
            $sql = "SELECT * FROM Amenities order by amenitiesID desc";

            $result = $conn->query($sql);

            if ($result === false) {
                echo '<tr><td colspan="5">Error executing query: ' . $conn->error . '</td></tr>';
            } else {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['amenitiesID']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><img src="uploaded_img/<?php echo $row['images']; ?>" height="100" alt=""></td>
                            <td>₱ <?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                            <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['amenitiesID']); ?>">
                                    <input type="submit" class="btn" name="edit" value="Edit">
                                </form>
            
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['amenitiesID']); ?>">
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
                    echo '<tr><td colspan="5">No records found</td></tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Adding Amenities -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Amenities Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
            <label class="control-label">Amenities Name: <br></label>
            <input type="text" class="form-control" autocomplete="off" name="name" step="any"><br><br>

            <label class="control-label">Price: <br></label>
            <input type="number" class="form-control" autocomplete="off" name="price" step="any"><br><br>

            <label for="" class="control-label">Image: </label><br>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" class="box"><br><br>

            <input type="submit" class="btn" name="add" value="Add">
        </form>
    </div>
</div>

<!-- Modal for Editing Amenities -->
<div id="editModal" class="modal">
    <div class="modal-content">
    <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Amenities Editor</h2>
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo isset($edit_user['amenitiesID']) ? $edit_user['amenitiesID'] : ''; ?>">

        <label class="control-label">Amenities Name: <br></label>
        <input type="text" class="form-control" autocomplete="off" name="name" value="<?php echo isset($edit_user['name']) ? htmlspecialchars($edit_user['name']) : ''; ?>" step="any"><br><br>

        <label class="control-label">Price: <br></label>
        <input type="number" class="form-control" autocomplete="off" name="price" value="<?php echo isset($edit_user['price']) ? htmlspecialchars($edit_user['price']) : ''; ?>" step="any"><br><br>

        <label for="" class="control-label">Image: </label><br>
        <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" class="box"><br><br>

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
