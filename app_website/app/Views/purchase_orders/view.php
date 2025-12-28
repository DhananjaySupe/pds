<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $po = $po ?? array();
    $items = $items ?? array();
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-file-list-3-line me-2"></i>Purchase Order Details</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= site_url('purchase-orders/qrcodes/'.$po['po_id']) ?>" class="btn btn-info btn-label">
                            <i class="ri-qr-code-line label-icon align-middle fs-16 me-2"></i> View QR Codes
                        </a>
                        <a href="<?= site_url('purchase-orders/edit/'.$po['po_id']) ?>" class="btn btn-secondary btn-label">
                            <i class="ri-pencil-line label-icon align-middle fs-16 me-2"></i> Edit
                        </a>
                        <a href="<?= site_url('purchase-orders') ?>" class="btn btn-outline-dark btn-label">
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
                            <h5 class="card-title mb-0">Order Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">PO Number</p>
                                    <h6><?= esc($po['po_number'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Vendor</p>
                                    <h6><?= esc($po['vendor_company_name'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Status</p>
                                    <h6><span class="badge bg-info-subtle text-info text-uppercase"><?= esc($statusLabel ?? ($po['status'] ?? '—')) ?></span></h6>
                                </div>

                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Order Date</p>
                                    <h6><?= !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Expected Delivery</p>
                                    <h6><?= !empty($po['expected_delivery_date']) ? date('d M Y', strtotime($po['expected_delivery_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Actual Delivery</p>
                                    <h6><?= !empty($po['actual_delivery_date']) ? date('d M Y', strtotime($po['actual_delivery_date'])) : '—' ?></h6>
                                </div>

                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Sub Total</p>
                                    <h6><?= isset($po['total_amount']) ? number_format((float)$po['total_amount'], 2) : '0.00' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Tax</p>
                                    <h6><?= isset($po['tax_amount']) ? number_format((float)$po['tax_amount'], 2) : '0.00' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Discount</p>
                                    <h6><?= isset($po['discount_amount']) ? number_format((float)$po['discount_amount'], 2) : '0.00' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Final</p>
                                    <h6 class="fw-semibold"><?= isset($po['final_amount']) ? number_format((float)$po['final_amount'], 2) : '0.00' ?></h6>
                                </div>

                                <div class="col-12">
                                    <p class="text-muted mb-1">Notes</p>
                                    <h6 class="mb-0"><?= !empty($po['notes']) ? nl2br(esc($po['notes'])) : '—' ?></h6>
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
                                            <th>Product</th>
                                            <th>Code</th>
                                            <th class="text-end">Tax</th>
                                            <th class="text-end">Discount</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($items)): ?>
                                            <?php foreach($items as $it): ?>
                                                <tr>
                                                    <td><?= esc($it['product_name'] ?? '—') ?></td>
                                                    <td><?= esc($it['product_code'] ?? '—') ?></td>
                                                    <td class="text-end"><?= (isset($it['tax_amount']) && $it['tax_amount'] !== null && $it['tax_amount'] !== '') ? number_format((float)$it['tax_amount'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= (isset($it['discount_amount']) && $it['discount_amount'] !== null && $it['discount_amount'] !== '') ? number_format((float)$it['discount_amount'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['quantity']) ? number_format((float)$it['quantity'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['unit_price']) ? number_format((float)$it['unit_price'], 2) : '0.00' ?></td>
                                                    <td class="text-end fw-semibold"><?= isset($it['total_price']) ? number_format((float)$it['total_price'], 2) : '0.00' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No items</td>
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


