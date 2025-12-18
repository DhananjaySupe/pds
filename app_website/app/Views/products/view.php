<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $statusClass = ((string) ($product['is_active'] ?? '0') === '1') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted';
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-shopping-bag-3-line me-2"></i>Product Details</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= site_url('products/edit/'.$product['product_id']) ?>" class="btn btn-secondary btn-label">
                            <i class="ri-pencil-line label-icon align-middle fs-16 me-2"></i> Edit
                        </a>
                        <a href="<?= site_url('products') ?>" class="btn btn-outline-dark btn-label">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row g-3 align-items-stretch">
                <div class="col-lg-12 d-flex">
                    <div class="card w-100 h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Product Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Product Name</p>
                                    <h6><?= !empty($product['product_name']) ? esc($product['product_name']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Product Code</p>
                                    <h6><?= !empty($product['product_code']) ? esc($product['product_code']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Category</p>
                                    <h6><?= !empty($product['category']) ? esc($product['category']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Brand</p>
                                    <h6><?= !empty($product['brand']) ? esc($product['brand']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Unit Type</p>
                                    <h6><?= !empty($product['unit_type']) ? esc($product['unit_type']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Base Unit</p>
                                    <h6><?= !empty($product['base_unit']) ? esc($product['base_unit']) : '—' ?></h6>
                                </div>

                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Standard Quantity / Unit</p>
                                    <h6><?= (isset($product['standard_quantity_per_unit']) && $product['standard_quantity_per_unit'] !== '' && $product['standard_quantity_per_unit'] !== null) ? esc($product['standard_quantity_per_unit']) : '—' ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Min Stock Level</p>
                                    <h6><?= (isset($product['min_stock_level']) && $product['min_stock_level'] !== '' && $product['min_stock_level'] !== null) ? esc($product['min_stock_level']) : '—' ?></h6>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Max Stock Level</p>
                                    <h6><?= (isset($product['max_stock_level']) && $product['max_stock_level'] !== '' && $product['max_stock_level'] !== null) ? esc($product['max_stock_level']) : '—' ?></h6>
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Status</p>
                                    <h6><span class="badge <?= $statusClass ?>"><?= ((string) ($product['is_active'] ?? '0') === '1') ? 'Active' : 'Inactive' ?></span></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Created At</p>
                                    <h6><?= !empty($product['created_at']) ? date('d M Y h:i A', strtotime($product['created_at'])) : '—' ?></h6>
                                </div>

                                <div class="col-12">
                                    <p class="text-muted mb-1">Description</p>
                                    <h6 class="mb-0"><?= !empty($product['description']) ? nl2br(esc($product['description'])) : '—' ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>


