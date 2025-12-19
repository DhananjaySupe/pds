<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-store-2-line me-2"></i>Shop Report
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('reports/export?type=shop&entity_type=shop&entity_id='.($shop['shop_id'] ?? 0).'&start_date='.$startDate.'&end_date='.$endDate) ?>"
                           class="btn btn-success btn-label ms-2">
                            <i class="ri-file-download-line label-icon align-middle fs-16 me-2"></i> Export Excel
                        </a>
                        <a href="<?= site_url('reports/pdf-export?type=shop&entity_type=shop&entity_id='.($shop['shop_id'] ?? 0).'&start_date='.$startDate.'&end_date='.$endDate) ?>"
                           class="btn btn-danger btn-label ms-2">
                            <i class="ri-file-pdf-line label-icon align-middle fs-16 me-2"></i> Export PDF
                        </a>
                        <a href="<?= site_url('reports/shop') ?>" class="btn btn-outline-dark btn-label ms-2">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Shop Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Shop Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Shop Name</p>
                            <h6><?= esc($shop['shop_name'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Location</p>
                            <h6><?= esc($shop['location'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Contact Person</p>
                            <h6><?= esc($shop['contact_person'] ?? '—') ?></h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Godown</p>
                            <h6><?= esc($shop['godown_name'] ?? '—') ?></h6>
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
                            <h3 class="mb-1"><?= $summary['total_sales'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Total Sales</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= number_format($summary['total_sales_amount'] ?? 0, 2) ?></h3>
                            <p class="text-muted mb-0">Sales Amount</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_inventory_items'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Inventory Items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= number_format($summary['total_inventory_value'] ?? 0, 2) ?></h3>
                            <p class="text-muted mb-0">Inventory Value</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_transfers_from'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Transfers Out</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_transfers_to'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Transfers In</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?= $summary['total_shop_requests'] ?? 0 ?></h3>
                            <p class="text-muted mb-0">Shop Requests</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sales (<?= $summary['total_sales'] ?? 0 ?> sales, Amount: <?= number_format($summary['total_sales_amount'] ?? 0, 2) ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Customer</th>
                                    <th>Sale Date</th>
                                    <th>Payment Status</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Final Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($sales)): ?>
                                    <?php foreach($sales as $sale): ?>
                                        <tr>
                                            <td><a href="<?= site_url('sales/view/'.$sale['sale_id']) ?>" class="text-reset"><?= esc($sale['invoice_number'] ?? '—') ?></a></td>
                                            <td><?= esc($sale['customer_name'] ?? '—') ?></td>
                                            <td><?= !empty($sale['sale_date']) ? date('d M Y', strtotime($sale['sale_date'])) : '—' ?></td>
                                            <td><span class="badge bg-info-subtle text-info"><?= esc($sale['payment_status'] ?? '—') ?></span></td>
                                            <td class="text-end"><?= number_format((float)($sale['total_amount'] ?? 0), 2) ?></td>
                                            <td class="text-end fw-semibold"><?= number_format((float)($sale['final_amount'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No sales found</td>
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
                    <h5 class="card-title mb-0">Stock Inventory (<?= $summary['total_inventory_items'] ?? 0 ?> items, Qty: <?= number_format($summary['total_inventory_quantity'] ?? 0, 2) ?>, Value: <?= number_format($summary['total_inventory_value'] ?? 0, 2) ?>)</h5>
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
                    </div>
                </div>
            </div>

            <!-- Stock Transfers -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock Transfers</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#transfers-from" role="tab">Transfers Out (<?= $summary['total_transfers_from'] ?? 0 ?>)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#transfers-to" role="tab">Transfers In (<?= $summary['total_transfers_to'] ?? 0 ?>)</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="transfers-from" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
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
                                                    <td><a href="<?= site_url('stock-transfers/view/'.$tf['transfer_id']) ?>" class="text-reset"><?= esc($tf['transfer_number'] ?? '—') ?></a></td>
                                                    <td><?= esc($tf['to_godown_name'] ?? $tf['to_shop_name'] ?? '—') ?></td>
                                                    <td><span class="badge bg-info-subtle text-info"><?= esc($tf['status'] ?? '—') ?></span></td>
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
                            </div>
                        </div>
                        <div class="tab-pane fade" id="transfers-to" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
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
                                            <?php foreach($transfersTo as $tt): ?>
                                                <tr>
                                                    <td><a href="<?= site_url('stock-transfers/view/'.$tt['transfer_id']) ?>" class="text-reset"><?= esc($tt['transfer_number'] ?? '—') ?></a></td>
                                                    <td><?= esc($tt['from_godown_name'] ?? $tt['from_shop_name'] ?? '—') ?></td>
                                                    <td><span class="badge bg-info-subtle text-info"><?= esc($tt['status'] ?? '—') ?></span></td>
                                                    <td><?= !empty($tt['dispatch_date']) ? date('d M Y', strtotime($tt['dispatch_date'])) : '—' ?></td>
                                                    <td class="text-end"><?= (int)($tt['total_items'] ?? 0) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No transfers found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shop Requests -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Shop Requests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Request Number</th>
                                    <th>Godown</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($shopRequests)): ?>
                                    <?php foreach($shopRequests as $req): ?>
                                        <tr>
                                            <td><a href="<?= site_url('shop-requests/view/'.$req['request_id']) ?>" class="text-reset"><?= esc($req['request_number'] ?? '—') ?></a></td>
                                            <td><?= esc($req['godown_name'] ?? '—') ?></td>
                                            <td><?= !empty($req['request_date']) ? date('d M Y', strtotime($req['request_date'])) : '—' ?></td>
                                            <td><span class="badge bg-info-subtle text-info"><?= esc($req['status'] ?? '—') ?></span></td>
                                            <td class="text-end"><?= (int)($req['total_items'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No shop requests found</td>
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

