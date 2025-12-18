<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
    $avatar = !empty($user['profile_photo']) ? site_url('uploads/users/thumb/'.$user['profile_photo']) : site_url('assets/images/user.png');
    $statusClass = 'bg-secondary-subtle text-muted';
    $statusClass = $user['status'] == '1' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted';
    $userImageConstraints = 'max-width: 220px; max-height: 220px; object-fit: cover;';
?>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-user-line me-2"></i>User Details</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= site_url('users/edit/'.$user['user_id']) ?>" class="btn btn-secondary btn-label">
                            <i class="ri-pencil-line label-icon align-middle fs-16 me-2"></i> Edit
                        </a>
                        <a href="<?= site_url('users') ?>" class="btn btn-outline-dark btn-label">
                            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row g-3 align-items-stretch">
                <div class="col-lg-4 d-flex">
                    <div class="card w-100 h-100">
                        <div class="card-body text-center">
                            <img src="<?= $avatar ?>" alt="<?= esc($user['full_name']) ?>" class="avatar-xxl rounded-circle mb-3" style="<?= $userImageConstraints ?>">
                            <h4 class="mb-1"><?= esc($user['full_name']) ?></h4>
                            <?php if(!empty($user['user_type'])): ?>
                                <p class="text-muted mb-2"><?= esc($user['user_type']) ?></p>
                            <?php endif; ?>
                            <span class="badge <?= $statusClass ?>"><?= ($user['status'] == '1' ? 'Active' : 'Inactive') ?></span>
                            <div class="mt-4 text-start">
                                <p class="mb-2"><i class="ri-mail-line me-2 text-muted"></i><a href="mailto:<?= esc($user['email']) ?>" class="text-reset"><?= esc($user['email']) ?></a></p>
                                <?php if(!empty($user['phone'])): ?>
                                    <p class="mb-2"><i class="ri-phone-line me-2 text-muted"></i><a href="tel:<?= esc($user['phone']) ?>" class="text-reset"><?= esc($user['phone']) ?></a></p>
                                <?php endif; ?>
                                <?php if(!empty($user['language'])): ?>
                                    <p class="mb-0"><i class="ri-translate-2 me-2 text-muted"></i><?= esc(strtoupper($user['language'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 d-flex">
                    <div class="card w-100 h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Full Name</p>
                                    <h6><?= esc($user['full_name']) ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">User Type</p>
                                    <h6><?= !empty($user['user_type']) ? esc($user['user_type']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Email</p>
                                    <h6><?= esc($user['email']) ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Phone</p>
                                    <h6><?= !empty($user['phone']) ? esc($user['phone']) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Status</p>
                                    <h6><span class="badge <?= $statusClass ?>"><?= ($user['status'] == '1' ? 'Active' : 'Inactive') ?></span></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Created At</p>
                                    <h6><?= !empty($user['created_at']) ? date('d M Y h:i A', strtotime($user['created_at'])) : '—' ?></h6>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Language</p>
                                    <h6><?= !empty($user['language']) ? esc(strtoupper($user['language'])) : '—' ?></h6>
                                </div>
                                <?php if(!empty($user['last_login_at'])): ?>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Last Login</p>
                                    <h6><?= date('d M Y h:i A', strtotime($user['last_login_at'])) ?></h6>
                                </div>
                                <?php endif; ?>
                                <?php if(!empty($user['last_login_ip'])): ?>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Last Login IP</p>
                                    <h6><?= esc($user['last_login_ip']) ?></h6>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

