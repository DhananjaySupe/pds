<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Shop Requests</h4>
                    <div class="page-title-right">
                        <button type="button" id="btn-export-requests" class="btn btn-soft-success btn-label">
                            <i class="ri-file-download-line label-icon align-middle fs-16 me-2"></i> Export
                        </button>
                        <a href="<?= site_url('shop-requests/new'); ?>" class="btn btn-primary btn-label ms-2">
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
                    <input type="text" class="form-control form-control-icon" id="filter-keywords" placeholder="Search by request number, shop, godown" value="<?= esc($filters['keywords']); ?>">
                    <i class="ri-search-line"></i>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" id="filter-status">
                    <option value="">All</option>
                    <?php foreach(($statusOptions ?? array()) as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (string)$filters['status'] === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
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
                    <table id="requests-grid" class="table dt-responsive w-100 align-middle table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Request Number</th>
                                <th>Shop</th>
                                <th>Godown</th>
                                <th>Status</th>
                                <th>Request Date</th>
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
        status : '<?= esc($filters['status']); ?>'
    };
</script>

<?php $this->endSection(); ?>

<?php $this->section('javascripts'); ?>

<?php $this->endSection(); ?>

