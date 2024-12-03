<?php
// Get daily revenue
$query = "SELECT SUM(payment_amount) AS total_daily_revenue 
          FROM revenue
          WHERE revenue_type = 'daily' 
          AND DATE(payment_date) = CURDATE()";

$result = $conn->query($query);
$row = $result->fetch_assoc();
$totalDailyRevenue = $row['total_daily_revenue'] ?? 0;
echo "Total Daily Revenue: ₱ " . number_format($totalDailyRevenue, 2);
?>
