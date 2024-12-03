function sendOTP() {
  const email = document.getElementById('email').value; // Get email input value
  const otpverify = document.getElementsByClassName('otpverify')[0]; // OTP input container
  const otp_inp = document.getElementById('otp_inp'); // OTP input field
  const otp_btn = document.getElementById('otp-btn'); // OTP verify button

  // Check if email is empty
  if (email === "") {
    alert("Please enter a valid email address.");
    return;
  }

  // Generate a random 4-digit OTP
  const otp_val = Math.floor(Math.random() * 10000);
  let emailbody = `<h2>Your OTP is: </h2>${otp_val}`;

  // Send email with the generated OTP using EmailJS
  Email.send({
    SecureToken: "10fc79cd-9d4c-4267-8b2a-39e3c264e71c", // Use your EmailJS secure token
    To: email, 
    From: "seemab857@gmail.com", // Replace with your email
    Subject: "OTP", 
    Body: emailbody
  }).then(message => {
    if (message === "OK") {
      alert("OTP sent to your email: " + email);

      // Show OTP input section
      otpverify.style.display = "flex"; // Display OTP input field

      // Handle OTP verification when the button is clicked
      otp_btn.addEventListener('click', () => {
        if (otp_inp.value == otp_val) {
          alert("Email address verified!");
        } else {
          alert("Invalid OTP. Please try again.");
        }
      });
    } else {
      alert("Failed to send OTP. EmailJS returned: " + message);
      console.log("Error from EmailJS: ", message); // Log the response for debugging
    }
  }).catch(err => {
    console.error("Error sending OTP:", err);
    alert("There was an error while sending the OTP: " + err.message);
  });
}
