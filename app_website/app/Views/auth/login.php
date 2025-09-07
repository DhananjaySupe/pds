<?php $this->extend('layouts/auth'); ?>
<?php $this->section('content'); ?>
<div class="container">
	<div class="row">
		<div class="col-lg-12">
			<div class="card overflow-hidden">
				<div class="row g-0">
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
								<h5 class="text-primary">Welcome Back !</h5>
								<p class="text-muted">Sign in.</p>
							</div>
							<div class="mt-4">
								<form class="needs-validation" action="<?= site_url('login'); ?>" method="post" novalidate>
									<?= csrf_field(); ?>
									<div class="mb-3">
										<label for="user_type_id" class="form-label required">Select User Type</label>
										<select class="form-select" aria-label="Default select example" name="user_type_id" id="user_type_id" required>
											<option value="">Select User Type</option>
											<?php foreach ($userRoles as $userRole): ?>
												<option value="<?= $userRole['role_id']; ?>" <?= isset($formdata['user_type_id']) && $formdata['user_type_id'] == $userRole['role_id'] ? 'selected' : ''; ?>><?= $userRole['name']; ?></option>
											<?php endforeach; ?>
										</select>
										<div class="invalid-feedback">
											Please select a user type.
										</div>
									</div>
									<div class="mb-3 d-none" id="center_id_div" >
										<label for="center_id" class="form-label">Select Center</label>
										<select class="form-select" aria-label="Default select example" name="center_id" id="center_id">
											<option value="">Select Center</option>
										</select>
										<div class="invalid-feedback">
											Please select a center.
										</div>
									</div>
									<div class="mb-3">
										<label for="mobile_number" class="form-label required">Mobile number</label>
										<input type="text" class="form-control" id="mobile_number" placeholder="Enter mobile number" name="mobile_number" value="<?= isset($formdata['mobile_number']) ? $formdata['mobile_number'] : ''; ?>" pattern="[0-9]{10}" required>
										<div class="invalid-feedback">
											Please enter a valid 10-digit mobile number.
										</div>
									</div>

									<div class="mb-3">
										<div class="float-end">
											<a href="<?= site_url('forgot-password'); ?>" class="text-muted">Forgot password?</a>
										</div>
										<label class="form-label required" for="password-input">Password</label>
										<div class="position-relative auth-pass-inputgroup mb-3">
											<input type="password" class="form-control pe-5 password-input" placeholder="Enter password" id="password-input" name="password" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" value="<?= isset($formdata['password']) ? $formdata['password'] : ''; ?>">
											<button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
											<div class="invalid-feedback">
												Please enter your password (minimum 8 characters) contain one uppercase, one lowercase, one number and one special character.
											</div>
										</div>
									</div>

									<div class="mb-3">
										<div class="row">
											<div class="col-md-4">
												<img src="<?= base_url('captcha') ?>" alt="CAPTCHA" />
											</div>
											<div class="col-md-8">
												<input type="text" class="form-control" name="captcha" placeholder="Enter captcha" required />
												<div class="invalid-feedback">
													Please enter the captcha.
												</div>
											</div>
										</div>
									</div>

									<div class="mt-4">
										<button class="btn btn-success w-100" type="submit" id="login-btn" name="login-btn"  data-loading="Loading...">Sign In</button>
									</div>

									<!--<div class="mt-4 text-center">
										<div class="signin-other-title">
											<h5 class="fs-13 mb-4 title">Sign In with</h5>
										</div>

										<div>
											<button type="button" class="btn btn-primary btn-icon waves-effect waves-light"><i class="ri-facebook-fill fs-16"></i></button>
											<button type="button" class="btn btn-danger btn-icon waves-effect waves-light"><i class="ri-google-fill fs-16"></i></button>
											<button type="button" class="btn btn-dark btn-icon waves-effect waves-light"><i class="ri-github-fill fs-16"></i></button>
											<button type="button" class="btn btn-info btn-icon waves-effect waves-light"><i class="ri-twitter-fill fs-16"></i></button>
										</div>
									</div>-->

								</form>
							</div>

							<div class="mt-5 text-center">
								<p class="mb-0">Don't have an account ? <a href="<?= site_url('register'); ?>" class="fw-semibold text-primary text-decoration-underline"> Signup</a> </p>
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

		$(document).on('change', '#user_type_id', function (e) {
        e.preventDefault();
        // Handle subcategory loading
        $("#center_id option:gt(0)").remove();
        var user_type_id = $(this).val();
        if (user_type_id == 7) {
            $("#center_id").attr("disabled", true);
            $("#center_id").find("option:eq(0)").html("Loading...");
            $.ajax({
                type: "GET",
                url: SITE_URL + '/get/centers',
                dataType: 'json',
				}).done(function (data) {
					if(data.success == true){
						$("#center_id_div").removeClass("d-none");
						$("#center_id").attr("required", true);
						$("#center_id").find("option:eq(0)").html("Any");
						for (var i = 0; i < data.centers.length; i++) {
							var option = $('<option />');
							option.attr('value', data.centers[i]['id']).text(data.centers[i]['name']);
							$("#center_id").append(option);
						}
						$("#center_id").removeAttr("disabled");
					} else{
						if (typeof response.message != 'undefined') { toastr["error"](response.message); }
					}
			});
		} else {
			$("#center_id_div").addClass("d-none");
			$("#center_id").attr("disabled", false);
			$("#center_id").find("option:gt(0)").remove();
			$("#center_id").find("option:eq(0)").html("Select Center");
			$("#center_id").removeAttr("required");
		}
	});
	});
</script>
<?php $this->endSection(); ?>