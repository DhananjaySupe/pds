<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Sales</h4>
                    <div class="page-title-right">
                        <button type="button" id="btn-export-sales" class="btn btn-soft-success btn-label">
                            <i class="ri-file-download-line label-icon align-middle fs-16 me-2"></i> Export
                        </button>
                        <a href="<?= site_url('sales/new'); ?>" class="btn btn-primary btn-label ms-2">
                            <i class="ri-add-line label-icon align-middle fs-16 me-2"></i> Add
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Search</label>
                <div class="form-icon">
                    <input type="text" class="form-control form-control-icon" id="filter-keywords" placeholder="Search by invoice, customer, phone or notes" value="<?= esc($filters['keywords']); ?>">
                    <i class="ri-search-line"></i>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Payment Status</label>
                <select class="form-select" id="filter-payment-status">
                    <option value="">All</option>
                    <?php foreach(($paymentStatusOptions ?? array()) as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (string)$filters['payment_status'] === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger w-100" id="filter-reset">
                    <i class="ri-refresh-line me-1"></i> Reset
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive  min-height-card-body">
                    <table id="sales-grid" class="table dt-responsive w-100 align-middle table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Sale Date</th>
                                <th class="text-end">Final</th>
                                <th>Created</th>
                                <th class="text-center"><i class="ri-more-2-line"></i></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var nRecordsTotal = 0;
    var nRecordsFiltered = 0;
    var oFilter = {
        keywords : '<?= esc($filters['keywords']); ?>',
        payment_status : '<?= esc($filters['payment_status']); ?>'
    };
</script>

<?php $this->endSection(); ?>

<?php $this->section('javascripts'); ?>

<?php $this->endSection(); ?>


