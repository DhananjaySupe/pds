<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $sale = $sale ?? array();
    $items = $items ?? array();
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-shopping-cart-2-line me-2"></i>Sale Details</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= site_url('sales/edit/'.$sale['sale_id']) ?>" class="btn btn-secondary btn-label">
                            <i class="ri-pencil-line label-icon align-middle fs-16 me-2"></i> Edit
                        </a>
                        <a href="<?= site_url('sales') ?>" class="btn btn-outline-dark btn-label">
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
                            <h5 class="card-title mb-0">Sale Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Invoice</p>
                                    <h6><?= esc($sale['invoice_number'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Customer</p>
                                    <h6><?= esc($sale['customer_name'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Payment Status</p>
                                    <h6><span class="badge bg-info-subtle text-info text-uppercase"><?= esc($sale['payment_status'] ?? '—') ?></span></h6>
                                </div>

                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Sale Date</p>
                                    <h6><?= !empty($sale['sale_date']) ? date('d M Y', strtotime($sale['sale_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Payment Method</p>
                                    <h6><?= esc($sale['payment_method'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Sold By</p>
                                    <h6><?= esc($sale['sold_by_name'] ?? '—') ?></h6>
                                </div>

                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Sub Total</p>
                                    <h6><?= isset($sale['total_amount']) ? number_format((float)$sale['total_amount'], 2) : '0.00' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Tax</p>
                                    <h6><?= isset($sale['tax_amount']) ? number_format((float)$sale['tax_amount'], 2) : '0.00' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Discount</p>
                                    <h6><?= isset($sale['discount_amount']) ? number_format((float)$sale['discount_amount'], 2) : '0.00' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Final</p>
                                    <h6 class="fw-semibold"><?= isset($sale['final_amount']) ? number_format((float)$sale['final_amount'], 2) : '0.00' ?></h6>
                                </div>

                                <div class="col-12">
                                    <p class="text-muted mb-1">Notes</p>
                                    <h6 class="mb-0"><?= !empty($sale['notes']) ? nl2br(esc($sale['notes'])) : '—' ?></h6>
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
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Disc %</th>
                                            <th class="text-end">Tax %</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($items)): ?>
                                            <?php foreach($items as $it): ?>
                                                <tr>
                                                    <td><?= esc($it['product_name'] ?? '—') ?></td>
                                                    <td><?= esc($it['product_code'] ?? '—') ?></td>
                                                    <td class="text-end"><?= isset($it['quantity']) ? number_format((float)$it['quantity'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['unit_price']) ? number_format((float)$it['unit_price'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['discount_percent']) ? number_format((float)$it['discount_percent'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['tax_percent']) ? number_format((float)$it['tax_percent'], 2) : '0.00' ?></td>
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


