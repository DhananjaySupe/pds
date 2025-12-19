<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-file-chart-line me-2"></i>Reports
                    </h4>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generate Report</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="" id="report-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Report Type</label>
                                <select class="form-select" id="report-type" name="type" required>
                                    <option value="">Select report type</option>
                                    <option value="vendor" <?= $reportType === 'vendor' ? 'selected' : '' ?>>Vendor Report</option>
                                    <option value="godown" <?= $reportType === 'godown' ? 'selected' : '' ?>>Godown Report</option>
                                    <option value="shop" <?= $reportType === 'shop' ? 'selected' : '' ?>>Shop Report</option>
                                </select>
                            </div>

                            <div class="col-md-3" id="vendor-select-container" style="display: none;">
                                <label class="form-label fw-semibold required">Vendor</label>
                                <select class="form-select" id="vendor-select" name="vendor_id">
                                    <option value="">Select vendor</option>
                                    <?php foreach(($vendorOptions ?? array()) as $v): ?>
                                        <option value="<?= $v['vendor_id'] ?>" <?= $entityType === 'vendor' && $entityId === (int)$v['vendor_id'] ? 'selected' : '' ?>>
                                            <?= esc($v['company_name'] ?? ('Vendor #'.$v['vendor_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3" id="godown-select-container" style="display: none;">
                                <label class="form-label fw-semibold required">Godown</label>
                                <select class="form-select" id="godown-select" name="godown_id">
                                    <option value="">Select godown</option>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option value="<?= $g['godown_id'] ?>" <?= $entityType === 'godown' && $entityId === (int)$g['godown_id'] ? 'selected' : '' ?>>
                                            <?= esc($g['godown_name'] ?? ('Godown #'.$g['godown_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3" id="shop-select-container" style="display: none;">
                                <label class="form-label fw-semibold required">Shop</label>
                                <select class="form-select" id="shop-select" name="shop_id">
                                    <option value="">Select shop</option>
                                    <?php foreach(($shopOptions ?? array()) as $s): ?>
                                        <option value="<?= $s['shop_id'] ?>" <?= $entityType === 'shop' && $entityId === (int)$s['shop_id'] ? 'selected' : '' ?>>
                                            <?= esc($s['shop_name'] ?? ('Shop #'.$s['shop_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?= esc($startDate) ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?= esc($endDate) ?>">
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-file-chart-line me-1"></i> Generate Report
                                </button>
                                <a href="<?= site_url('reports') ?>" class="btn btn-outline-secondary ms-2">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if(!empty($reportType) && !empty($entityType) && $entityId > 0): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Report Actions</h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $params = array('start_date' => $startDate, 'end_date' => $endDate);
                            if ($entityType === 'vendor') {
                                $params['vendor_id'] = $entityId;
                            } elseif ($entityType === 'godown') {
                                $params['godown_id'] = $entityId;
                            } elseif ($entityType === 'shop') {
                                $params['shop_id'] = $entityId;
                            }
                        ?>
                        <a href="<?= site_url('reports/'.$reportType.'?'.http_build_query($params)) ?>"
                           class="btn btn-primary me-2">
                            <i class="ri-eye-line me-1"></i> View Report
                        </a>
                        <a href="<?= site_url('reports/export?type='.$reportType.'&entity_type='.$entityType.'&entity_id='.$entityId.'&start_date='.$startDate.'&end_date='.$endDate) ?>"
                           class="btn btn-success">
                            <i class="ri-file-download-line me-1"></i> Export to Excel
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

