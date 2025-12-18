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
                        <i class="ri-shopping-cart-2-line me-2"></i>
                        <?= $formdata['mode'] == 'new' ? 'Add Sale' : 'Edit Sale' ?>
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('sales') ?>" class="btn btn-outline-dark btn-label ms-2">
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

            <form class="needs-validation" novalidate method="post" action="<?= site_url('sales/'.($formdata['mode'] == 'new' ? 'new' : 'edit/'.$formdata['id'])) ?>">
                <?= csrf_field() ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Sale Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Invoice Number</label>
                                <input type="text" class="form-control" name="invoice_number" value="<?= esc($formdata['invoice_number']) ?>" placeholder="Auto-generated" required <?= $formdata['mode'] == 'new' ? 'readonly' : '' ?>>
                                <div class="invalid-feedback">Please enter invoice number.</div>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-semibold required">Customer</label>
                                <select class="form-select js-customer-select" name="customer_id" required>
                                    <option value=""></option>
                                    <?php if(!empty($formdata['customer_id'])): ?>
                                        <option value="<?= esc($formdata['customer_id']) ?>" selected><?= esc($formdata['customer_text'] ?? ('Customer #'.$formdata['customer_id'])) ?></option>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback">Please select customer.</div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold required">Location Type</label>
                                <select class="form-select" id="location_type" name="location_type" required>
                                    <option value="shop" <?= (string)$formdata['location_type'] === 'shop' ? 'selected' : '' ?>>Shop</option>
                                    <option value="godown" <?= (string)$formdata['location_type'] === 'godown' ? 'selected' : '' ?>>Godown</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold required">Location</label>
                                <select class="form-select" id="location_id" name="location_id" required>
                                    <option value="">Select location</option>
                                    <?php foreach(($shopOptions ?? array()) as $s): ?>
                                        <option data-type="shop" value="<?= $s['shop_id'] ?>" <?= ((string)$formdata['location_type'] === 'shop' && (string)$formdata['location_id'] === (string)$s['shop_id']) ? 'selected' : '' ?>>
                                            <?= esc($s['shop_name'] ?? ('Shop #'.$s['shop_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php foreach(($godownOptions ?? array()) as $g): ?>
                                        <option data-type="godown" value="<?= $g['godown_id'] ?>" <?= ((string)$formdata['location_type'] === 'godown' && (string)$formdata['location_id'] === (string)$g['godown_id']) ? 'selected' : '' ?>>
                                            <?= esc($g['godown_name'] ?? ('Godown #'.$g['godown_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Payment Status</label>
                                <select class="form-select" name="payment_status" required>
                                    <option value="">Select</option>
                                    <?php foreach(($paymentStatusOptions ?? array()) as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= (string)$formdata['payment_status'] === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <select class="form-select" name="payment_method">
                                    <?php foreach(($paymentMethodOptions ?? array()) as $value => $label): ?>
                                        <option value="<?= esc($value) ?>" <?= (string)$formdata['payment_method'] === (string)$value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold required">Sale Date</label>
                                <input type="date" class="form-control" name="sale_date" value="<?= esc($formdata['sale_date']) ?>" required>
                                <div class="invalid-feedback">Please select sale date.</div>
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
                            <table class="table table-striped align-middle" id="sale-items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40%;">Product</th>
                                        <th style="width:12%;">Qty</th>
                                        <th style="width:12%;">Unit Price</th>
                                        <th style="width:12%;">Disc %</th>
                                        <th style="width:10%;">Tax %</th>
                                        <th style="width:12%;">Total</th>
                                        <th style="width:5%;" class="text-center"><i class="ri-more-2-line"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($items)): ?>
                                        <?php foreach($items as $it): ?>
                                            <tr class="sale-item-row">
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
                                                <td><input type="number" class="form-control item-discount-percent" name="item_discount_percent[]" value="<?= esc($it['discount_percent'] ?? '') ?>" step="0.01" min="0" max="100" placeholder="0"></td>
                                                <td><input type="number" class="form-control item-tax-percent" name="item_tax_percent[]" value="<?= esc($it['tax_percent'] ?? '') ?>" step="0.01" min="0" max="100" placeholder="0"></td>
                                                <td class="item-total text-end fw-semibold">0.00</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="sale-item-row">
                                            <td>
                                                <select class="form-select item-product js-product-select" name="item_product_id[]">
                                                    <option value=""></option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="" step="0.01" min="0"></td>
                                            <td><input type="number" class="form-control item-price" name="item_unit_price[]" value="" step="0.01" min="0"></td>
                                            <td><input type="number" class="form-control item-discount-percent" name="item_discount_percent[]" value="" step="0.01" min="0" max="100" placeholder="0"></td>
                                            <td><input type="number" class="form-control item-tax-percent" name="item_tax_percent[]" value="" step="0.01" min="0" max="100" placeholder="0"></td>
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
                                        <th class="text-end" id="sale-subtotal">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Item Tax</th>
                                        <th class="text-end" id="sale-tax-total">0.00</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Discount on Sale</th>
                                        <th class="text-end">
                                            <input type="number" class="form-control form-control-sm text-end" id="discount_amount" name="discount_amount" value="<?= esc($formdata['discount_amount']) ?>" step="0.01" min="0" placeholder="0.00">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Final Total</th>
                                        <th class="text-end" id="sale-final-total">0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-muted">Final Total = Sub Total + Item Tax - Discount</small>
                    </div>
                </div>

                <!-- Hidden (computed by JS, server recomputes anyway) -->
                <input type="hidden" id="tax_amount" name="tax_amount" value="<?= esc($formdata['tax_amount'] ?? '') ?>">

                <div class="text-end mt-3">
                    <a href="<?= site_url('sales'); ?>" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-loading="Saving...">
                        <?= $formdata['mode'] == 'new' ? 'Create Sale' : 'Update Sale' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>


