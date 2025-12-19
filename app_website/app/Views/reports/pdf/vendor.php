<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vendor Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 10px; }
        h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; margin-bottom: 20px; }
        h2 { color: #555; margin-top: 20px; margin-bottom: 10px; font-size: 14px; }
        .info-section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; }
        .info-row { margin-bottom: 8px; }
        .info-label { font-weight: bold; color: #666; display: inline-block; width: 150px; }
        .summary-box { display: inline-block; width: 23%; margin: 5px; padding: 10px; border: 1px solid #ddd; text-align: center; vertical-align: top; }
        .summary-value { font-size: 18px; font-weight: bold; color: #333; }
        .summary-label { font-size: 11px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th { background-color: #f5f5f5; padding: 8px; text-align: left; border: 1px solid #ddd; font-weight: bold; }
        td { padding: 6px; border: 1px solid #ddd; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #666; }
    </style>
</head>
<body>
    <h1>Vendor Report</h1>

    <!-- Vendor Information -->
    <div class="info-section">
        <h2>Vendor Information</h2>
        <div class="info-row">
            <span class="info-label">Company Name:</span>
            <span><?= esc($vendor['company_name'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Person:</span>
            <span><?= esc($vendor['full_name'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?= esc($vendor['email'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span><?= esc($vendor['phone'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">GST Number:</span>
            <span><?= esc($vendor['gst_number'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">PAN Number:</span>
            <span><?= esc($vendor['pan_number'] ?? '—') ?></span>
        </div>
        <?php if($startDate || $endDate): ?>
        <div class="info-row">
            <span class="info-label">Report Period:</span>
            <span><?= $startDate ? date('d M Y', strtotime($startDate)) : 'Start' ?> - <?= $endDate ? date('d M Y', strtotime($endDate)) : 'End' ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Summary -->
    <div style="margin: 20px 0;">
        <div class="summary-box">
            <div class="summary-value"><?= $summary['total_purchase_orders'] ?? 0 ?></div>
            <div class="summary-label">Purchase Orders</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= number_format($summary['total_purchase_amount'] ?? 0, 2) ?></div>
            <div class="summary-label">Total Purchase Amount</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= $summary['total_receipts'] ?? 0 ?></div>
            <div class="summary-label">Stock Receipts</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= $summary['total_qr_codes'] ?? 0 ?></div>
            <div class="summary-label">QR Codes</div>
        </div>
    </div>

    <!-- Purchase Orders -->
    <h2>Purchase Orders</h2>
    <table>
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Godown</th>
                <th>Order Date</th>
                <th>Status</th>
                <th class="text-end">Total Amount</th>
                <th class="text-end">Final Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($purchaseOrders)): ?>
                <?php foreach($purchaseOrders as $po): ?>
                    <tr>
                        <td><?= esc($po['po_number'] ?? '—') ?></td>
                        <td><?= esc($po['godown_name'] ?? '—') ?></td>
                        <td><?= !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—' ?></td>
                        <td><?= esc($po['status'] ?? '—') ?></td>
                        <td class="text-end"><?= number_format((float)($po['total_amount'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($po['final_amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No purchase orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Stock Receipts -->
    <h2>Stock Receipts</h2>
    <table>
        <thead>
            <tr>
                <th>Receipt Number</th>
                <th>PO Number</th>
                <th>Godown</th>
                <th>Receipt Date</th>
                <th class="text-end">Total Items</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($stockReceipts)): ?>
                <?php foreach($stockReceipts as $receipt): ?>
                    <tr>
                        <td><?= esc($receipt['receipt_number'] ?? '—') ?></td>
                        <td><?= esc($receipt['po_number'] ?? '—') ?></td>
                        <td><?= esc($receipt['godown_name'] ?? '—') ?></td>
                        <td><?= !empty($receipt['receipt_date']) ? date('d M Y', strtotime($receipt['receipt_date'])) : '—' ?></td>
                        <td class="text-end"><?= (int)($receipt['total_items'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No stock receipts found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Stock Inventory -->
    <h2>Stock Inventory (<?= $summary['total_inventory_items'] ?? 0 ?> items, Value: <?= number_format($summary['total_inventory_value'] ?? 0, 2) ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>QR Code</th>
                <th>Product</th>
                <th>Product Code</th>
                <th>Batch Number</th>
                <th>Location Type</th>
                <th>Location</th>
                <th class="text-end">Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($stockInventory)): ?>
                <?php foreach($stockInventory as $inv): ?>
                    <tr>
                        <td><?= esc($inv['qr_code'] ?? '—') ?></td>
                        <td><?= esc($inv['product_name'] ?? '—') ?></td>
                        <td><?= esc($inv['product_code'] ?? '—') ?></td>
                        <td><?= esc($inv['batch_number'] ?? '—') ?></td>
                        <td><?= esc($inv['location_type'] ?? '—') ?></td>
                        <td><?= esc($inv['godown_name'] ?? $inv['shop_name'] ?? '—') ?></td>
                        <td class="text-end"><?= number_format((float)($inv['quantity'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No inventory found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

