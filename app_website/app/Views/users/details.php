<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><?= $formdata['mode'] == 'new' ? 'New User' : 'Edit User' ?></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('users') ?>">Users</a></li>
                            <li class="breadcrumb-item active"><?= $formdata['mode'] == 'new' ? 'New User' : 'Edit User' ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <?php if(isset($error) && !empty($error)): ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">User Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="userForm" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="id" value="<?= $formdata['id'] ?>">
                            <input type="hidden" name="uniqid" value="<?= $formdata['uniqid'] ?>">

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="text-center mb-4">
                                        <!-- Profile Photo Upload Card -->
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <img src="<?= $formdata['profile_photo'] ? site_url('uploads/users/thumb/'.$formdata['profile_photo']) : site_url('assets/images/user.png') ?>"
                                                         class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                                         alt="user-profile" id="profileImage" width="120" height="120">
                                                </div>
                                                <div class="mb-2">
                                                    <input type="file" class="form-control form-control-sm" id="profile-img-file-input" name="profilePhoto" accept="image/*" onchange="previewImage(this)">
                                                </div>
                                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="cameraBtn">
                                                    <i class="bx bx-camera me-1"></i>Camera
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm w-100 mt-2" id="savePhotoBtn" style="display: none;">
                                                    <i class="bx bx-save me-1"></i>Save Photo
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm w-100 mt-2" id="aiEditBtn" style="display: none;">
                                                    <i class="bx bx-magic-wand me-1"></i>AI Edit
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm w-100 mt-2" id="debugBtn" style="display: none;">
                                                    <i class="bx bx-bug me-1"></i>Debug
                                                </button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="profile_photo" id="profilePhotoInput" value="<?= $formdata['profile_photo'] ?>">
                                        <div class="invalid-feedback" id="profilePhotoError"></div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= $formdata['full_name'] ?>" required>
                                                <div class="invalid-feedback">
                                                    Please enter full name.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?= $formdata['email'] ?>" required>
                                                <div class="invalid-feedback">
                                                    Please enter a valid email address.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="phone" name="phone" value="<?= $formdata['phone'] ?>" required pattern="[0-9]{10}">
                                                <div class="invalid-feedback">
                                                    Please enter a valid 10-digit phone number.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                                <select class="form-select" id="role_id" name="role_id" required>
                                                    <option value="">Select Role</option>
                                                    <?php if(isset($roles) && !empty($roles)): ?>
                                                        <?php foreach($roles as $role): ?>
                                                            <option value="<?= $role['role_id'] ?>" <?= ($formdata['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                                                <?= $role['name'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select a role.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">
                                                    Password <?= $formdata['mode'] == 'new' ? '<span class="text-danger">*</span>' : '' ?>
                                                </label>
                                                <input type="password" class="form-control" id="password" name="password" <?= $formdata['mode'] == 'new' ? 'required' : '' ?> minlength="6">
                                                <div class="invalid-feedback">
                                                    <?= $formdata['mode'] == 'new' ? 'Please enter a password (minimum 6 characters).' : 'Password must be at least 6 characters if provided.' ?>
                                                </div>
                                                <?php if($formdata['mode'] == 'edit'): ?>
                                                    <small class="text-muted">Leave blank to keep current password</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">
                                                    Confirm Password <?= $formdata['mode'] == 'new' ? '<span class="text-danger">*</span>' : '' ?>
                                                </label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" <?= $formdata['mode'] == 'new' ? 'required' : '' ?>>
                                                <div class="invalid-feedback" id="confirmPasswordError">
                                                    <?= $formdata['mode'] == 'new' ? 'Please confirm your password.' : 'Passwords do not match.' ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status_1" name="status">
                                                    <option value="active" <?= ($formdata['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= ($formdata['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="language" class="form-label">Language</label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="en" <?= ($formdata['language'] == 'en') ? 'selected' : '' ?>>English</option>
                                                    <option value="hi" <?= ($formdata['language'] == 'hi') ? 'selected' : '' ?>>Hindi</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= site_url('users') ?>" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <?= $formdata['mode'] == 'new' ? 'Create User' : 'Update User' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Camera Capture Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraModalLabel">
                    <i class="bx bx-camera me-2"></i>Capture Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="camera-container mb-3">
                    <video id="cameraVideo" autoplay playsinline style="width: 100%; max-width: 500px; border-radius: 8px;"></video>
                    <canvas id="cameraCanvas" style="display: none;"></canvas>
                </div>
                <div id="capturedImageContainer" style="display: none;">
                    <img id="capturedImage" style="max-width: 100%; border-radius: 8px;" alt="Captured Photo">
                </div>
                <div class="camera-controls">
                    <button type="button" class="btn btn-primary me-2" id="captureBtn">
                        <i class="bx bx-camera me-1"></i>Capture
                    </button>
                    <button type="button" class="btn btn-secondary me-2" id="retakeBtn" style="display: none;">
                        <i class="bx bx-refresh me-1"></i>Retake
                    </button>
                    <button type="button" class="btn btn-success" id="usePhotoBtn" style="display: none;">
                        <i class="bx bx-check me-1"></i>Use Photo
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- AI Edit Modal -->
<div class="modal fade" id="aiEditModal" tabindex="-1" aria-labelledby="aiEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiEditModalLabel">
                    <i class="bx bx-magic-wand me-2"></i>AI Photo Enhancement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Original Image</h6>
                        <img id="originalImage" src="" alt="Original" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                    <div class="col-md-6">
                        <h6>Enhanced Image</h6>
                        <div id="enhancedImageContainer" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Processing...</span>
                            </div>
                            <p class="mt-2">Enhancing image...</p>
                        </div>
                        <img id="enhancedImage" src="" alt="Enhanced" class="img-fluid rounded" style="max-height: 300px; display: none;">
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Enhancement Options</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enhanceFace" checked>
                                <label class="form-check-label" for="enhanceFace">
                                    Face Enhancement
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enhanceLighting" checked>
                                <label class="form-check-label" for="enhanceLighting">
                                    Lighting Correction
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enhanceQuality" checked>
                                <label class="form-check-label" for="enhanceQuality">
                                    Quality Improvement
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyEnhancement" style="display: none;">
                    <i class="bx bx-check me-1"></i>Apply Enhancement
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('javascripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    const submitBtn = document.getElementById('submitBtn');
    const profilePhotoInput = document.getElementById('profile-img-file-input');
    const profileImage = document.getElementById('profileImage');
    const profilePhotoInputHidden = document.getElementById('profilePhotoInput');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const mode = '<?= $formdata['mode'] ?>';

    // Profile form elements
    const savePhotoBtn = document.getElementById('savePhotoBtn');
    const aiEditBtn = document.getElementById('aiEditBtn');
    const cameraBtn = document.getElementById('cameraBtn');

    // Camera modal elements
    const cameraModal = document.getElementById('cameraModal');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const capturedImage = document.getElementById('capturedImage');
    const capturedImageContainer = document.getElementById('capturedImageContainer');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const usePhotoBtn = document.getElementById('usePhotoBtn');

    // AI Edit modal elements
    const aiEditModal = document.getElementById('aiEditModal');
    const originalImage = document.getElementById('originalImage');
    const enhancedImage = document.getElementById('enhancedImage');
    const enhancedImageContainer = document.getElementById('enhancedImageContainer');
    const applyEnhancementBtn = document.getElementById('applyEnhancement');

    let stream = null;
    let capturedImageData = null;
    let currentImageFile = null;

    // Function to preview image (called from HTML onchange)
    window.previewImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImage.src = e.target.result;
                // Show save and AI edit buttons when image is selected
                savePhotoBtn.style.display = 'block';
                aiEditBtn.style.display = 'block';
                debugBtn.style.display = 'block';
                currentImageFile = input.files[0];
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            savePhotoBtn.style.display = 'none';
            aiEditBtn.style.display = 'none';
            debugBtn.style.display = 'none';
        }
    };

    // Profile photo upload with validation
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showToast('error', 'Please select a valid image file (JPG, PNG, GIF).');
                    this.value = '';
                    return;
                }

                // Validate file size (max 5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    showToast('error', 'File size should be less than 5MB.');
                    this.value = '';
                    return;
                }

                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Show save and AI edit buttons
                savePhotoBtn.style.display = 'block';
                aiEditBtn.style.display = 'block';
                currentImageFile = file;
            }
        });
    }

    // Function to upload profile photo
    function uploadProfilePhoto(file) {
        const formData = new FormData();
        formData.append('profilePhoto', file);
        formData.append('id', document.querySelector('input[name="id"]').value);

        // Show loading state
        const originalSrc = profileImage.src;
        profileImage.style.opacity = '0.5';

        fetch('<?= site_url('users/save-photo') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            profileImage.style.opacity = '1';
            if (data.success) {
                if (data.images && data.images.length > 0) {
                    const image = data.images[0];
                    profileImage.src = image.thumb;
                    profilePhotoInputHidden.value = image.filename;

                    // Also update the hidden input in the main form
                    const mainFormProfilePhotoInput = document.querySelector('#userForm input[name="profile_photo"]');
                    if (mainFormProfilePhotoInput) {
                        mainFormProfilePhotoInput.value = image.filename;
                    }

                    console.log('Profile photo saved:', image.filename);
                }
                showToast('success', data.message || 'Profile photo uploaded successfully!');
                // Hide save and AI edit buttons after successful save
                savePhotoBtn.style.display = 'none';
                aiEditBtn.style.display = 'none';
                debugBtn.style.display = 'none';
            } else {
                profileImage.src = originalSrc;
                showToast('error', data.message || 'Failed to upload profile photo.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            profileImage.style.opacity = '1';
            profileImage.src = originalSrc;
            showToast('error', 'Error uploading photo. Please try again.');
        });
    }

    // Save photo button functionality
    if (savePhotoBtn) {
        savePhotoBtn.addEventListener('click', function() {
            if (currentImageFile) {
                uploadProfilePhoto(currentImageFile);
            } else if (profilePhotoInput.files[0]) {
                uploadProfilePhoto(profilePhotoInput.files[0]);
            } else {
                showToast('error', 'Please select an image first.');
            }
        });
    }

    // AI Edit button functionality
    if (aiEditBtn) {
        aiEditBtn.addEventListener('click', function() {
            if (currentImageFile || profilePhotoInput.files[0]) {
                const file = currentImageFile || profilePhotoInput.files[0];
                openAIEditModal(file);
            } else {
                showToast('error', 'Please select an image first.');
            }
        });
    }

    // Debug button functionality
    const debugBtn = document.getElementById('debugBtn');
    if (debugBtn) {
        debugBtn.addEventListener('click', function() {
            console.log('Debug Info:');
            console.log('Current Image File:', currentImageFile);
            console.log('Profile Photo Input Files:', profilePhotoInput.files);
            console.log('Profile Photo Input Hidden Value:', profilePhotoInputHidden.value);
            console.log('Main Form Profile Photo Input:', document.querySelector('#userForm input[name="profile_photo"]').value);
            console.log('Profile Image Src:', profileImage.src);

            showToast('info', 'Debug info logged to console. Check browser console for details.');
        });
    }

    // Password confirmation validation
    function validatePasswordConfirmation() {
        if (passwordInput.value || confirmPasswordInput.value) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
                return false;
            } else {
                confirmPasswordInput.setCustomValidity('');
                return true;
            }
        }
        return true;
    }

    // Add event listeners for password validation
    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', validatePasswordConfirmation);
        confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);
    }

    // Form submission with Bootstrap validation
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();

            // Validate password confirmation
            if (!validatePasswordConfirmation()) {
                confirmPasswordInput.focus();
                return;
            }

            // Check if form is valid
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

            // Submit form via AJAX
            const formData = new FormData(form);

            // Ensure profile photo is included in form data
            const profilePhotoValue = profilePhotoInputHidden.value;
            if (profilePhotoValue) {
                formData.set('profile_photo', profilePhotoValue);
            } else {
                // If no profile photo is set, check if there's an existing one
                const existingProfilePhoto = '<?= $formdata['profile_photo'] ?>';
                if (existingProfilePhoto) {
                    formData.set('profile_photo', existingProfilePhoto);
                }
            }

            // Log form data for debugging
            console.log('Form data being submitted:');
            console.log('Profile photo value:', profilePhotoValue);
            console.log('Profile photo input hidden value:', profilePhotoInputHidden.value);
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message || 'User saved successfully!');
                    setTimeout(() => {
                        window.location.href = '<?= site_url('users') ?>';
                    }, 1500);
                } else {
                    showToast('error', data.message || 'Failed to save user. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred while saving. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Real-time validation feedback
    const inputs = form.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });

        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });
    });

    // Phone number validation and formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/\D/g, '');
            // Limit to 10 digits
            value = value.substring(0, 10);
            this.value = value;

            // Validate on blur
            if (this.value.length === 10) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (this.value.length > 0) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    }

    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }

    // Camera functionality
    if (cameraBtn) {
        cameraBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(cameraModal);
            modal.show();
            startCamera();
        });
    }

    // Camera event listeners
    if (captureBtn) {
        captureBtn.addEventListener('click', capturePhoto);
    }
    if (retakeBtn) {
        retakeBtn.addEventListener('click', retakePhoto);
    }
    if (usePhotoBtn) {
        usePhotoBtn.addEventListener('click', useCapturedPhoto);
    }

    // Camera functions
    async function startCamera() {
        try {
            // Check if running on HTTPS (required for camera access)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                throw new Error('Camera access requires HTTPS. Please use HTTPS or localhost.');
            }

            // Check if MediaDevices API is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Camera access is not supported in this browser. Please use a modern browser.');
            }

            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user' // Use front camera
                }
            });
            cameraVideo.srcObject = stream;

            // Show video, hide captured image
            cameraVideo.style.display = 'block';
            capturedImageContainer.style.display = 'none';

            // Show capture button, hide others
            captureBtn.style.display = 'inline-block';
            retakeBtn.style.display = 'none';
            usePhotoBtn.style.display = 'none';

        } catch (error) {
            console.error('Error accessing camera:', error);

            // Show user-friendly error message
            let errorMessage = 'Unable to access camera. ';

            if (error.name === 'NotAllowedError') {
                errorMessage += 'Camera permission was denied. Please allow camera access and try again.';
            } else if (error.name === 'NotFoundError') {
                errorMessage += 'No camera found on your device.';
            } else if (error.name === 'NotSupportedError') {
                errorMessage += 'Camera is not supported in this browser.';
            } else if (error.message.includes('HTTPS')) {
                errorMessage += 'Camera access requires HTTPS. Please use HTTPS or localhost.';
            } else if (error.message.includes('not supported')) {
                errorMessage += 'Please use a modern browser that supports camera access.';
            } else {
                errorMessage += 'Please check camera permissions and try again.';
            }

            // Show error in modal
            showCameraError(errorMessage);
        }
    }

    function capturePhoto() {
        const context = cameraCanvas.getContext('2d');
        cameraCanvas.width = cameraVideo.videoWidth;
        cameraCanvas.height = cameraVideo.videoHeight;

        // Draw video frame to canvas
        context.drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);

        // Convert canvas to blob
        cameraCanvas.toBlob(function(blob) {
            capturedImageData = blob;

            // Display captured image
            const imageUrl = URL.createObjectURL(blob);
            capturedImage.src = imageUrl;

            // Hide video, show captured image
            cameraVideo.style.display = 'none';
            capturedImageContainer.style.display = 'block';

            // Show retake and use photo buttons
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            usePhotoBtn.style.display = 'inline-block';
        }, 'image/jpeg', 0.8);
    }

    function retakePhoto() {
        // Show video, hide captured image
        cameraVideo.style.display = 'block';
        capturedImageContainer.style.display = 'none';

        // Show capture button, hide others
        captureBtn.style.display = 'inline-block';
        retakeBtn.style.display = 'none';
        usePhotoBtn.style.display = 'none';

        // Clear captured data
        capturedImageData = null;
    }

    function useCapturedPhoto() {
        if (capturedImageData) {
            // Create a File object from the blob
            const file = new File([capturedImageData], 'captured-photo.jpg', { type: 'image/jpeg' });

            // Create a FileList-like object
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            // Set the file to the file input
            profilePhotoInput.files = dataTransfer.files;

            // Update preview
            const imageUrl = URL.createObjectURL(capturedImageData);
            profileImage.src = imageUrl;

            // Show save and AI edit buttons when photo is captured
            savePhotoBtn.style.display = 'block';
            aiEditBtn.style.display = 'block';
            debugBtn.style.display = 'block';
            currentImageFile = file;

            // Close modal
            const modal = bootstrap.Modal.getInstance(cameraModal);
            modal.hide();

            // Stop camera stream
            stopCamera();
        }
    }

    function showCameraError(message) {
        // Hide camera elements
        cameraVideo.style.display = 'none';
        capturedImageContainer.style.display = 'none';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'none';
        usePhotoBtn.style.display = 'none';

        // Show error message
        const modalBody = cameraModal.querySelector('.modal-body');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-warning text-center';
        errorDiv.innerHTML = `
            <i class="bx bx-error-circle me-2"></i>
            <strong>Camera Access Error</strong><br>
            ${message}<br><br>
            <small>You can still upload a photo using the file input above.</small>
        `;

        // Clear previous content and add error
        modalBody.innerHTML = '';
        modalBody.appendChild(errorDiv);
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        capturedImageData = null;
    }

    // Stop camera when modal is closed
    if (cameraModal) {
        cameraModal.addEventListener('hidden.bs.modal', function() {
            stopCamera();
        });
    }

    // AI Edit functionality
    function openAIEditModal(file) {
        // Set original image
        const reader = new FileReader();
        reader.onload = function(e) {
            originalImage.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Show modal
        const modal = new bootstrap.Modal(aiEditModal);
        modal.show();

        // Simulate AI enhancement (in real implementation, this would call an AI service)
        simulateAIEnhancement(file);
    }

    function simulateAIEnhancement(file) {
        // Show loading state
        enhancedImageContainer.style.display = 'block';
        enhancedImage.style.display = 'none';
        applyEnhancementBtn.style.display = 'none';

        // Simulate processing time
        setTimeout(() => {
            // For demo purposes, we'll use the same image as "enhanced"
            // In a real implementation, this would be the AI-enhanced version
            const reader = new FileReader();
            reader.onload = function(e) {
                enhancedImage.src = e.target.result;
                enhancedImageContainer.style.display = 'none';
                enhancedImage.style.display = 'block';
                applyEnhancementBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }, 2000);
    }

        // Apply enhancement button
    if (applyEnhancementBtn) {
        applyEnhancementBtn.addEventListener('click', function() {
            // Replace the current image with the enhanced version
            profileImage.src = enhancedImage.src;

            // Create a new file from the enhanced image (in real implementation)
            // For now, we'll just update the preview
            showToast('success', 'AI enhancement applied successfully!');

            // Show save button after AI enhancement
            savePhotoBtn.style.display = 'block';
            aiEditBtn.style.display = 'block';
            debugBtn.style.display = 'block';

            // Close modal
            const modal = bootstrap.Modal.getInstance(aiEditModal);
            modal.hide();
        });
    }

    // Toast notification function
    function showToast(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Toastify !== 'undefined') {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: type === 'success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }
            }).showToast();
        } else {
            // Fallback alert if no notification library is available
            alert(message);
        }
    }
});
</script>
<?= $this->endSection() ?>