<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Welcome to CodeIgniter 4!</title>
		<meta name="description" content="The small framework with powerful features">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" type="image/png" href="/favicon.ico">
		<link href='<?= base_url('assets/css/bootstrap.min.css'); ?>' rel='stylesheet' />
		<link href='<?= base_url('assets/css/icons.min.css'); ?>' rel='stylesheet' />
		<link href='<?= base_url('assets/css/app.min.css'); ?>' rel='stylesheet' />
		<link href='<?= base_url('assets/css/custom.min.css'); ?>' rel='stylesheet' />
		<link href='<?= base_url('assets/css/toastr.css'); ?>' rel='stylesheet' />
		<link href='<?= base_url('assets/css/style.css?v=1.0.0'); ?>' rel='stylesheet' />
	</head>
	<body>
		<!-- auth-page wrapper -->
		<div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
			<div class="bg-overlay"></div>
			<!-- auth-page content -->
			<div class="auth-page-content overflow-hidden pt-lg-5">
				<div class="auth-page-content">
					<div class="container">
						<div class="row">
							<div class="col-lg-12">
								<div class="text-center mt-sm-5 pt-4">
									<div class="mb-5 text-white-50">
										<h1 class="display-5 coming-soon-text">Site is Under Maintenance</h1>
										<p class="fs-14">Please check back in sometime</p>
										<div class="mt-4 pt-2">
											<a href="<?= base_url(); ?>" class="btn btn-success"><i class="mdi mdi-home me-1"></i> Back to Home</a>
										</div>
									</div>
									<div class="row justify-content-center mb-5">
										<div class="col-xl-4 col-lg-8">
											<div>
												<img src="<?= base_url('assets/images/maintenance.png'); ?>" alt="" class="img-fluid">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
