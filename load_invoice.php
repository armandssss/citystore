<?php
include "db.php";

if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);
    $sql = "SELECT o.order_id as order_id, o.user_id, o.status, oi.product_id, oi.quantity, p.name, p.price, p.image_url, u.username, u.email
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            JOIN users u ON o.user_id = u.id
            WHERE o.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $invoiceContent = '
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
            <img src="uploads/citystore-logo.png" alt="Company Logo" style="margin: 0 auto; display: block;" class="logo">
            <h2 style="text-align: center;">INVOICE</h2>
            <p>Date: <strong>' . date('d M, Y') . '</strong></p>
            <p>Order ID: <strong>#' . $orderId . '</strong></p>
            <p>Distributor: <strong>citystore</strong></p>';
        
        $total = 0;
        $invoiceContent .= '
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #dddddd; padding: 8px;">Item</th>
                    <th style="border: 1px solid #dddddd; padding: 8px;">Quantity</th>
                    <th style="border: 1px solid #dddddd; padding: 8px;">Price</th>
                    <th style="border: 1px solid #dddddd; padding: 8px;">Amount</th>
                </tr>
            </thead>
            <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            $amount = $row['quantity'] * $row['price'];
            $total += $amount;

            $invoiceContent .= '
                <tr>
                    <td style="border: 1px solid #dddddd; padding: 8px;">' . $row['name'] . '</td>
                    <td style="border: 1px solid #dddddd; padding: 8px;">' . $row['quantity'] . '</td>
                    <td style="border: 1px solid #dddddd; padding: 8px;">' . $row['price'] . '</td>
                    <td style="border: 1px solid #dddddd; padding: 8px;">' . $amount . '</td>
                </tr>';
        }

        $invoiceContent .= '
            </tbody>
        </table>
        <p style="text-align: right; margin-top: 20px;">Total: <strong>' . $total . ' EUR</strong></p>
        <p>Payment method: <strong>Card</strong></p>
        <p><strong>Thank you for choosing us!</strong></p>
        </div>';

        echo $invoiceContent;
    } else {
        echo "No invoice found for this order.";
    }
}
?>
