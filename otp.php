<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OTP Sample</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f7fc;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      width: 300px;
      text-align: center;
    }

    h1 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #333;
    }

    input[type="email"], input[type="text"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
    }

    input[type="text"] {
      display: none; /* Initially hidden OTP input */
    }

    .otpverify {
      display: none; /* Hide OTP input by default */
    }

    .btn {
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      margin-top: 10px;
    }

    .btn:hover {
      background-color: #45a049;
    }

    .btn:active {
      background-color: #3e8e41;
    }

    .otpverify button {
      margin-top: 10px;
      background-color: #007BFF;
    }

    .otpverify button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

  <div class="form">
    <h1>OTP Sample</h1>
    <input type="email" id="email" placeholder="Enter your email" required>
    <div class="otpverify">
        <input type="text" id="otp_inp" placeholder="Enter OTP here...">
        <button class="btn" id="otp-btn">Verify</button>
    </div>
    <button class="btn" onclick="sendOTP()">Send OTP</button>
  </div>

  <script src="https://smtpjs.com/v3/smtp.js"></script> <!-- Ensure this is included for EmailJS -->
  <script src="test.js"></script> <!-- Link your JS file here -->
</body>
</html>
