<?php

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Content-Type: application/json");

// Allow any domain to access (for testing purposes, replace with your domain in production)
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods for cross-origin requests
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow specific headers (e.g., Content-Type and Authorization headers)
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If the request is an OPTIONS request (preflight), send a 200 OK response without further processing
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
$conn = mysqli_connect('localhost','root','','system');
date_default_timezone_set('Asia/Manila');
?>