<?php
@include 'config.php';
session_start(); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
		Golden Grain Hotel
	</title>

	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="./assets/css/global-header.css">
	<link rel="stylesheet" href="./assets/css/global-footer.css">
	<link rel="stylesheet" href="./assets/css/accesibility.css">
	<link rel="stylesheet" href="./assets/css/index.css">
	<link rel="shortcut icon" href="./assets/images/carousel/1.png" type="image/x-icon">


</head>

<body class="scroll-bar">
	<div id="loader">
		<svg version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve">
			<path fill="#d4af37" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">
				<animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite" />
			</path>
		</svg>
	</div>
	<header>
		<div class="header-container">
			<nav class="header-nav-bar">
				<div class="header-nav-logo">
					<a href="index.php">
						<img src="./assets/images/carousel/1.png" alt="Golden Grain Hotel logo">
					</a>
					<h2>GoldenGrainHotel</h2>
				</div>
				<ul class="header-nav-lists">
					<li class="header-nav-list">
						<a class="header-nav-link header-active" href="index.php">Home</a>
					</li>
					<li class="header-nav-list"><a class="header-nav-link" href="rooms-and-suites.php">Rooms</a></li>
					<li class="header-nav-list"><a class="header-nav-link" href="facilities.php">About Us</a></li>
					<li class="header-nav-list"><a class="header-nav-link" href="contact-page.php">Contact Us</a></li>
					<li class="header-nav-list"><a class="header-btn header-btn-custom">Login</a></li>
				</ul>

				<div class="header-hamburger-icon">

				</div>
			</nav>
		</div>

		</div>
		<div id="loginPopup" class="popup">
			<div class="popup-content">
				<span class="close" id="closePopup">&times;</span>
				<h2>Login</h2>
				<form action="verify.php" method="post">
					<label for="username">Username:</label><br>
					<input type="text" id="username" placeholder="username" name="username" autocomplete="off"><br>
					<label for="password">Password:</label><br>
					<input type="password" id="password" name="password" placeholder="password" autocomplete="off"><br><br>
					<input type="submit" name="login" value="Login">
				</form>
			</div>
		</div>


	</header>

	<div class="jumbotron-container">

		<div class="jumbotron-left">
			<h2 class="jumbotron-header">Uncover<br>the ultimate destination for
				<br>your ideal place
			</h2>
			<p>We prioritize offering customers the utmost quality and service, <br>
				striving to exceed expectations at every turn.</p>
			<a href="login1.php" class="btn btn-fill btn-large">Book Now</a>
			
		</div>
	</div>


	<div class="enjoy-container">
		<div class="enjoy-header">
			<h2 class="enjoy-heading">Enjoy your stay <br>at our hotel</h2>
			<hr class="horizontal">
			<p>We are more than being a hotel because we are able<br> to combine the quality standard of a hotel with the<br> advantages of an apartment.</p>
		</div>
		<div class="enjoy-services">
			<div class="first-col">
				<div class="upper">
					<span>
						<img src="./assets/img/clock.svg" alt="clock icon" class="enjoy__clock-icon">
					</span>
					<h3>24 hours Room Service</h3>
					<p>You have access to 24-hours a day room service at our hotel.</p>
				</div>
				<div class="lower">
					<span>
						<img src="./assets/img/wifi.svg" alt="wifi icon" class="enjoy__wifi-icon">
					</span>
					<h3>Free Wi-Fi Access</h3>
					<p>You have access to 24-hours free Wi-Fi services irrespective of any room.</p>
				</div>

			</div>
			<div class="sec-col">
				<div class="upper">
					<span>
						<img src="./assets/img/coffee.svg" alt="coffee icon" class="enjoy__coffee-icon">
					</span>
					<h3>Convenient Store</h3>
					<p>You have access to the world state of art restaurants and bars at our hotel</p>
				</div>

			</div>
			<div class="third-col cont">
				<img src="./assets/images/carousel/4.png">
			</div>
		</div>
		<div class="up">
			<h2 class="page-header">Promo Packs</h2>
		</div>

		<div class="box-container">

			<?php
			@include 'config.php';
			$select_products = mysqli_query($conn, "SELECT * FROM promo where bit = 1");
			if (mysqli_num_rows($select_products) > 0) {
				while ($fetch_product = mysqli_fetch_assoc($select_products)) {
			?>

					<form action="" method="post">
						<div class="box">
							<img src="uploaded_img/<?php echo $fetch_product['img']; ?>" alt="">
							<h3 style="padding-top: 5px;"> <?php echo $fetch_product['name']; ?> </h3>
						</div>
					</form>

			<?php
				};
			};
			?>

		</div>

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
							<img src="./assets/img/map-pin.svg" class="footer-description-icon" alt="Golden Grain Hotel">

							<span>P.A BUILDING GENSAN DRIVE CORNER CASA SUBDIVISION, Koronadal, Philippines</span>
						</p>
						<p class="footer-description-detail">
							<img src="./assets/img/phone.svg" class="footer-description-icon" alt="Golden Grain Hotel">
							<span>
								0985 154 2810</span>
						</p>
						<p class="footer-description-detail">
							<img src="./assets/img/mail.svg" class="footer-description-icon" alt="Golden Grain Hotel">
							<span>goldengrainhotel@gmail.com</span>
						</p>
					</div>
					<div class="footer-follow-us">
						<h3 class="footer-description-title">Follow Us</h3>
						<ul class="footer-follow-us-lists">
							<li class="follow-us-list">
								<a href="https://www.facebook.com/goldengrainhotelkoronadal">
									<img src="./assets/img/facebook.svg" alt="Golden Grain Hotel">
								</a>
								<span>Facebook.com</span>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</footer>
		<script defer async>
			(() => {
				const loader = document.getElementById('loader');
				const scrollBar = document.getElementsByClassName('scroll-bar')[0];
				window.addEventListener('load', () => {
					loader.classList.add('none');
					scrollBar.classList.remove('scroll-bar')
				});
			})();
		</script>
		<script>
			const loginButton = document.querySelector('.header-btn-custom');
			const loginPopup = document.getElementById('loginPopup');
			const closeButton = document.getElementById('closePopup');


			function openLoginPopup() {
				loginPopup.style.display = 'block';
			}


			function closeLoginPopup() {
				loginPopup.style.display = 'none';
			}

			loginButton.addEventListener('click', openLoginPopup);
			closeButton.addEventListener('click', closeLoginPopup);
		</script>
		<script>
			function openPopup() {
				document.getElementById("popup1").style.display = "block";
				document.getElementById("popup-overlay1").style.display = "block";
			}

			function closePopup() {
				document.getElementById("popup1").style.display = "none";
				document.getElementById("popup-overlay1").style.display = "none";
			}
		</script>
		<script type="text/javascript">
			history.pushState(null, null, location.href);
			window.onpopstate = function() {
				history.go(1);
			};
		</script>

</html>
</body>