<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-building-line me-2"></i>Vendor Report
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('reports/export?type=vendor&entity_type=vendor&entity_id='.($vendor['vendor_id'] ?? 0).'&start_date='.$startDate.'&end_date='.$endDate) ?>"
                           class="btn btn-success btn-label ms-2">
                            <i class="ri-file-download-line label-icon align-middle fs-16 me-2"></i> Export Excel
                        </a>
                        <a href="<?= site_url('reports/pdf-export?type=vendor&entity_type=vendor&entity_id='.($vendor['vendor_id'] ?? 0).'&start_date='.$startDate.'&end_date='.$endDate) ?>"
                           class="btn btn-danger btn-label ms-2">
                            <i class="ri-file-pdf-line label-icon align-middle fs-16 me-2"></i> Export PDF
                        </a>
                        <a href="<?= site_url('reports/vendor') ?>" class="btn btn-outline-dark btn-label ms-2">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Vendor Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vendor Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Company Name</p>
                            <h6><?= esc($vendor['company_name'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Contact Person</p>
                            <h6><?= esc($vendor['full_name'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Email</p>
                            <h6><?= esc($vendor['email'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Phone</p>
                            <h6><?= esc($vendor['phone'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">GST Number</p>
                            <h6><?= esc($vendor['gst_number'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">PAN Number</p>
                            <h6><?= esc($vendor['pan_number'] ?? '—') ?></h6>
                        </div>
                        <?php if($startDate || $endDate): ?>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Report Period</p>
                                <h6><?= $startDate ? date('d M Y', strtotime($startDate)) : 'Start' ?> - <?= $endDate ? date('d M Y', strtotime($endDate)) : 'End' ?></h6>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_purchase_orders'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Purchase Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= number_format($summary['total_purchase_amount'] ?? 0, 2) ?></h3>
                            <p class="text-muted mb-0">Total Purchase Amount</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_receipts'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Stock Receipts</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_qr_codes'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">QR Codes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Orders -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Purchase Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
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
                                            <td><a href="<?= site_url('purchase-orders/view/'.$po['po_id']) ?>" class="text-reset"><?= esc($po['po_number'] ?? '—') ?></a></td>
                                            <td><?= esc($po['godown_name'] ?? '—') ?></td>
                                            <td><?= !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—' ?></td>
                                            <td><span class="badge bg-info-subtle text-info"><?= esc($po['status'] ?? '—') ?></span></td>
                                            <td class="text-end"><?= number_format((float)($po['total_amount'] ?? 0), 2) ?></td>
                                            <td class="text-end fw-semibold"><?= number_format((float)($po['final_amount'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No purchase orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stock Receipts -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock Receipts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
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
                    </div>
                </div>
            </div>

            <!-- Stock Inventory -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock Inventory (<?= $summary['total_inventory_items'] ?? 0 ?> items, Value: <?= number_format($summary['total_inventory_value'] ?? 0, 2) ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

