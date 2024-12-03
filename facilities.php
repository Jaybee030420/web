<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Golden Grain Hotel</title>
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="./assets/css/global-header.css">
	<link rel="stylesheet" href="./assets/css/global-footer.css">
	<link rel="stylesheet" href="./assets/css/facilities.css">
	<link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
</head>
<body>
	<header>
		<div class="header-container">
			<nav class="header-nav-bar">
				<div class="header-nav-logo">
					<a href="index.php">
						<img src="./assets/images/logo/logo1.png"
							alt="Golden Grain Hotel logo">
					</a>
					<h2>GoldenGrainHotel</h2>
					
				</div>
					
				<ul class="header-nav-lists">
                    <li class="header-nav-list">
                        <a class="header-nav-link" href="index.php">Home</a>
                    </li>
                    <li class="header-nav-list"><a class="header-nav-link "
                            href="rooms-and-suites.php">Rooms</a></li>
                    <li class="header-nav-list"><a class="header-nav-link header-active" href="facilities.php">About Us</a></li>
                    <li class="header-nav-list"><a class="header-nav-link" href="contact-page.php">Contact Us</a></li>
                    
                </ul>
			</nav>
		</div>
	</header>
	<main>
		<div class="container">

			<!-- Top Text -->
			<div class="page-header-container">
				<h2 class="page-header">Visit Us</h2>
				<hr />
				<p class="page-sub-header">
					Get the most of our room.<br> you can visit us @P.A BUILDING GENSAN DRIVE CORNER CASA SUBDIVISION, Koronadal
				</p>
			</div>
			<!-- Upper Section -->
			<section class="upper-section">
				<div class="container">
 					<div class="row">
   					<div class="col-lg-8 col-md-8 p-4 mb-lg-0 mb-3 bg-white rounded">
    				<iframe class="w-100 rounded" height="600px" width="1100px" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.2169838598293!2d124.85011837883845!3d6.4941884895769535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f819b74f6d1c97%3A0x539b27f4cb9cb021!2sGolden%20Grain%20Hotel!5e0!3m2!1sen!2sph!4v1710269945707!5m2!1sen!2sph" loading="lazy"></iframe>
  					</div>
			</section>
			
			<div class="up">
				<h2 class="page-header">Our Amenities</h2>
				<hr />
			</div>
			
<div class="box-container">

   <?php
    @include 'config.php';
   $select_products = mysqli_query($conn, "SELECT * FROM amenities where bit = 1");
   if(mysqli_num_rows($select_products) > 0){
      while($fetch_product = mysqli_fetch_assoc($select_products)){
   ?>

   <form action="" method="post">
      <div class="box">
	  	 <h3 style="padding-top: 5px;"> <?php echo $fetch_product['name']; ?> </h3>
         <img src="uploaded_img/<?php echo $fetch_product['images']; ?>" alt="">
		 <div class="price"><p style="padding: 10px; font-weight: 700; font-size:24px;">₱<?php echo number_format($fetch_product['price'], 2); ?></p></div>
         
		 
      </div>
   </form>

   <?php
      };
   };
   ?>

</div>
			

	</main>

	<footer class="footer">
		<div class="footer-container">
			<nav class="footer-nav">
				<div class="footer-description">
					<h3 class="footer-description-title">Golden Grain Hotel</h3>
					<p>Your ideal place to stay.....</p>
				</div>
				<div class="footer-contact-us">
					<h3 class="footer-description-title">Contact Us</h3>
					<p class="footer-description-detail"> 
						<img src="./assets/img/map-pin.svg" class="footer-description-icon" alt="star hotel location">

						<span>P.A BUILDING GENSAN DRIVE CORNER CASA SUBDIVISION, Koronadal, Philippines</span></p>
					<p class="footer-description-detail">
						<img src="./assets/img/phone.svg" class="footer-description-icon" alt="star hotels phone number"> 
						<span>
						0985 154 2810</span></p>
					<p class="footer-description-detail">
						<img src="./assets/img/mail.svg" class="footer-description-icon" alt="star hotels email">
						<span>goldengrainhotel@gmail.com</span> </p>
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
	
</body>

</html>