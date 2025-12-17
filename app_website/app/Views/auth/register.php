<?php $this->extend('layouts/auth'); ?>
<?php $this->section('content'); ?>
<div class="container">
	<div class="row">
		<div class="col-lg-12">
			<div class="card overflow-hidden m-0">
				<div class="row justify-content-center g-0">
					<?= view('auth/carousel'); ?>
					<div class="col-lg-6">
						<div class="p-lg-5 p-4">
							<?php if (isset($formdata['errors']) && !empty($formdata['errors'])): ?>
								<div class="alert alert-danger alert-dismissible border-2 bg-body-secondary fade show" role="alert">
									<strong> Something is very wrong! </strong><b><?= $formdata['errors']; ?></b>
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
							<?php endif; ?>
							<div>
								<h5 class="text-primary">Register Account</h5>
							</div>
							<div class="mt-4">
								<form class="needs-validation" action="<?= site_url('register'); ?>" method="post" novalidate>
									<?= csrf_field(); ?>
									<div class="mb-3">
										<label for="full_name" class="form-label">Full name <span class="text-danger">*</span></label>
										<input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter full name" value="<?= isset($formdata['full_name']) ? $formdata['full_name'] : ''; ?>" required>
										<div class="invalid-feedback">
											Please enter full name
										</div>
									</div>
									<div class="mb-3">
										<label for="mobile_number" class="form-label">Mobile number <span class="text-danger">*</span></label>
										<input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter mobile number" value="<?= isset($formdata['mobile_number']) ? $formdata['mobile_number'] : ''; ?>" pattern="[0-9]{10}" required>
										<div class="invalid-feedback">
											Please enter a valid 10-digit mobile number
										</div>
									</div>
									<div class="mb-3">
										<label for="email" class="form-label">Email <span class="text-danger">*</span></label>
										<input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" value="<?= isset($formdata['email']) ? $formdata['email'] : ''; ?>" required>
										<div class="invalid-feedback">
											Please enter a valid email address
										</div>
									</div>

									<div class="mb-3">
										<label class="form-label" for="password-input">Password <span class="text-danger">*</span></label>
										<div class="position-relative auth-pass-inputgroup">
											<input type="password" class="form-control pe-5 password-input" onpaste="return false" placeholder="Enter password" id="password-input" name="password" aria-describedby="passwordInput" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" value="<?= isset($formdata['password']) ? $formdata['password'] : ''; ?>">
											<button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
											<div class="invalid-feedback">
												Please enter your password (minimum 8 characters) contain one uppercase, one lowercase, one number and one special character.
											</div>
										</div>
									</div>

									<div class="mb-4">
										<p class="mb-0 fs-12 text-muted fst-italic">By registering you agree to the Velzon <a href="<?= site_url('terms-of-use'); ?>" class="text-primary text-decoration-underline fst-normal fw-medium">Terms of Use</a></p>
									</div>

									<div class="mt-4">
										<button class="btn btn-success w-100" type="submit" id="register-btn" name="register-btn" data-loading="Loading..." >Sign Up</button>
									</div>
								</form>
							</div>

							<div class="mt-5 text-center">
								<p class="mb-0">Already have an account ? <a href="<?= site_url('login'); ?>" class="fw-semibold text-primary text-decoration-underline"> Signin</a> </p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->endSection(); ?>
<?php $this->section('javascripts'); ?>
<script>
$(document).ready(function() {
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
		}, false);
	})();

	// Real-time validation feedback
	$('.form-control, .form-select').on('input change', function() {
		if (this.checkValidity()) {
			$(this).removeClass('is-invalid').addClass('is-valid');
		} else {
			$(this).removeClass('is-valid').addClass('is-invalid');
		}
	});

	// Password visibility toggle
	$("#password-addon").on('click', function(e) {
		e.preventDefault();
		var passwordInput = $("#password-input");
		var icon = $(this).find('i');

		if (passwordInput.attr('type') === 'password') {
			passwordInput.attr('type', 'text');
			icon.removeClass('ri-eye-fill').addClass('ri-eye-off-fill');
		} else {
			passwordInput.attr('type', 'password');
			icon.removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
		}
	});

	// Mobile number validation
	$('#mobile_number').on('input', function() {
		var value = $(this).val();
		// Remove non-numeric characters
		value = value.replace(/[^0-9]/g, '');
		// Limit to 10 digits
		value = value.substring(0, 10);
		$(this).val(value);
	});

	// Password validation
	$('#password-input').on('input', function() {
		var password = $(this).val();
		var isValid = true;
		var feedback = [];

		// Check minimum length
		if (password.length < 8) {
			isValid = false;
			feedback.push("at least 8 characters");
		}

		// Check for uppercase letter
		if (!/[A-Z]/.test(password)) {
			isValid = false;
			feedback.push("one uppercase letter");
		}

		// Check for lowercase letter
		if (!/[a-z]/.test(password)) {
			isValid = false;
			feedback.push("one lowercase letter");
		}

		// Check for number
		if (!/\d/.test(password)) {
			isValid = false;
			feedback.push("one number");
		}

		// Check for special character
		if (!/[@$!%*?&]/.test(password)) {
			isValid = false;
			feedback.push("one special character (@$!%*?&)");
		}

		// Update validation state
		if (isValid) {
			$(this).removeClass('is-invalid').addClass('is-valid');
			$(this).next('.invalid-feedback').hide();
		} else {
			$(this).removeClass('is-valid').addClass('is-invalid');
			$(this).next('.invalid-feedback').text('Password must contain: ' + feedback.join(', ')).show();
		}
	});
});
</script>
<?php $this->endSection(); ?>