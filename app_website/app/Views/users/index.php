<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Users</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row g-4 align-items-center">
                            <div class="col-sm">
                                <div>
                                    <h5 class="card-title mb-0">Users List</h5>
                                </div>
                            </div>
                            <div class="col-sm-auto">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= site_url('users/new') ?>" class="btn btn-success">
                                        <i class="ri-add-line align-bottom me-1"></i> Add New User
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <div class="search-box">
                                    <input type="text" class="form-control search" placeholder="Search users...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="filterRole">
                                    <option value="">All Roles</option>
                                    <?php if(isset($roles) && !empty($roles)): ?>
                                        <?php foreach($roles as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"><?= $role['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <select class="form-select" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="email" class="form-control" id="filterEmail" placeholder="Filter by Email">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="filterPhone" placeholder="Filter by Phone">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="filterDate" placeholder="Filter by Date Range" value="<?= date('d M Y', strtotime('-3 months')) . ' - ' . date('d M Y') ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-warning" id="btnFilter" title="Reset Filters">
                                    <i class="ri-refresh-line"></i>
                                </button>
                                <button type="button" class="btn btn-success" id="btnExport" title="Export to Excel">
                                    <i class="ri-file-excel-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="usersTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 70px;">ID</th>
                                        <th scope="col">Photo</th>
                                        <th scope="col">User Details</th>
                                        <th scope="col">Role & Status</th>
                                        <th scope="col">Language & Last Login</th>
                                        <th scope="col">Created Date</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset the password for this user? A new random password will be generated.</p>
                <div id="newPasswordDisplay" class="alert alert-info" style="display: none;">
                    <strong>New Password:</strong> <span id="newPasswordText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmResetPassword">Reset Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>