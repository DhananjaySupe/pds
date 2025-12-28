<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $po = $po ?? array();
    $qrCodes = $qrCodes ?? array();
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-qr-code-line me-2"></i>Purchase Order QR Codes</h4>
                    <div class="page-title-right d-flex gap-2">
                        <button type="button" onclick="window.print()" class="btn btn-primary btn-label">
                            <i class="ri-printer-line label-icon align-middle fs-16 me-2"></i> Print
                        </button>
                        <a href="<?= site_url('purchase-orders/qrcodes-pdf/'.$po['po_id']) ?>" class="btn btn-success btn-label" target="_blank">
                            <i class="ri-file-pdf-line label-icon align-middle fs-16 me-2"></i> PDF
                        </a>
                        <a href="<?= site_url('purchase-orders/view/'.$po['po_id']) ?>" class="btn btn-outline-dark btn-label">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Purchase Order Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">PO Number</p>
                                    <h6><?= esc($po['po_number'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Vendor</p>
                                    <h6><?= esc($po['vendor_company_name'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Order Date</p>
                                    <h6><?= !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Total QR Codes</p>
                                    <h6><?= count($qrCodes) ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">QR Codes</h5>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($qrCodes)): ?>
                                <div class="row g-3" id="qr-codes-container">
                                    <?php foreach($qrCodes as $qr): ?>
                                        <div class="col-md-3 col-sm-4 col-6 qr-code-item">
                                            <div class="card border">
                                                <div class="card-body text-center p-3">
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($qr['qr_code']) ?>" alt="QR Code" class="img-fluid mb-2" style="max-width: 150px;">
                                                    <p class="mb-1 small text-muted"><?= esc($qr['product_code'] ?? '—') ?></p>
                                                    <p class="mb-1 fw-semibold small"><?= esc($qr['product_name'] ?? '—') ?></p>
                                                    <p class="mb-0 small text-muted" style="font-size: 0.7rem; word-break: break-all;"><?= esc($qr['qr_code']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">No QR codes found for this purchase order.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .page-title-box,
    .btn,
    .card-header {
        display: none !important;
    }
    .qr-code-item {
        page-break-inside: avoid;
        margin-bottom: 20px;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

<?php $this->endSection(); ?>

