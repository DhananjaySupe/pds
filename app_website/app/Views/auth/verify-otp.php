<?php $this->extend('layouts/auth'); ?>
<?php $this->section('content'); ?>
<div class="container">
	<div class="row">
		<div class="col-lg-12">
			<div class="overflow-hidden">
				<div class="row g-0 justify-content-center">
					<div class="col-lg-6 card">
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
								    <form class="needs-validation" action="<?= site_url('verify-otp/'.$code); ?>" method="post" novalidate>
									<?= csrf_field(); ?>

									<div class="mb-3">
										<label for="otp" class="form-label required">OTP <span class="text-danger">*</span></label>
										<div class="d-flex justify-content-center gap-2 mb-2">
											<input type="text" class="form-control otp-input text-center" id="otp1" name="otp1" maxlength="1" pattern="[0-9]" required>
											<input type="text" class="form-control otp-input text-center" id="otp2" name="otp2" maxlength="1" pattern="[0-9]" required>
											<input type="text" class="form-control otp-input text-center" id="otp3" name="otp3" maxlength="1" pattern="[0-9]" required>
											<input type="text" class="form-control otp-input text-center" id="otp4" name="otp4" maxlength="1" pattern="[0-9]" required>
											<input type="text" class="form-control otp-input text-center" id="otp5" name="otp5" maxlength="1" pattern="[0-9]" required>
											<input type="text" class="form-control otp-input text-center" id="otp6" name="otp6" maxlength="1" pattern="[0-9]" required>
										</div>
										<input type="hidden" id="otp" name="otp" value="">
										<div class="invalid-feedback">
											Please enter a valid 6-digit OTP.
										</div>
									</div>

									<div class="mt-4">
										<button class="btn btn-success w-100" type="submit" id="verify-otp-btn" name="verify-otp-btn"  data-loading="Loading..." >Verify OTP</button>
									</div>
								</form>
							</div>
							<div class="mt-5 text-center">
								<p class="mb-0"><a href="<?= site_url('login'); ?>" class="fw-semibold text-primary  text-decoration-underline"> Back to Login</a> </p>
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
document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const hiddenOtpInput = document.getElementById('otp');
    const form = document.querySelector('form');

    // Handle input events for OTP fields
    otpInputs.forEach((input, index) => {
        // Only allow numbers
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            if (value.length > 0) {
                // Ensure only numbers are entered
                if (!/^[0-9]$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Move to next input if available
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            }
        });

        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        // Handle paste event
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);

            if (pastedData.length === 6) {
                otpInputs.forEach((input, i) => {
                    input.value = pastedData[i] || '';
                });
                updateHiddenOtp();
            }
        });
    });

    // Update hidden OTP field when any input changes
    function updateHiddenOtp() {
        const otpValue = Array.from(otpInputs).map(input => input.value).join('');
        hiddenOtpInput.value = otpValue;
    }

    // Add event listeners to update hidden field
    otpInputs.forEach(input => {
        input.addEventListener('input', updateHiddenOtp);
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        const otpValue = Array.from(otpInputs).map(input => input.value).join('');

        if (otpValue.length !== 6) {
            e.preventDefault();
            alert('Please enter a complete 6-digit OTP.');
            return false;
        }

        if (!/^[0-9]{6}$/.test(otpValue)) {
            e.preventDefault();
            alert('Please enter only numbers for the OTP.');
            return false;
        }

        // Update hidden field before submit
        hiddenOtpInput.value = otpValue;
    });

    // Auto-focus first input on page load
    otpInputs[0].focus();
});
</script>
<style>
.otp-input {
    width: 50px !important;
    height: 50px;
    font-size: 18px;
    font-weight: bold;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
    text-align: center;
}

.otp-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    outline: none;
}

.otp-input:valid {
    border-color: #198754;
}

.otp-input:invalid:not(:placeholder-shown) {
    border-color: #dc3545;
}
</style>
<?php $this->endSection(); ?>