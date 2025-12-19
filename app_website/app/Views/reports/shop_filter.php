<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-shopping-bag-line me-2"></i>Shop Reports
                    </h4>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generate Shop Report</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= site_url('reports/shop') ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold required">Shop</label>
                                <select class="form-select" id="shop-select" name="shop_id" required>
                                    <option value="">Select shop</option>
                                    <?php foreach(($shopOptions ?? array()) as $s): ?>
                                        <option value="<?= $s['shop_id'] ?>">
                                            <?= esc($s['shop_name'] ?? ('Shop #'.$s['shop_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?= esc($startDate) ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?= esc($endDate) ?>">
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-file-chart-line me-1"></i> Generate Report
                                </button>
                                <a href="<?= site_url('reports/shop') ?>" class="btn btn-outline-secondary ms-2">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

