<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php $userImageConstraints = 'max-width: 220px; max-height: 220px; object-fit: cover;'; ?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-user-line me-2"></i>
                        <?= $formdata['mode'] == 'new' ? 'Add User' : 'Edit User' ?>
                    </h4>
                    <div class="page-title-right">
                        <a href="<?= site_url('users') ?>" class="btn btn-outline-dark btn-label ms-2">
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

            <?php $userTypeOptions = isset($userTypeOptions) ? $userTypeOptions : array(); ?>
            <?php $godownOptions = isset($godownOptions) ? $godownOptions : array(); ?>
            <form class="needs-validation" novalidate method="post" action="<?= site_url('users/'.($formdata['mode'] == 'new' ? 'new' : 'edit/'.$formdata['id'])) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row align-items-stretch">
                    <div class="col-lg-8 d-flex">
                        <div class="card w-100 h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Basic Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" value="<?= esc($formdata['full_name']) ?>" placeholder="Enter full name" required>
                                        <div class="invalid-feedback">Please enter name.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= esc($formdata['email']) ?>" placeholder="name@example.com">
                                        <div class="invalid-feedback">Please enter valid email.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Phone</label>
                                        <input type="text" class="form-control" name="phone" value="<?= esc($formdata['phone']) ?>" placeholder="+91 9876543210" required>
                                        <div class="invalid-feedback">Please enter phone.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Language</label>
                                        <select class="form-select" name="language">
                                            <option value="en" <?= ($formdata['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                            <option value="hi" <?= ($formdata['language'] ?? 'en') === 'hi' ? 'selected' : '' ?>>Hindi</option>
                                            <option value="mr" <?= ($formdata['language'] ?? 'en') === 'mr' ? 'selected' : '' ?>>Marathi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">Status</label>
                                        <select class="form-select" name="status" required>
                                            <?php foreach($statusOptions as $value => $label): ?>
                                                <option value="<?= $value ?>" <?= $formdata['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold required">User Type</label>
                                        <select class="form-select" id="userTypeSelect" name="user_type_id" required>
                                            <option value="">Select user type</option>
                                            <?php foreach($userTypeOptions as $type): ?>
                                                <option value="<?= $type['user_type_id'] ?>" <?= (string)$formdata['user_type_id'] === (string)$type['user_type_id'] ? 'selected' : '' ?>><?= esc($type['type_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select user type.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold <?= $formdata['mode'] == 'new' ? 'required' : '' ?>">Password</label>
                                        <div class="input-group password-toggle">
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="userPassword"
                                                name="password"
                                                minlength="8"
                                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                                                autocomplete="new-password"
                                                placeholder="<?= $formdata['mode'] == 'new' ? 'Create password' : 'Leave blank to keep current' ?>"
                                                <?= $formdata['mode'] == 'new' ? 'required' : '' ?>>
                                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="Show password" data-target="userPassword">
                                                <i class="ri-eye-off-line"></i>
                                            </button>
                                        </div>
                                        <?php if($formdata['mode'] == 'new'): ?>
                                            <div class="invalid-feedback">Please enter password.</div>
                                        <?php endif; ?>
                                        <small class="text-muted d-block mt-1">Use at least 8 characters with uppercase, lowercase, number and special character.</small>
                                    </div>

                                    <!-- Extra details for Vendor/Godown/Shop -->
                                    <div class="col-12">
                                        <div id="extraDetailsWrapper" class="border rounded p-3 mt-2 d-none">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h6 class="mb-0">Additional Details</h6>
                                                <span class="text-muted small">Shown for Vendor / Godown / Shop users</span>
                                            </div>

                                            <!-- Vendor -->
                                            <div id="vendorDetails" class="extra-details d-none">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold required">Company Name</label>
                                                        <input type="text" class="form-control" name="company_name" value="<?= esc($formdata['company_name'] ?? '') ?>" placeholder="Enter company name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">GST Number</label>
                                                        <input type="text" class="form-control" name="gst_number" value="<?= esc($formdata['gst_number'] ?? '') ?>" placeholder="Enter GST number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">PAN Number</label>
                                                        <input type="text" class="form-control" name="pan_number" value="<?= esc($formdata['pan_number'] ?? '') ?>" placeholder="Enter PAN number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Rating</label>
                                                        <input type="number" class="form-control" name="rating" value="<?= esc($formdata['rating'] ?? '') ?>" min="0" max="5" step="0.1" placeholder="0 - 5">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Bank Account Details</label>
                                                        <textarea class="form-control" name="bank_account_details" rows="2" placeholder="Enter bank details"><?= esc($formdata['bank_account_details'] ?? '') ?></textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Payment Terms</label>
                                                        <textarea class="form-control" name="payment_terms" rows="2" placeholder="Enter payment terms"><?= esc($formdata['payment_terms'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Godown -->
                                            <div id="godownDetails" class="extra-details d-none">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold required">Godown Name</label>
                                                        <input type="text" class="form-control" name="godown_name" value="<?= esc($formdata['godown_name'] ?? '') ?>" placeholder="Enter godown name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Capacity (sqft)</label>
                                                        <input type="number" class="form-control" name="capacity_sqft" value="<?= esc($formdata['capacity_sqft'] ?? '') ?>" min="0" step="1" placeholder="Enter capacity">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Location</label>
                                                        <input type="text" class="form-control" name="godown_location" value="<?= esc($formdata['godown_location'] ?? '') ?>" placeholder="Enter location">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Contact Person</label>
                                                        <input type="text" class="form-control" name="godown_contact_person" value="<?= esc($formdata['godown_contact_person'] ?? '') ?>" placeholder="Enter contact person">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Shop -->
                                            <div id="shopDetails" class="extra-details d-none">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold required">Shop Name</label>
                                                        <input type="text" class="form-control" name="shop_name" value="<?= esc($formdata['shop_name'] ?? '') ?>" placeholder="Enter shop name">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold required">Godown</label>
                                                        <select class="form-select" name="shop_godown_id">
                                                            <option value="">Select godown</option>
                                                            <?php foreach($godownOptions as $g): ?>
                                                                <option value="<?= $g['godown_id'] ?>" <?= (string)($formdata['shop_godown_id'] ?? '') === (string)$g['godown_id'] ? 'selected' : '' ?>>
                                                                    <?= esc($g['godown_name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Location</label>
                                                        <input type="text" class="form-control" name="shop_location" value="<?= esc($formdata['shop_location'] ?? '') ?>" placeholder="Enter location">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Contact Person</label>
                                                        <input type="text" class="form-control" name="shop_contact_person" value="<?= esc($formdata['shop_contact_person'] ?? '') ?>" placeholder="Enter contact person">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex">
                        <div class="card w-100 h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Profile Photo</h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <img src="<?= !empty($formdata['profile_photo']) ? site_url('uploads/users/thumb/'.$formdata['profile_photo']) : site_url('assets/images/user.png'); ?>" id="userPhotoPreview" class="avatar-xxl rounded-circle border" alt="User photo" style="<?= $userImageConstraints ?>">
                                </div>
                                <div class="d-grid gap-2">
                                    <input type="file" class="form-control d-none" id="userPhoto" name="profile_photo" accept="image/*" onchange="previewUserImage(this)">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('userPhoto').click()">
                                        <i class="ri-upload-line me-1"></i> Upload Photo
                                    </button>
                                    <small class="text-muted">Square image recommended, max 10MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <a href="<?= site_url('users'); ?>" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary" data-loading="Saving..." ><?= $formdata['mode'] == 'new' ? 'Create User' : 'Update User' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewUserImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('userPhotoPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        function setSectionEnabled(section, enabled) {
            if (!section) return;
            section.querySelectorAll('input, select, textarea').forEach(function (el) {
                el.disabled = !enabled;
            });
        }

        function updateExtraDetailsUI() {
            var userTypeSelect = document.getElementById('userTypeSelect');
            var wrapper = document.getElementById('extraDetailsWrapper');
            var vendor = document.getElementById('vendorDetails');
            var godown = document.getElementById('godownDetails');
            var shop = document.getElementById('shopDetails');

            if (!userTypeSelect || !wrapper) return;

            var type = (userTypeSelect.value || '').toString();
            var showWrapper = (type === '3' || type === '4' || type === '5');

            wrapper.classList.toggle('d-none', !showWrapper);

            // Hide all
            if (vendor) vendor.classList.add('d-none');
            if (godown) godown.classList.add('d-none');
            if (shop) shop.classList.add('d-none');

            // Disable all inputs by default to avoid posting irrelevant fields
            setSectionEnabled(vendor, false);
            setSectionEnabled(godown, false);
            setSectionEnabled(shop, false);

            if (type === '3') {
                vendor.classList.remove('d-none');
                setSectionEnabled(vendor, true);
            } else if (type === '4') {
                godown.classList.remove('d-none');
                setSectionEnabled(godown, true);
            } else if (type === '5') {
                shop.classList.remove('d-none');
                setSectionEnabled(shop, true);
            }
        }

        var userTypeSelect = document.getElementById('userTypeSelect');
        if (userTypeSelect) {
            userTypeSelect.addEventListener('change', updateExtraDetailsUI);
        }
        updateExtraDetailsUI();

        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                var isHidden = input.getAttribute('type') === 'password';
                input.setAttribute('type', isHidden ? 'text' : 'password');
                this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

                var icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('ri-eye-line', isHidden);
                    icon.classList.toggle('ri-eye-off-line', !isHidden);
                }
            });
        });
    });
</script>

<?php $this->endSection(); ?>

