<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-shopping-bag-3-line me-2"></i>
                        <?= $formdata['mode'] == 'new' ? 'Add Product' : 'Edit Product' ?>
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('products') ?>" class="btn btn-outline-dark btn-label ms-2">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <?php if($formdata['error'] != ''): ?>
                <div class="alert alert-danger">
                    <i class="ri-information-line me-2"></i><?= $formdata['error'] ?>
                </div>
            <?php endif; ?>

            <form class="needs-validation" novalidate method="post" action="<?= site_url('products/'.($formdata['mode'] == 'new' ? 'new' : 'edit/'.$formdata['id'])) ?>">
                <?= csrf_field() ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold required">Product Code</label>
                                <input type="text" class="form-control" name="product_code" value="<?= esc($formdata['product_code']) ?>" placeholder="Auto-generated" required <?= $formdata['mode'] == 'new' ? 'readonly' : '' ?>>
                                <div class="invalid-feedback">Please enter product code.</div>
                                <?php if($formdata['mode'] == 'new'): ?>
                                    <small class="text-muted d-block mt-1">Product code is auto-generated.</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold required">Product Name</label>
                                <input type="text" class="form-control" name="product_name" value="<?= esc($formdata['product_name']) ?>" placeholder="Enter product name" required>
                                <div class="invalid-feedback">Please enter product name.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category</label>
                                <input type="text" class="form-control" name="category" value="<?= esc($formdata['category']) ?>" placeholder="e.g. Grocery">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Brand</label>
                                <input type="text" class="form-control" name="brand" value="<?= esc($formdata['brand']) ?>" placeholder="e.g. Acme">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit Type</label>
                                <input type="text" class="form-control" name="unit_type" value="<?= esc($formdata['unit_type']) ?>" placeholder="e.g. Box / Pack / Kg">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Base Unit</label>
                                <input type="text" class="form-control" name="base_unit" value="<?= esc($formdata['base_unit']) ?>" placeholder="e.g. pcs / g / ml">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Standard Quantity / Unit</label>
                                <input type="number" class="form-control" name="standard_quantity_per_unit" value="<?= esc($formdata['standard_quantity_per_unit']) ?>" step="0.01" min="0" placeholder="e.g. 10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Min Stock Level</label>
                                <input type="number" class="form-control" name="min_stock_level" value="<?= esc($formdata['min_stock_level']) ?>" step="0.01" min="0" placeholder="e.g. 5">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Max Stock Level</label>
                                <input type="number" class="form-control" name="max_stock_level" value="<?= esc($formdata['max_stock_level']) ?>" step="0.01" min="0" placeholder="e.g. 100">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold required">Status</label>
                                <select class="form-select" name="is_active" required>
                                    <?php foreach($statusOptions as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $formdata['is_active'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select status.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Enter description"><?= esc($formdata['description']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="<?= site_url('products'); ?>" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-loading="Saving..."><?= $formdata['mode'] == 'new' ? 'Create Product' : 'Update Product' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>


