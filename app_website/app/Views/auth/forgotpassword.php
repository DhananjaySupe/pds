<?php $this->extend('layouts/auth'); ?>
<?php $this->section('content'); ?>
<div class="auth-maintenance d-flex align-items-center min-vh-100">
    <div class="bg-overlay bg-light"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="auth-full-page-content d-flex min-vh-100 py-sm-5 py-4">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100 py-0 py-xl-3">
                            <div class="text-center mb-4">
                                <a href="<?= site_url('/'); ?>" class="">
                                    <img src="<?= site_url('/assets/images/logo-dark.png'); ?>" alt="" height="22" class="auth-logo logo-dark mx-auto">
                                    <img src="<?= site_url('/assets/images/logo-light.png'); ?>" alt="" height="22" class="auth-logo logo-light mx-auto">
								</a>
                                <p class="text-muted mt-2"><?= site_tagline(); ?></p>
							</div>

                            <div class="card my-auto overflow-hidden">
								<div class="row g-0">
									<div class="col-lg-6">
										<div class="bg-overlay bg-primary"></div>
										<div class="h-100 bg-auth align-items-end">
										</div>
									</div>

									<div class="col-lg-6">
										<div class="p-lg-5 p-4">
											<div>
												<div class="text-center mt-1">
													<h4 class="font-size-18">Forgot Password !</h4>
													<p class="text-muted">Enter your email to reset your password.</p>
												</div>

                                                <?php if (isset($formdata['errors']) && !empty($formdata['errors'])): ?>
                                                    <div class="alert alert-danger" role="alert">
                                                        <?= $formdata['errors']; ?>
                                                    </div>
                                                <?php endif; ?>

												<form action="<?= site_url('/forgot-password'); ?>" method="post" class="auth-input needs-validation" novalidate>
													<input type="hidden" name="csrftoken" value="<?= $formdata['csrftoken']; ?>">
													<div class="mb-2">
														<label for="email" class="form-label">Email</label>
														<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
														<div class="invalid-feedback">
															Please enter a valid email address.
														</div>
													</div>
													<div class="mt-3">
														<button class="btn btn-primary w-100" type="submit">Sign In</button>
													</div>
												</form>
											</div>

											<div class="mt-4 text-center">
												<p class="mb-0">Already have an account ? <a href="<?= site_url('/login'); ?>" class="fw-medium text-primary"> Login</a> </p>
											</div>
										</div>
									</div>
								</div>
							</div>
                            <!-- end card -->
						</div>
					</div>
				</div>
			</div>
            <!-- end col -->
		</div>
        <!-- end row -->
	</div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('javascripts'); ?>
<script>
// Form validation
(function () {
    'use strict'

    // Fetch all forms we want to apply validation to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
<?php $this->endSection(); ?>