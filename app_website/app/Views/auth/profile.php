<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0" data-key="t-profile">Profile</h4>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Left Panel - Profile Photo -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="<?= $formdata['profile_photo'] && $formdata['profile_photo'] != '' ? site_url('uploads/users/thumb/'.$formdata['profile_photo']) : site_url('assets/images/user.png'); ?>"
                                 class="img-thumbnail rounded-circle" alt="Profile Photo"
                                 width="150" height="150" id="profilePreview">
                        </div>

                        <!-- Profile Photo Upload Form -->
                        <form id="profilePhotoForm" action="<?= site_url('profile'); ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="full_name" value="<?= $formdata['full_name'] ?>">
                            <input type="hidden" name="language" value="<?= $formdata['language'] ?>">

                            <div class="mb-3">
                                <input type="file" class="form-control form-control-sm" id="profilePhoto" name="profile_photo"
                                       accept="image/*" onchange="previewImage(this)">
                            </div>



                            <button type="submit" class="btn btn-success btn-sm w-100" id="savePhotoBtn" data-key="t-save">
                                Save Photo
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Profile Information -->
            <div class="col-lg-9 col-md-8">
                <!-- Profile Update Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0" data-key="t-personal-information">Personal Information</h5>
                    </div>
                    <div class="card-body">
                            <form id="profileForm" action="<?= site_url('profile'); ?>" method="post" class="needs-validation" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_profile">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label" data-key="t-name">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                           value="<?= $formdata['full_name'] ?>" required>
                                    <div class="invalid-feedback">
                                        Please enter your full name.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label" data-key="t-email">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?= $formdata['email'] ?>"
                                           readonly disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label" data-key="t-phone">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" value="<?= $formdata['phone'] ?>"
                                           readonly disabled>
                                    <small class="text-muted">Phone number cannot be changed</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label" data-key="t-role">Role</label>
                                    <input type="text" class="form-control" id="role" value="<?= $formdata['role'] ?>" readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="language" class="form-label" data-key="t-language">Language</label>
                                    <select class="form-select" id="language" name="language" required>
                                        <option value="">Choose language...</option>
                                        <option value="en" <?= $formdata['language'] == 'en' ? 'selected' : '' ?>>English</option>
                                        <option value="hi" <?= $formdata['language'] == 'hi' ? 'selected' : '' ?>>हिंदी (Hindi)</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a language.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label" data-key="t-status">Status</label>
                                    <input type="text" class="form-control" id="status-1"
                                           value="<?= $formdata['status'] == 1 ? 'Active' : 'Inactive' ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="created_at" class="form-label" data-key="t-created-at">Created At</label>
                                    <input type="text" class="form-control" id="created_at"
                                           value="<?= $formdata['created_at'] ? date('d M Y, h:i A', strtotime($formdata['created_at'])) : 'N/A' ?>" readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="updated_at" class="form-label" data-key="t-updated-at">Last Updated</label>
                                    <input type="text" class="form-control" id="updated_at"
                                           value="<?= $formdata['updated_at'] ? date('d M Y, h:i A', strtotime($formdata['updated_at'])) : 'N/A' ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="last_login_at" class="form-label" data-key="t-last-login">Last Login</label>
                                    <input type="text" class="form-control" id="last_login_at"
                                           value="<?= $formdata['last_login_at'] ? date('d M Y, h:i A', strtotime($formdata['last_login_at'])) : 'N/A' ?>" readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_login_ip" class="form-label" data-key="t-last-login-ip">Last Login IP</label>
                                    <input type="text" class="form-control" id="last_login_ip"
                                           value="<?= $formdata['last_login_ip'] ?: 'N/A' ?>" readonly>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" data-key="t-save">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0" data-key="t-change-password">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form id="passwordForm" action="<?= site_url('profile'); ?>" method="post" class="needs-validation" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="change_password">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="current_password" class="form-label" data-key="t-current-password">Current Password</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5 password-input" placeholder="Enter current password" id="current_password" name="current_password" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="current-password-addon">
                                            <i class="ri-eye-fill align-middle"></i>
                                        </button>
                                        <div class="invalid-feedback">
                                            Please enter your current password.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label" data-key="t-new-password">New Password</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5 password-input" placeholder="Enter new password" id="new_password" name="new_password" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="new-password-addon">
                                            <i class="ri-eye-fill align-middle"></i>
                                        </button>
                                        <div class="invalid-feedback">
                                            Please enter your password (minimum 8 characters) contain one uppercase, one lowercase, one number and one special character.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label" data-key="t-confirm-password">Confirm New Password</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5 password-input" placeholder="Confirm new password" id="confirm_password" name="confirm_password" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="confirm-password-addon">
                                            <i class="ri-eye-fill align-middle"></i>
                                        </button>
                                        <div class="invalid-feedback">
                                            Please confirm your password (minimum 8 characters) contain one uppercase, one lowercase, one number and one special character.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-warning" data-key="t-change-password">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('css'); ?>
<style>
.auth-pass-inputgroup {
    position: relative;
}

.auth-pass-inputgroup .password-input {
    padding-right: 3rem;
}

.auth-pass-inputgroup .password-addon {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    border: none;
    background: transparent;
    padding: 0.5rem;
    z-index: 10;
}

.auth-pass-inputgroup .password-addon:hover {
    background-color: rgba(0,0,0,0.05);
}

.auth-pass-inputgroup .password-addon:focus {
    outline: none;
    box-shadow: none;
}

/* Enhanced Bootstrap validation styling */
.was-validated .form-control:valid,
.was-validated .form-select:valid {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 2.89 2.89 2.89-2.89.94.94L5.12 9.62z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.was-validated .form-control:invalid,
.was-validated .form-select:invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='m5.8 4.6 1.4 1.4L5.8 7.4 4.4 6l1.4-1.4z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.was-validated .form-control:valid:focus,
.was-validated .form-select:valid:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

.was-validated .form-control:invalid:focus,
.was-validated .form-select:invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.valid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #198754;
}

/* Enhanced validation styling for better visibility */
.form-control.is-valid,
.form-select.is-valid {
    border-color: #198754;
    background-color: #f8fff9;
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    background-color: #fff8f8;
}

/* Show validation icons even without was-validated class */
.form-control.is-valid,
.form-select.is-valid {
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 2.89 2.89 2.89-2.89.94.94L5.12 9.62z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid,
.form-select.is-invalid {
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='m5.8 4.6 1.4 1.4L5.8 7.4 4.4 6l1.4-1.4z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Smooth transitions for validation states */
.form-control,
.form-select {
    transition: all 0.15s ease-in-out;
}

/* Focus states for validation */
.form-control.is-valid:focus,
.form-select.is-valid:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

.form-control.is-invalid:focus,
.form-select.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}
</style>
<?php $this->endSection(); ?>

<?php $this->section('javascript'); ?>
<script>
// Profile photo preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Show/hide password fields using eye icons
document.getElementById('current-password-addon').addEventListener('click', function() {
    const field = document.getElementById('current_password');
    const icon = this.querySelector('i');

    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'ri-eye-off-fill align-middle';
    } else {
        field.type = 'password';
        icon.className = 'ri-eye-fill align-middle';
    }
});

document.getElementById('new-password-addon').addEventListener('click', function() {
    const field = document.getElementById('new_password');
    const icon = this.querySelector('i');

    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'ri-eye-off-fill align-middle';
    } else {
        field.type = 'password';
        icon.className = 'ri-eye-fill align-middle';
    }
});

document.getElementById('confirm-password-addon').addEventListener('click', function() {
    const field = document.getElementById('confirm_password');
    const icon = this.querySelector('i');

    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'ri-eye-off-fill align-middle';
    } else {
        field.type = 'password';
        icon.className = 'ri-eye-fill align-middle';
    }
});

// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');

        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        // Show validation feedback immediately for profile form
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            // Validate all required fields on page load
            const requiredFields = profileForm.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (field.value.trim() !== '') {
                    field.classList.add('is-valid');
                }
            });

            // Add was-validated class to show validation styling
            profileForm.classList.add('was-validated');
        }

        // Show validation feedback immediately for password form
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            // Add was-validated class to show validation styling
            passwordForm.classList.add('was-validated');
        }
    }, false);
})();

// Custom validation for password forms
document.getElementById('profileForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Additional custom validation for password matching
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        // Add custom validation message
        const confirmField = document.getElementById('confirm_password');
        confirmField.setCustomValidity('Passwords do not match');
        confirmField.reportValidity();
        return false;
    } else {
        // Clear custom validation message
        document.getElementById('confirm_password').setCustomValidity('');
    }

    this.classList.add('was-validated');
});

// Real-time validation feedback for all fields
document.addEventListener('DOMContentLoaded', function() {
    // Profile form real-time validation
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        const profileFields = profileForm.querySelectorAll('input[required], select[required]');
        profileFields.forEach(function(field) {
            field.addEventListener('input', function() {
                validateField(this);
            });
            field.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    // Password form real-time validation
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        const passwordFields = passwordForm.querySelectorAll('input[required]');
        passwordFields.forEach(function(field) {
            field.addEventListener('input', function() {
                validateField(this);
            });
            field.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    // Password matching validation
    document.getElementById('new_password').addEventListener('input', function() {
        const confirmField = document.getElementById('confirm_password');
        if (confirmField.value && this.value !== confirmField.value) {
            confirmField.setCustomValidity('Passwords do not match');
        } else {
            confirmField.setCustomValidity('');
        }
        validateField(confirmField);
    });

    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPasswordField = document.getElementById('new_password');
        if (newPasswordField.value && this.value !== newPasswordField.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
        validateField(this);
    });
});

// Function to validate individual fields
function validateField(field) {
    if (field.checkValidity()) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
}
</script>
<?php $this->endSection(); ?>