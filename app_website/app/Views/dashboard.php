<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-home-alt me-2"></i>
                        Dashboard
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section('javascripts'); ?>
<script>
    // Dashboard specific JavaScript can be added here
    $(document).ready(function() {
        // Initialize any dashboard-specific functionality
        console.log('Dashboard loaded successfully');
    });
</script>
<?php $this->endSection(); ?>