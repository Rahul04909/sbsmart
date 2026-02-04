<?php
// invoice.php
// Generates a printer-friendly invoice for a specific order.

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$orderId = (int)($_GET['id'] ?? 0);
$user = current_user();
$userId = (int)($user['id'] ?? 0);
$userEmail = $user['email'] ?? '';

if ($orderId <= 0) {
    flash_set('danger', 'Invalid order.');
    safe_redirect('account-orders.php');
}

$conn = get_db();
// Fetch order ensuring it belongs to the user
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND email = ? LIMIT 1");
$stmt->execute([$orderId, $userEmail]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    flash_set('danger', 'Order not found or access denied.');
    safe_redirect('account-orders.php');
}

// Fetch items
$stmtIt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtIt->execute([$orderId]);
$items = $stmtIt->fetchAll(PDO::FETCH_ASSOC);

$orderDate = new DateTime($order['created_at']);

// Simple printer-friendly layout
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #<?= $orderId ?> - SBSmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #000; font-size: 14px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .logo { font-size: 24px; font-weight: bold; color: #0b63d6; }
        .invoice-title { font-size: 24px; text-transform: uppercase; color: #555; }
        .table th { background: #f8f9fa; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: 0; padding: 0; }
        }
    </style>
</head>
<body class="py-5">

<div class="invoice-box">
    
    <div class="invoice-header">
        <div>
            <div class="logo mb-2">
                <img src="assets/images/logo.png" alt="SBSmart" style="height:40px;" onerror="this.style.display='none'"> 
                SBSmart
            </div>
            <address class="small text-muted">
                123 Industrial Area,<br>
                New Delhi, India - 110020<br>
                Email: marcom.sbsyscon@gmail.com
            </address>
        </div>
        <div class="text-end">
            <h1 class="invoice-title mb-2">Invoice</h1>
            <div class="mb-1"><strong>Invoice #:</strong> INV-<?= str_pad((string)$orderId, 6, '0', STR_PAD_LEFT) ?></div>
            <div class="mb-1"><strong>Date:</strong> <?= $orderDate->format('d M Y') ?></div>
            <div class="mb-1"><strong>Status:</strong> <?= ucfirst($order['status']) ?></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h5 class="border-bottom pb-1 mb-2">Bill To:</h5>
            <address>
                <strong><?= htmlspecialchars($order['name']) ?></strong><br>
                <?= nl2br(htmlspecialchars($order['address'])) ?><br>
                Phone: <?= htmlspecialchars($order['phone']) ?><br>
                Email: <?= htmlspecialchars($order['email']) ?>
            </address>
        </div>
        <div class="col-6 text-end">
            <h5 class="border-bottom pb-1 mb-2">Payment Details:</h5>
            <div>Method: <?= $order['razorpay_payment_id'] === 'COD' ? 'Cash On Delivery' : 'Online Payment' ?></div>
            <div>Transaction ID: <?= htmlspecialchars($order['razorpay_payment_id'] ?: '-') ?></div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center" style="width:100px;">Qty</th>
                <th class="text-end" style="width:150px;">Unit Price</th>
                <th class="text-end" style="width:150px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0; foreach ($items as $item): ?>
                <?php $sub = $item['price'] * $item['qty']; $grandTotal += $sub; ?>
                <tr>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td class="text-center"><?= $item['qty'] ?></td>
                    <td class="text-end">₹<?= number_format((float)$item['price'], 2) ?></td>
                    <td class="text-end">₹<?= number_format((float)$sub, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end fw-bold">Grand Total</td>
                <td class="text-end fw-bold">₹<?= number_format((float)$order['total'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="mt-5 text-center small text-muted">
        <p>Thank you for your business!</p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
    </div>

    <div class="mt-4 text-center no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print Invoice</button>
        <a href="javascript:history.back()" class="btn btn-secondary ms-2">Back</a>
    </div>

</div>

</body>
</html>
