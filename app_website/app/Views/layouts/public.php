<!DOCTYPE html>
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
	<!-- Start here -->
	<?= $this->renderSection('content');?>
	<!-- end here -->
	<script type="text/javascript">
		window.SITE_URL = "<?= trim(site_url(), '/'); ?>";
		window.SITE_TOKEN = "<?= csrf_hash(); ?>";
		window.IS_AUTHENTICATED = <?= isset($_user) ? 'true' : 'false'; ?>;
	</script>
	<?= site_scripts($js); ?>
	<?= $this->renderSection('javascripts');?>
</body>
</html>