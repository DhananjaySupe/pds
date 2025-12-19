<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-store-2-line me-2"></i>Godown Reports
                    </h4>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generate Godown Report</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= site_url('reports/godown') ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold required">Godown</label>
                                <select class="form-select" id="godown-select" name="godown_id" required>
                                    <option value="">Select godown</option>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option value="<?= $g['godown_id'] ?>">
                                            <?= esc($g['godown_name'] ?? ('Godown #'.$g['godown_id'])) ?>
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
                                <a href="<?= site_url('reports/godown') ?>" class="btn btn-outline-secondary ms-2">
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

