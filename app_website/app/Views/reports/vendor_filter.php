<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-building-line me-2"></i>Vendor Reports
                    </h4>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generate Vendor Report</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= site_url('reports/vendor') ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold required">Vendor</label>
                                <select class="form-select" id="vendor-select" name="vendor_id" required>
                                    <option value="">Select vendor</option>
                                    <?php foreach(($vendorOptions ?? array()) as $v): ?>
                                        <option value="<?= $v['vendor_id'] ?>">
                                            <?= esc($v['company_name'] ?? ('Vendor #'.$v['vendor_id'])) ?>
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
                                <a href="<?= site_url('reports/vendor') ?>" class="btn btn-outline-secondary ms-2">
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

