<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $transfer = $transfer ?? array();
    $items = $items ?? array();
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-truck-line me-2"></i>Stock Transfer Details</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= site_url('stock-transfers/edit/'.$transfer['transfer_id']) ?>" class="btn btn-secondary btn-label">
                            <i class="ri-pencil-line label-icon align-middle fs-16 me-2"></i> Edit
                        </a>
                        <a href="<?= site_url('stock-transfers') ?>" class="btn btn-outline-dark btn-label">
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
                            <h5 class="card-title mb-0">Transfer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Transfer Number</p>
                                    <h6><?= esc($transfer['transfer_number'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">From Location</p>
                                    <h6><?= esc($fromLocation ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">To Location</p>
                                    <h6><?= esc($toLocation ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Status</p>
                                    <h6><span class="badge bg-info-subtle text-info text-uppercase"><?= esc($transfer['status'] ?? '—') ?></span></h6>
                                </div>

                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Dispatch Date</p>
                                    <h6><?= !empty($transfer['dispatch_date']) ? date('d M Y', strtotime($transfer['dispatch_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Delivery Date</p>
                                    <h6><?= !empty($transfer['delivery_date']) ? date('d M Y', strtotime($transfer['delivery_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Transporter Name</p>
                                    <h6><?= esc($transfer['transporter_name'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Vehicle Number</p>
                                    <h6><?= esc($transfer['vehicle_number'] ?? '—') ?></h6>
                                </div>

                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Total Items</p>
                                    <h6><?= isset($transfer['total_items']) ? (int) $transfer['total_items'] : '0' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Request Number</p>
                                    <h6><?= esc($transfer['request_number'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Created At</p>
                                    <h6><?= !empty($transfer['created_at']) ? date('d M Y H:i', strtotime($transfer['created_at'])) : '—' ?></h6>
                                </div>

                                <div class="col-12">
                                    <p class="text-muted mb-1">Notes</p>
                                    <h6 class="mb-0"><?= !empty($transfer['notes']) ? nl2br(esc($transfer['notes'])) : '—' ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>QR Code</th>
                                            <th>Product</th>
                                            <th>Code</th>
                                            <th class="text-end">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($items)): ?>
                                            <?php foreach($items as $it): ?>
                                                <tr>
                                                    <td><?= esc($it['qr_code'] ?? '—') ?></td>
                                                    <td><?= esc($it['product_name'] ?? '—') ?></td>
                                                    <td><?= esc($it['product_code'] ?? '—') ?></td>
                                                    <td class="text-end"><?= isset($it['quantity']) ? number_format((float)$it['quantity'], 2) : '0.00' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No items</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

