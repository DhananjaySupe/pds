<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Codes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .po-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
        }
        .po-info p {
            margin: 5px 0;
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        .qr-item {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            page-break-inside: avoid;
        }
        .qr-item img {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        .qr-item p {
            margin: 5px 0;
            font-size: 11px;
        }
        .qr-code-text {
            font-size: 9px;
            word-break: break-all;
            color: #666;
        }
        .product-name {
            font-weight: bold;
            font-size: 12px;
        }
        .product-code {
            color: #666;
            font-size: 10px;
        }
        @media print {
            .qr-item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Order QR Codes</h1>
    </div>

    <div class="po-info">
        <p><strong>Vendor:</strong> <?= esc($po['vendor_company_name'] ?? '—') ?></p>
        <p><strong>Order Date:</strong> <?= !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—' ?></p>
        <p><strong>Total QR Codes:</strong> <?= count($qrCodes ?? array()) ?></p>
    </div>

    <div class="qr-grid">
        <?php if(!empty($qrCodes)): ?>
            <?php foreach($qrCodes as $qr): ?>
                <div class="qr-item">
                    <?php if (!empty($qr['qr_image_base64'])): ?>
                        <img src="<?= esc($qr['qr_image_base64']) ?>" alt="QR Code">
                    <?php else: ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode($qr['qr_code']) ?>" alt="QR Code">
                    <?php endif; ?>
                    <p class="product-code"><?= esc($qr['product_code'] ?? '—') ?></p>
                    <p class="product-name"><?= esc($qr['product_name'] ?? '—') ?></p>
                    <p class="qr-code-text"><?= esc($qr['qr_code']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No QR codes found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

