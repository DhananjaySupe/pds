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
                        <?= $formdata['mode'] == 'new' ? 'Add Purchase Order' : 'Edit Purchase Order' ?>
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('purchase-orders') ?>" class="btn btn-outline-dark btn-label ms-2">
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

            <form class="needs-validation" novalidate method="post" action="<?= site_url('purchase-orders/'.($formdata['mode'] == 'new' ? 'new' : 'edit/'.$formdata['id'])) ?>">
                <?= csrf_field() ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">PO Number</label>
                                <input type="text" class="form-control" name="po_number" value="<?= esc($formdata['po_number']) ?>" placeholder="Auto-generated" required <?= $formdata['mode'] == 'new' ? 'readonly' : '' ?>>
                                <div class="invalid-feedback">Please enter PO number.</div>
                                <?php if($formdata['mode'] == 'new'): ?>
                                    <small class="text-muted d-block mt-1">PO number is auto-generated.</small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Vendor</label>
                                <select class="form-select" name="vendor_id" required>
                                    <option value="">Select vendor</option>
                                    <?php foreach(($vendorOptions ?? array()) as $v): ?>
                                        <option value="<?= $v['vendor_id'] ?>" <?= (string)$formdata['vendor_id'] === (string)$v['vendor_id'] ? 'selected' : '' ?>>
                                            <?= esc($v['company_name'] ?? ('Vendor #' . $v['vendor_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select vendor.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Godown</label>
                                <select class="form-select" name="godown_id" required>
                                    <option value="">Select godown</option>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option value="<?= $g['godown_id'] ?>" <?= (string)$formdata['godown_id'] === (string)$g['godown_id'] ? 'selected' : '' ?>>
                                            <?= esc($g['godown_name'] ?? ('Godown #' . $g['godown_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select godown.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="">Select status</option>
                                    <?php foreach(($statusOptions ?? array()) as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= (string)$formdata['status'] === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select status.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Order Date</label>
                                <input type="date" class="form-control" name="order_date" value="<?= esc($formdata['order_date']) ?>" required>
                                <div class="invalid-feedback">Please select order date.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Expected Delivery</label>
                                <input type="date" class="form-control" name="expected_delivery_date" value="<?= esc($formdata['expected_delivery_date']) ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Actual Delivery</label>
                                <input type="date" class="form-control" name="actual_delivery_date" value="<?= esc($formdata['actual_delivery_date']) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Enter notes"><?= esc($formdata['notes']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle" id="po-items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:25%;">Product</th>
                                        <th style="width:10%;">Qty</th>
                                        <th style="width:10%;">Unit Price</th>
                                        <th style="width:10%;">Tax</th>
                                        <th style="width:10%;">Discount</th>
                                        <th style="width:12%;">Expiry Date</th>
                                        <th style="width:13%;">Total</th>
                                        <th style="width:5%;" class="text-center"><i class="ri-more-2-line"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($items)): ?>
                                        <?php foreach($items as $it): ?>
                                            <tr class="po-item-row">
                                                <td>
                                                    <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                        <option value=""></option>
                                                        <?php if(!empty($it['product_id'])): ?>
                                                            <option value="<?= esc($it['product_id']) ?>" selected><?= esc($it['product_text'] ?? ('Product #'.$it['product_id'])) ?></option>
                                                        <?php endif; ?>
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="<?= esc($it['quantity'] ?? '') ?>" step="0.01" min="0"></td>
                                                <td><input type="number" class="form-control item-price" name="item_unit_price[]" value="<?= esc($it['unit_price'] ?? '') ?>" step="0.01" min="0"></td>
                                                <td><input type="number" class="form-control item-tax" name="item_tax_amount[]" value="<?= esc($it['tax_amount'] ?? '') ?>" step="0.01" min="0" placeholder="0.00"></td>
                                                <td><input type="number" class="form-control item-discount" name="item_discount_amount[]" value="<?= esc($it['discount_amount'] ?? '') ?>" step="0.01" min="0" placeholder="0.00"></td>
                                                <td><input type="date" class="form-control item-expiry" name="item_expiry_date[]" value="<?= esc($it['expiry_date'] ?? '') ?>"></td>
                                                <td class="item-total text-end fw-semibold">0.00</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="po-item-row">
                                            <td>
                                                <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                    <option value=""></option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="" step="0.01" min="0"></td>
                                            <td><input type="number" class="form-control item-price" name="item_unit_price[]" value="" step="0.01" min="0"></td>
                                            <td><input type="number" class="form-control item-tax" name="item_tax_amount[]" value="" step="0.01" min="0" placeholder="0.00"></td>
                                            <td><input type="number" class="form-control item-discount" name="item_discount_amount[]" value="" step="0.01" min="0" placeholder="0.00"></td>
                                            <td><input type="date" class="form-control item-expiry" name="item_expiry_date[]" value=""></td>
                                            <td class="item-total text-end fw-semibold">0.00</td>
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
                                        <th colspan="3" class="text-end">Sub Total</th>
                                        <th class="text-end" id="po-subtotal">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Tax Amount</th>
                                        <th class="text-end" id="po-tax-total">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Item Discount</th>
                                        <th class="text-end" id="po-item-discount-total">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Discount on Purchase</th>
                                        <th class="text-end">
                                            <input type="number" class="form-control form-control-sm text-end" id="discount_amount" name="discount_amount" value="<?= esc($formdata['discount_amount']) ?>" step="0.01" min="0" placeholder="0.00">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Final Total</th>
                                        <th class="text-end" id="po-final-total">0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-muted">Final Total = Sub Total + Item Tax - Item Discount - Purchase Discount</small>
                    </div>
                </div>

                <!-- Hidden (computed by JS, server recomputes anyway) -->
                <input type="hidden" id="tax_amount" name="tax_amount" value="<?= esc($formdata['tax_amount'] ?? '') ?>">

                <div class="text-end mt-3">
                    <a href="<?= site_url('purchase-orders'); ?>" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-loading="Saving...">
                        <?= $formdata['mode'] == 'new' ? 'Create Purchase Order' : 'Update Purchase Order' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>


