<?php $this->extend('layouts/auth'); ?>
<?php $this->section('content'); ?>
<div class="container">
	<div class="row">
		<div class="col-lg-12">
			<div class="overflow-hidden">
				<div class="row g-0 justify-content-center">
					<div class="col-lg-6 card">
						<div class="p-lg-5 p-4">
							<div>
								<h5 class="text-primary">Use mobile application to login</h5>
								<p class="text-muted">Please download the mobile application from the play store and login using the code <?= $user['code']; ?>.</p>
								<a href="<?= app_config('appDownloadUrl'); ?>" target="_blank" class="btn btn-primary">Download App</a>
								<img src="<?= base_url(app_config('appDownloadUrlQr')); ?>" alt="QR Code" class="img-fluid">
								<p class="text-muted">Or scan the QR code to download the app.</p>
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

<?php $this->endSection(); ?>