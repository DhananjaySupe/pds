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
                                        <select class="form-select" name="user_type_id" required>
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
                    <button type="submit" class="btn btn-primary"><?= $formdata['mode'] == 'new' ? 'Create User' : 'Update User' ?></button>
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

