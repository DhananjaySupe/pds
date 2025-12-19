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
                        <i class="ri-truck-line me-2"></i>
                        <?= $formdata['mode'] == 'new' ? 'Add Stock Transfer' : 'Edit Stock Transfer' ?>
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('stock-transfers') ?>" class="btn btn-outline-dark btn-label ms-2">
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

            <form class="needs-validation" novalidate method="post" action="<?= site_url('stock-transfers/'.($formdata['mode'] == 'new' ? 'new' : 'edit/'.$formdata['id'])) ?>">
                <?= csrf_field() ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transfer Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Transfer Number</label>
                                <input type="text" class="form-control" name="transfer_number" value="<?= esc($formdata['transfer_number']) ?>" placeholder="Auto-generated" required <?= $formdata['mode'] == 'new' ? 'readonly' : '' ?>>
                                <div class="invalid-feedback">Please enter transfer number.</div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold required">From Location Type</label>
                                <select class="form-select" id="from_location_type" name="from_location_type" required>
                                    <option value="godown" <?= (string)$formdata['from_location_type'] === 'godown' ? 'selected' : '' ?>>Godown</option>
                                    <option value="shop" <?= (string)$formdata['from_location_type'] === 'shop' ? 'selected' : '' ?>>Shop</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">From Location</label>
                                <select class="form-select" id="from_location_id" name="from_location_id" required>
                                    <option value="">Select location</option>
                                    <?php foreach(($shopOptions ?? array()) as $s): ?>
                                        <option data-type="shop" value="<?= $s['shop_id'] ?>" <?= ((string)$formdata['from_location_type'] === 'shop' && (string)$formdata['from_location_id'] === (string)$s['shop_id']) ? 'selected' : '' ?>>
                                            <?= esc($s['shop_name'] ?? ('Shop #'.$s['shop_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option data-type="godown" value="<?= $g['godown_id'] ?>" <?= ((string)$formdata['from_location_type'] === 'godown' && (string)$formdata['from_location_id'] === (string)$g['godown_id']) ? 'selected' : '' ?>>
                                            <?= esc($g['godown_name'] ?? ('Godown #'.$g['godown_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold required">To Location Type</label>
                                <select class="form-select" id="to_location_type" name="to_location_type" required>
                                    <option value="shop" <?= (string)$formdata['to_location_type'] === 'shop' ? 'selected' : '' ?>>Shop</option>
                                    <option value="godown" <?= (string)$formdata['to_location_type'] === 'godown' ? 'selected' : '' ?>>Godown</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold required">To Location</label>
                                <select class="form-select" id="to_location_id" name="to_location_id" required>
                                    <option value="">Select location</option>
                                    <?php foreach(($shopOptions ?? array()) as $s): ?>
                                        <option data-type="shop" value="<?= $s['shop_id'] ?>" <?= ((string)$formdata['to_location_type'] === 'shop' && (string)$formdata['to_location_id'] === (string)$s['shop_id']) ? 'selected' : '' ?>>
                                            <?= esc($s['shop_name'] ?? ('Shop #'.$s['shop_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option data-type="godown" value="<?= $g['godown_id'] ?>" <?= ((string)$formdata['to_location_type'] === 'godown' && (string)$formdata['to_location_id'] === (string)$g['godown_id']) ? 'selected' : '' ?>>
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
                                <label class="form-label fw-semibold">Dispatch Date</label>
                                <input type="date" class="form-control" name="dispatch_date" value="<?= esc($formdata['dispatch_date']) ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Delivery Date</label>
                                <input type="date" class="form-control" name="delivery_date" value="<?= esc($formdata['delivery_date']) ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Transporter Name</label>
                                <input type="text" class="form-control" name="transporter_name" value="<?= esc($formdata['transporter_name']) ?>" placeholder="Enter transporter name">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vehicle Number</label>
                                <input type="text" class="form-control" name="vehicle_number" value="<?= esc($formdata['vehicle_number']) ?>" placeholder="Enter vehicle number">
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
                            <table class="table table-striped align-middle" id="transfer-items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:20%;">QR Code</th>
                                        <th style="width:30%;">Product</th>
                                        <th style="width:10%;">Qty</th>
                                        <th style="width:40%;">Available Stock</th>
                                        <th style="width:5%;" class="text-center"><i class="ri-more-2-line"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($items)): ?>
                                        <?php foreach($items as $it): ?>
                                            <tr class="transfer-item-row">
                                                <td>
                                                    <select class="form-select item-qr js-qr-select" name="item_qr_id[]">
                                                        <option value=""></option>
                                                        <?php if(!empty($it['qr_id'])): ?>
                                                            <option value="<?= esc($it['qr_id']) ?>" selected><?= esc($it['qr_text'] ?? ($it['qr_code'] ?? ('QR #'.$it['qr_id']))) ?></option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <input type="hidden" class="item-source-stock-id" name="item_source_stock_id[]" value="<?= esc($it['source_stock_id'] ?? '') ?>">
                                                </td>
                                                <td>
                                                    <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                        <option value=""></option>
                                                        <?php if(!empty($it['product_id'])): ?>
                                                            <option value="<?= esc($it['product_id']) ?>" selected><?= esc($it['product_text'] ?? ('Product #'.$it['product_id'])) ?></option>
                                                        <?php endif; ?>
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="<?= esc($it['quantity'] ?? '1') ?>" step="0.01" min="0"></td>
                                                <td class="item-stock-info text-muted small">—</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="transfer-item-row">
                                            <td>
                                                <select class="form-select item-qr js-qr-select" name="item_qr_id[]">
                                                    <option value=""></option>
                                                </select>
                                                <input type="hidden" class="item-source-stock-id" name="item_source_stock_id[]" value="">
                                            </td>
                                            <td>
                                                <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                    <option value=""></option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="1" step="0.01" min="0"></td>
                                            <td class="item-stock-info text-muted small">—</td>
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
                    <a href="<?= site_url('stock-transfers'); ?>" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-loading="Saving...">
                        <?= $formdata['mode'] == 'new' ? 'Create Transfer' : 'Update Transfer' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

