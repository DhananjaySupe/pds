<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $request = $request ?? array();
    $items = $items ?? array();
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-file-list-3-line me-2"></i>Shop Request Details</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= site_url('shop-requests/edit/'.$request['request_id']) ?>" class="btn btn-secondary btn-label">
                            <i class="ri-pencil-line label-icon align-middle fs-16 me-2"></i> Edit
                        </a>
                        <a href="<?= site_url('shop-requests') ?>" class="btn btn-outline-dark btn-label">
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
                            <h5 class="card-title mb-0">Request Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Request Number</p>
                                    <h6><?= esc($request['request_number'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Shop</p>
                                    <h6><?= esc($request['shop_name'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Godown</p>
                                    <h6><?= esc($request['godown_name'] ?? '—') ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Status</p>
                                    <h6><span class="badge bg-info-subtle text-info text-uppercase"><?= esc($request['status'] ?? '—') ?></span></h6>
                                </div>

                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Request Date</p>
                                    <h6><?= !empty($request['request_date']) ? date('d M Y', strtotime($request['request_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Required Date</p>
                                    <h6><?= !empty($request['required_date']) ? date('d M Y', strtotime($request['required_date'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Total Items</p>
                                    <h6><?= isset($request['total_items']) ? (int) $request['total_items'] : '0' ?></h6>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Created At</p>
                                    <h6><?= !empty($request['created_at']) ? date('d M Y H:i', strtotime($request['created_at'])) : '—' ?></h6>
                                </div>

                                <div class="col-12">
                                    <p class="text-muted mb-1">Notes</p>
                                    <h6 class="mb-0"><?= !empty($request['notes']) ? nl2br(esc($request['notes'])) : '—' ?></h6>
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
                                            <th class="text-end">Quantity</th>
                                            <th class="text-end">Fulfilled</th>
                                            <th class="text-end">Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($items)): ?>
                                            <?php foreach($items as $it): ?>
                                                <tr>
                                                    <td><?= esc($it['product_name'] ?? '—') ?></td>
                                                    <td><?= esc($it['product_code'] ?? '—') ?></td>
                                                    <td class="text-end"><?= isset($it['quantity']) ? number_format((float)$it['quantity'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['fulfilled_quantity']) ? number_format((float)$it['fulfilled_quantity'], 2) : '0.00' ?></td>
                                                    <td class="text-end"><?= isset($it['priority']) ? (int) $it['priority'] : '0' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No items</td>
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

