<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Godown Report</title>
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
    <h1>Godown Report</h1>

    <!-- Godown Information -->
    <div class="info-section">
        <h2>Godown Information</h2>
        <div class="info-row">
            <span class="info-label">Godown Name:</span>
            <span><?= esc($godown['godown_name'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Location:</span>
            <span><?= esc($godown['location'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Person:</span>
            <span><?= esc($godown['contact_person'] ?? '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Capacity (sqft):</span>
            <span><?= isset($godown['capacity_sqft']) ? number_format((float)$godown['capacity_sqft'], 2) : '—' ?></span>
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
            <div class="summary-value"><?= $summary['total_inventory_items'] ?? 0 ?></div>
            <div class="summary-label">Inventory Items</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= number_format($summary['total_inventory_value'] ?? 0, 2) ?></div>
            <div class="summary-label">Inventory Value</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= $summary['total_transfers_from'] ?? 0 ?></div>
            <div class="summary-label">Transfers Out</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= $summary['total_transfers_to'] ?? 0 ?></div>
            <div class="summary-label">Transfers In</div>
        </div>
        <div class="summary-box">
            <div class="summary-value"><?= $summary['total_shop_requests'] ?? 0 ?></div>
            <div class="summary-label">Shop Requests</div>
        </div>
    </div>

    <!-- Stock Inventory -->
    <h2>Stock Inventory (<?= $summary['total_inventory_items'] ?? 0 ?> items, Qty: <?= number_format($summary['total_inventory_quantity'] ?? 0, 2) ?>, Value: <?= number_format($summary['total_inventory_value'] ?? 0, 2) ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>QR Code</th>
                <th>Product</th>
                <th>Product Code</th>
                <th>Batch Number</th>
                <th class="text-end">Quantity</th>
                <th>Stock Date</th>
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
                        <td class="text-end"><?= number_format((float)($inv['quantity'] ?? 0), 2) ?></td>
                        <td><?= !empty($inv['stock_date']) ? date('d M Y', strtotime($inv['stock_date'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No inventory found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Purchase Orders -->
    <h2>Purchase Orders</h2>
    <table>
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Vendor</th>
                <th>Order Date</th>
                <th>Status</th>
                <th class="text-end">Final Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($purchaseOrders)): ?>
                <?php foreach($purchaseOrders as $po): ?>
                    <tr>
                        <td><?= esc($po['po_number'] ?? '—') ?></td>
                        <td><?= esc($po['vendor_company_name'] ?? '—') ?></td>
                        <td><?= !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—' ?></td>
                        <td><?= esc($po['status'] ?? '—') ?></td>
                        <td class="text-end"><?= number_format((float)($po['final_amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No purchase orders found</td>
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
                <th>Vendor</th>
                <th>Receipt Date</th>
                <th class="text-end">Total Items</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($receipts)): ?>
                <?php foreach($receipts as $receipt): ?>
                    <tr>
                        <td><?= esc($receipt['receipt_number'] ?? '—') ?></td>
                        <td><?= esc($receipt['po_number'] ?? '—') ?></td>
                        <td><?= esc($receipt['vendor_company_name'] ?? '—') ?></td>
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

    <!-- Stock Transfers Out -->
    <h2>Stock Transfers Out (<?= $summary['total_transfers_from'] ?? 0 ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Transfer Number</th>
                <th>To Location</th>
                <th>Status</th>
                <th>Dispatch Date</th>
                <th class="text-end">Total Items</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($transfersFrom)): ?>
                <?php foreach($transfersFrom as $tf): ?>
                    <tr>
                        <td><?= esc($tf['transfer_number'] ?? '—') ?></td>
                        <td><?= esc($tf['to_godown_name'] ?? $tf['to_shop_name'] ?? '—') ?></td>
                        <td><?= esc($tf['status'] ?? '—') ?></td>
                        <td><?= !empty($tf['dispatch_date']) ? date('d M Y', strtotime($tf['dispatch_date'])) : '—' ?></td>
                        <td class="text-end"><?= (int)($tf['total_items'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No transfers found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Stock Transfers In -->
    <h2>Stock Transfers In (<?= $summary['total_transfers_to'] ?? 0 ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Transfer Number</th>
                <th>From Location</th>
                <th>Status</th>
                <th>Dispatch Date</th>
                <th class="text-end">Total Items</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($transfersTo)): ?>
                <?php foreach($transfersTo as $tf): ?>
                    <tr>
                        <td><?= esc($tf['transfer_number'] ?? '—') ?></td>
                        <td><?= esc($tf['from_godown_name'] ?? $tf['from_shop_name'] ?? '—') ?></td>
                        <td><?= esc($tf['status'] ?? '—') ?></td>
                        <td><?= !empty($tf['dispatch_date']) ? date('d M Y', strtotime($tf['dispatch_date'])) : '—' ?></td>
                        <td class="text-end"><?= (int)($tf['total_items'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No transfers found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Shop Requests -->
    <h2>Shop Requests (<?= $summary['total_shop_requests'] ?? 0 ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Request Number</th>
                <th>Shop</th>
                <th>Request Date</th>
                <th>Required Date</th>
                <th>Status</th>
                <th class="text-end">Total Items</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($shopRequests)): ?>
                <?php foreach($shopRequests as $req): ?>
                    <tr>
                        <td><?= esc($req['request_number'] ?? '—') ?></td>
                        <td><?= esc($req['shop_name'] ?? '—') ?></td>
                        <td><?= !empty($req['request_date']) ? date('d M Y', strtotime($req['request_date'])) : '—' ?></td>
                        <td><?= !empty($req['required_date']) ? date('d M Y', strtotime($req['required_date'])) : '—' ?></td>
                        <td><?= esc($req['status'] ?? '—') ?></td>
                        <td class="text-end"><?= (int)($req['total_items'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No shop requests found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

