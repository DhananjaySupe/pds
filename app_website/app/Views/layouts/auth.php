<!DOCTYPE html>
<html lang="en" data-layout="horizontal" data-topbar="dark" data-sidebar="light" data-sidebar-size="sm-hover" data-sidebar-image="none" data-preloader="enable" data-bs-theme="light" data-layout-width="fluid" data-layout-position="fixed" data-layout-style="default" data-sidebar-visibility="show">
<head>
	<title><?= site_title($title); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="" />
	<?= site_meta($meta); ?>
	<?= site_styles($css); ?>
	<link rel="shortcut icon" type="image/x-icon" href="<?= site_url('/favicon.ico'); ?>" />
	<meta name="robots" content="noindex">
</head>
<body>
	<!-- auth-page wrapper -->
	<div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
		<div class="bg-overlay"></div>
		<!-- auth-page content -->
		<div class="auth-page-content overflow-hidden pt-lg-5">
			<!-- Start here -->
			<?= $this->renderSection('content');?>
			<!-- end here -->
		</div>
		<!-- end auth page content -->
		<!-- footer -->
		<footer class="footer">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="text-center">
							<p class="mb-0">&copy;
								<script>document.write(new Date().getFullYear())</script> KASH IT Solutions Pvt Ltd
							</p>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- end Footer -->
    </div>
	<script type="text/javascript">
		window.SITE_URL  = "<?= trim(site_url(), '/'); ?>";
		window.SITE_TOKEN = "<?= csrf_hash(); ?>";
		window.IS_AUTHENTICATED = <?= isset($_user) ? 'true' : 'false'; ?>;
	</script>
	<?= site_scripts($js); ?>
	<?= $this->renderSection('javascripts');?>
	<?php if (isset($flashmessage) && count($flashmessage) > 0): ?>
		<script type="text/javascript">
			var msgtype = '<?php echo $flashmessage[1]; ?>';
			var message = '<?php echo $flashmessage[0]; ?>';
			switch(msgtype){
				case "danger":
				toastr.error(message);
				break;
				case "warning":
				toastr.warning(message);
				break;
				case "info":
				toastr.info(message);
				break;
				default:
				toastr.success(message);
				break;
			}
		</script>
	<?php endif; ?>
</body>
</html>