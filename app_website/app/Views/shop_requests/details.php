<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $items = $formdata['items'] ?? array();
    if (!is_array($items)) {
        $items = array();
    }
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-file-list-3-line me-2"></i>
                        <?= $formdata['mode'] == 'new' ? 'Add Shop Request' : 'Edit Shop Request' ?>
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('shop-requests') ?>" class="btn btn-outline-dark btn-label ms-2">
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

            <form class="needs-validation" novalidate method="post" action="<?= site_url('shop-requests/'.($formdata['mode'] == 'new' ? 'new' : 'edit/'.$formdata['id'])) ?>">
                <?= csrf_field() ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Request Number</label>
                                <input type="text" class="form-control" name="request_number" value="<?= esc($formdata['request_number']) ?>" placeholder="Auto-generated" required <?= $formdata['mode'] == 'new' ? 'readonly' : '' ?>>
                                <div class="invalid-feedback">Please enter request number.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Shop</label>
                                <select class="form-select" name="shop_id" required>
                                    <option value="">Select shop</option>
                                    <?php foreach(($shopOptions ?? array()) as $s): ?>
                                        <option value="<?= $s['shop_id'] ?>" <?= ((string)$formdata['shop_id'] === (string)$s['shop_id']) ? 'selected' : '' ?>>
                                            <?= esc($s['shop_name'] ?? ('Shop #'.$s['shop_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Godown</label>
                                <select class="form-select" name="godown_id" required>
                                    <option value="">Select godown</option>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option value="<?= $g['godown_id'] ?>" <?= ((string)$formdata['godown_id'] === (string)$g['godown_id']) ? 'selected' : '' ?>>
                                            <?= esc($g['godown_name'] ?? ('Godown #'.$g['godown_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="">Select</option>
                                    <?php foreach(($statusOptions ?? array()) as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= (string)$formdata['status'] === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Request Date</label>
                                <input type="date" class="form-control" name="request_date" value="<?= esc($formdata['request_date']) ?>" required>
                                <div class="invalid-feedback">Please select request date.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Required Date</label>
                                <input type="date" class="form-control" name="required_date" value="<?= esc($formdata['required_date']) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Enter notes"><?= esc($formdata['notes']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle" id="request-items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40%;">Product</th>
                                        <th style="width:15%;">Qty</th>
                                        <th style="width:15%;">Priority</th>
                                        <th style="width:30%;">Fulfilled Qty</th>
                                        <th style="width:5%;" class="text-center"><i class="ri-more-2-line"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($items)): ?>
                                        <?php foreach($items as $it): ?>
                                            <tr class="request-item-row">
                                                <td>
                                                    <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                        <option value=""></option>
                                                        <?php if(!empty($it['product_id'])): ?>
                                                            <option value="<?= esc($it['product_id']) ?>" selected><?= esc($it['product_text'] ?? ('Product #'.$it['product_id'])) ?></option>
                                                        <?php endif; ?>
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="<?= esc($it['quantity'] ?? '1') ?>" step="0.01" min="0"></td>
                                                <td><input type="number" class="form-control item-priority" name="item_priority[]" value="<?= esc($it['priority'] ?? '0') ?>" step="1" min="0" max="10" placeholder="0"></td>
                                                <td>
                                                    <?php if($formdata['mode'] == 'edit'): ?>
                                                        <input type="number" class="form-control item-fulfilled-qty" name="item_fulfilled_quantity[]" value="<?= esc($it['fulfilled_quantity'] ?? '0') ?>" step="0.01" min="0" readonly style="background-color: #f8f9fa;">
                                                    <?php else: ?>
                                                        <span class="text-muted">0.00</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="request-item-row">
                                            <td>
                                                <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                    <option value=""></option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="1" step="0.01" min="0"></td>
                                            <td><input type="number" class="form-control item-priority" name="item_priority[]" value="0" step="1" min="0" max="10" placeholder="0"></td>
                                            <td><span class="text-muted">0.00</span></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-start">
                                            <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">
                                                <i class="ri-add-line me-1"></i> Add Item
                                            </button>
                                        </th>
                                        <th colspan="3" class="text-end">Total Items: <span id="total-items-count">0</span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="<?= site_url('shop-requests'); ?>" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-loading="Saving...">
                        <?= $formdata['mode'] == 'new' ? 'Create Request' : 'Update Request' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

