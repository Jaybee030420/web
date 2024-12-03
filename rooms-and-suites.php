<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Grain Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/global-header.css">
    <link rel="stylesheet" href="./assets/css/global-footer.css">
    <link rel="stylesheet" href="./assets/css/rooms-and-suites.css">
    <link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
    <style>
        .sort-form {
            text-align: center;
            margin-bottom: 20px;
        }

        .sort-form select {
            padding: 8px;
            font-size: 16px;
        }

        .box {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: box-shadow 0.3s ease;
        }

        .box:hover {
            
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.573);
        }
    </style>
</head>

<body>
    <header>
        <div class="header-container">
            <nav class="header-nav-bar">
                <div class="header-nav-logo">
                    <a href="index.php">
                        <img src="./assets/images/logo/logo1.png" alt="Golden Grain Hotel logo">
                    </a>
                    <h2>GoldenGrainHotel</h2>
                </div>
                <ul class="header-nav-lists">
                    <li class="header-nav-list">
                        <a class="header-nav-link" href="index.php">Home</a>
                    </li>
                    <li class="header-nav-list">
                        <a class="header-nav-link header-active" href="rooms-and-suites.php">Rooms</a>
                    </li>
                    <li class="header-nav-list">
                        <a class="header-nav-link" href="facilities.php">About Us</a>
                    </li>
                    <li class="header-nav-list">
                        <a class="header-nav-link" href="contact-page.php">Contact Us</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <!-- Top Text -->
            <div class="page-header-container">
                <h2 class="page-header">Golden Grain Hotel Rooms</h2>
                <hr/>
                <p class="page-sub-header">
                    Get the most of our room specials. Enjoy the modern <br>
                    comfort and relaxing ideal room.
                </p>
            </div><br><br>

            <!-- Sort Form -->
            <div class="sort-form">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort">
                    <option value="rating" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'rating') echo 'selected'; ?>>Top Rating</option>
                    <option value="category" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'category') echo 'selected'; ?>>Room Category</option>
                    <option value="roomnumber" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'roomnumber') echo 'selected'; ?>>Room Number</option>
                    <option value="available" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'available') echo 'selected'; ?>>Available Room</option>
                </select>
            </div>

            <!-- Rooms -->
            <section class="hotel">
                <div class="box-container"><?php
@include 'config.php';

// Step 1: Get total ratings for all rooms
$totalRatingsQuery = "SELECT SUM(rating) AS total_ratings FROM roomcat";
$totalRatingsResult = mysqli_query($conn, $totalRatingsQuery);
$totalRatingsRow = mysqli_fetch_assoc($totalRatingsResult);

$totalRatings = $totalRatingsRow['total_ratings'] ?? 0;

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rating'; 

$condition = ""; 
$sort_order = 'ASC'; 


switch ($sort) {
    case 'category':
        $condition = "WHERE bit = 1 ";
        $sort_column = 'roomcat.name';
        break;
    case 'roomnumber':
        $condition = "WHERE bit = 1 ";
        $sort_column = 'roomcat.roomnumber';
        break;
    case 'available':
        $condition = "WHERE roombit = 0 and bit = 1";
        $sort_column = 'roomcat.roomnumber';
        break;
    case 'rating':
    default:
        $condition = "WHERE bit = 1";
        $sort_column = 'rating'; 
        $sort_order = 'DESC'; 
        break;
}


$query = "SELECT * FROM roomcat $condition ORDER BY $sort_column $sort_order";

// Fetch rooms based on sorting option
$select_products = mysqli_query($conn, $query);

if (mysqli_num_rows($select_products) > 0) {
    while ($fetch_product = mysqli_fetch_assoc($select_products)) {
        
        $rating = $fetch_product['rating'];

        $percentage = $totalRatings > 0 ? ($rating / $totalRatings) * 100 : 0;

        
        $starRating = ($percentage / 20); 
        $fullStars = floor($starRating);
        $remainder = $starRating - $fullStars;

        // Output HTML for each room
?>
        <form action="" method="post">
            <div class="box">
                <img src="uploaded_img/<?php echo $fetch_product['img']; ?>" alt="">
                <h3>Room <?php echo $fetch_product['roomnumber']; ?></h3>
                <div class="price"><p style="font-weight: 600;">Price: </p> <p style="padding-left: 60px;">₱<?php echo number_format($fetch_product['price'], 2); ?></p></div>
                <div class="price"><p style="font-weight: 600;">Room Type: </p> <p style="padding-left: 60px;"><?php echo $fetch_product['name']; ?></p></div>
                <div class="price"><p style="font-weight: 600;">Details: </p> <p style="font-size: medium; padding-left: 30px;"> <?php echo $fetch_product['breakfast']; ?> breakfast, <?php echo $fetch_product['person']; ?> person, free wifi</p></div>
                <div class="price">
                    <p style="padding-left: 20px; text-align: center;">
                    <?php 
                    // Display full stars
                    for ($i = 0; $i < $fullStars; $i++) {
                        echo '<span style="color: gold; font-weight: 500; font-size: 30px;">★</span>';
                    }

                    // Display half star if remainder is between 0.1 and 0.9
                    if ($remainder >= 0.1 && $remainder < 0.9) {
                        echo '<span style="color: gold; font-weight: 500; font-size: 30px;">★</span>'; // Half star
                    }

                    // Display no star if starRating is less than 1
                    while (($fullStars + ($remainder >= 0.1 && $remainder < 0.9 ? 1 : 0)) < 5) {
                        echo '<span style="color: gold; font-weight: 500; font-size: 30px; ">☆</span>'; // Empty star
                        $fullStars++;
                    }
                    ?>
                    <span>(<?php echo round($percentage, 2); ?>%)</span> <!-- Display the percentage -->
                    </p>
                </div>
            </div>
        </form>
<?php
    }
}
?>


                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <nav class="footer-nav">
                <div class="footer-description">
                    <h3 class="footer-description-title">Golden Grain Hotel</h3>
                    <p>You Ideal Place to stay.....</p>
                </div>
                <div class="footer-contact-us">
                    <h3 class="footer-description-title">Contact Us</h3>
                    <p class="footer-description-detail"> 
                        <img src="./assets/img/map-pin.svg" class="footer-description-icon" alt="star hotel location">
                        <span>23, Fola Osibo, Lekki Phase 1</span>
                    </p>
                    <p class="footer-description-detail">
                        <img src="./assets/img/phone.svg" class="footer-description-icon" alt="star hotels phone number"> 
                        <span>08185956620</span>
                    </p>
                    <p class="footer-description-detail">
                        <img src="./assets/img/mail.svg" class="footer-description-icon" alt="star hotels email">
                        <span>support@starhotels.com</span>
                    </p>
                </div>
                <div class="footer-follow-us">
                    <h3 class="footer-description-title">Follow Us</h3>
                    <ul class="footer-follow-us-lists">
                        <li class="follow-us-list">
                            <a href="https://www.facebook.com/goldengrainhotelkoronadal">
                                <img src="./assets/img/facebook.svg" alt="star hotels facebook page">
                            </a>
                            <span>Facebook.com</span>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </footer>

    <script>
        document.getElementById('sort').addEventListener('change', function() {
            var selectedValue = this.value;
            window.location.href = 'rooms-and-suites.php?sort=' + selectedValue;
        });
    </script>
</body>

</html>
