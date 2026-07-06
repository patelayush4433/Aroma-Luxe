<?php
/**
 * Export Sales Report (Excel CSV Spreadsheet Download)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Force Admin check
checkAdminAuth();

// Set Headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="aromaluxe_sales_report_' . date('Ymd_His') . '.csv"');

// Create file pointer
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, [
    'Order ID / Number',
    'Creation Date',
    'Customer Name',
    'Email Address',
    'Phone Contact',
    'Payment Method',
    'Payment Status',
    'Delivery Date',
    'Delivery Status',
    'GST Amount (INR)',
    'Discount (INR)',
    'Subtotal (INR)',
    'Final Paid (INR)'
]);

// Query database for orders
try {
    $stmt = $pdo->query("
        SELECT o.*, c.name as customer_name 
        FROM `orders` o 
        LEFT JOIN `customers` c ON o.customer_id = c.id 
        ORDER BY o.created_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['order_number'],
            $row['created_at'],
            $row['shipping_name'] ?? ($row['customer_name'] ?? 'Guest'),
            $row['shipping_email'],
            $row['shipping_phone'],
            $row['payment_method'],
            $row['payment_status'],
            $row['delivery_date'],
            $row['order_status'],
            $row['gst_amount'],
            $row['discount_amount'],
            $row['total_amount'],
            $row['final_amount']
        ]);
    }
} catch (PDOException $e) {
    // Write error inside CSV if it fails
    fputcsv($output, ['Database Error: ' . $e->getMessage()]);
}

fclose($output);
exit;
