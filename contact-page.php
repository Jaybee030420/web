<?php
@include 'config.php';
if(isset($_POST['send'])){
	$Fname = $_POST['name'];
	$email = $_POST['email'];
	$message = $_POST['message'];
	
	
	// Insert new product
	$insert = "INSERT INTO contact (email, sender, message) VALUES('$name', '$email', '$message')";
	$upload = mysqli_query($conn, $insert);
	echo "<script>window.location.href = 'contact-page.php'; alert(\"You're message was sent successfully!! Thank you for messaging us!\"); </script>";
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Golden Grain Hotel</title>
		<link
			rel="stylesheet"
			href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
		/>

		<link
			href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;700&display=swap"
			rel="stylesheet"
		/>
		<link rel="stylesheet" href="./assets/css/global-header.css" />
		<link rel="stylesheet" href="./assets/css/global-footer.css" />
		<link rel="stylesheet" href="./assets/css/accesibility.css">
		<link rel="stylesheet" href="./assets/css/contact-page.css" />
		<link rel="shortcut icon" href="./assets/img/1.png" type="image/x-icon">
	</head>
	<body>
		<header>
			<div class="header-container">
				<nav class="header-nav-bar">
				<div class="header-nav-logo">
					<a href="index.php">
						<img src="./assets/images/carousel/1.png"
							alt="Golden Grain Hotel logo">
					</a>
					<h2>GoldenGrainHotel</h2>
				</div>
					<ul class="header-nav-lists">
						<li class="header-nav-list">
							<a class="header-nav-link" href="index.php">Home</a>
						</li>
						<li class="header-nav-list">
							<a class="header-nav-link" href="rooms-and-suites.php"
								>Rooms</a
							>
						</li>
						<li class="header-nav-list">
							<a class="header-nav-link" href="facilities.php">About Us</a>
						</li>
						<li class="header-nav-list">
							<a class="header-nav-link header-active" href="contact-page.php"
								>Contact Us</a
							>
						</li>
				
					</ul>
				</nav>
			</div>
		</header>

		<main>
			<div class="container">
				
				<div class="header">
					<h2>Contact Us</h2>
					<hr/>
					<p>
						Intersted in striking a partnership or do you have any complaint or
						feedback? Fill the form below
					</p>
				</div>

				
				<div class="main">
					<div class="contact">
						
						<div class="contact-form">
	<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
        <div class="contact-detail">
            <label class="hide" for="name">Enter your name</label>
            <input type="text" class="form-control" placeholder="Name" id="name" name="name" />
            <label class="hide" for="email">Enter your email address</label>
            <input type="email" class="form-control" placeholder="Email" id="email" name="email" />
        </div>
        <label class="hide" for="message">Message</label>
        <textarea class="form-control" rows="5" id="comment" placeholder="Message" style="resize: none; width: 100%;" name="message"></textarea>
        <button type="submit" name="send" class="btn">SEND MESSAGE</button>
    </form>
						</div>
						
						<div class="contact-us">
							<h3>Contact Us</h3>

							<span
								><i
									style="font-size: 1.5rem;"
									class="fa fa-map-marker"
									aria-hidden="true"
								></i
								>P.A BUILDING GENSAN DRIVE CORNER &nbsp; CASA SUBDIVISION,&nbsp;  Koronadal</span
							>
							<span
								><i
									style="font-size: 1.5rem;"
									class="fa fa-phone"
									aria-hidden="true"
								></i
								>0985 154 2810</span
							>
							<span
								><i
									style="font-size: 1.5rem;"
									class="fa fa-envelope-o"
									aria-hidden="true"
								></i
								>goldengrainhotel@gmail.com</span
							>
						</div>
						
					</div>
				</div>
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
