<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">User Profile</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('users') ?>">Users</a></li>
                            <li class="breadcrumb-item active">User Profile</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <!-- User Profile Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                <img src="<?= $user['profile_photo'] ? site_url('uploads/users/thumb/'.$user['profile_photo']) : site_url('assets/images/user.png') ?>"
                                     class="rounded-circle avatar-xxl img-thumbnail user-profile-image"
                                     alt="user-profile">
                                <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                    <label for="profile-img-file-input" class="avatar-title rounded-circle bg-light text-body">
                                        <i class="ri-camera-fill"></i>
                                    </label>
                                </div>
                            </div>
                            <h5 class="mb-1"><?= $user['full_name'] ?></h5>
                            <p class="text-muted mb-3"><?= ucfirst($user['role']) ?></p>

                            <!-- Status Badge -->
                            <div class="mb-3">
                                <?php if($user['status'] == '1'): ?>
                                    <span class="badge bg-success-subtle text-success fs-12">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger fs-12">Inactive</span>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="<?= site_url('users/edit/'.$user['user_id']) ?>" class="btn btn-primary btn-sm">
                                    <i class="ri-edit-line align-bottom me-1"></i> Edit
                                </a>
                                <a href="<?= site_url('users') ?>" class="btn btn-light btn-sm">
                                    <i class="ri-arrow-left-line align-bottom me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Email Address</label>
                            <p class="mb-0">
                                <i class="ri-mail-line me-2 text-muted"></i>
                                <?= $user['email'] ?>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Phone Number</label>
                            <p class="mb-0">
                                <i class="ri-phone-line me-2 text-muted"></i>
                                <?= $user['phone'] ?>
                            </p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-muted">Language</label>
                            <p class="mb-0">
                                <i class="ri-translate-2 me-2 text-muted"></i>
                                <?= $user['language'] == 'en' ? 'English' : 'Hindi' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details -->
            <div class="col-lg-8">
                <!-- Account Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">User ID</label>
                                    <p class="form-control-plaintext fw-medium">#<?= $user['user_id'] ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Role</label>
                                    <p class="form-control-plaintext fw-medium"><?= ucfirst($user['role']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Account Status</label>
                                    <p class="form-control-plaintext">
                                        <?php if($user['status'] == '1'): ?>
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Language Preference</label>
                                    <p class="form-control-plaintext fw-medium">
                                        <?= $user['language'] == 'en' ? 'English' : 'Hindi' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Login Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Login Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Last Login</label>
                                    <p class="form-control-plaintext fw-medium">
                                        <?php if($user['last_login_at']): ?>
                                            <i class="ri-time-line me-1 text-muted"></i>
                                            <?= applicationDate($user['last_login_at']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never logged in</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Last Login IP</label>
                                    <p class="form-control-plaintext fw-medium">
                                        <?php if($user['last_login_ip']): ?>
                                            <i class="ri-global-line me-1 text-muted"></i>
                                            <?= $user['last_login_ip'] ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Account Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Account Created</label>
                                    <p class="form-control-plaintext fw-medium">
                                        <i class="ri-calendar-line me-1 text-muted"></i>
                                        <?= applicationDate($user['created_at']) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">Last Updated</label>
                                    <p class="form-control-plaintext fw-medium">
                                        <i class="ri-edit-line me-1 text-muted"></i>
                                        <?= applicationDate($user['updated_at']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?= site_url('users/edit/'.$user['user_id']) ?>" class="btn btn-primary">
                                <i class="ri-edit-line align-bottom me-1"></i> Edit User
                            </a>
                            <?php if($user['status'] == '1'): ?>
                                <button type="button" class="btn btn-warning" onclick="changeStatus(<?= $user['user_id'] ?>, 0)">
                                    <i class="ri-lock-line align-bottom me-1"></i> Deactivate
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success" onclick="changeStatus(<?= $user['user_id'] ?>, 1)">
                                    <i class="ri-lock-unlock-line align-bottom me-1"></i> Activate
                                </button>
                            <?php endif; ?>
                            <a href="<?= site_url('users') ?>" class="btn btn-secondary">
                                <i class="ri-arrow-left-line align-bottom me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeStatus(userId, status) {
    const action = status == 1 ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        // You can implement AJAX call here to change user status
        window.location.href = `<?= site_url('users/change-status/') ?>${userId}/${status}`;
    }
}
</script>

<?= $this->endSection() ?>