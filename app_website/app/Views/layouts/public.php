<!DOCTYPE html>
<head>
	<title><?= site_title($title); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="" />
	<?= site_meta($meta); ?>
	<?= site_styles($css); ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="shortcut icon" type="image/x-icon" href="<?= site_url('/favicon.ico'); ?>" />
	<meta name="robots" content="noindex">
	<style>
		body{font-family:'Poppins',system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#212529;}
		.navbar{background:rgba(18,41,69,.85);backdrop-filter:saturate(160%) blur(8px);box-shadow:0 6px 24px rgba(0,0,0,.15);}
		.navbar .navbar-brand{font-weight:700;letter-spacing:.3px}
		.navbar .nav-link{font-weight:500}
		.navbar .btn{border-radius:999px}
		.badge-soft{background:rgba(255,193,7,.15);color:#ffc107;border:1px solid rgba(255,193,7,.35)}
		footer{color:#2b3440}
		.footer-bg{position:relative;background:linear-gradient(180deg,#f6f8fb 0,#eef2f7 100%)}
		.footer-bg:before{content:"";position:absolute;inset:0;opacity:.08;mix-blend:multiply}
		.footer-bg > .container-fluid{position:relative}
		.footer-title{font-weight:600}
		.footer-link{color:#495057;text-decoration:none}
		.footer-link:hover{color:#0d6efd}
		.qr-box{border-radius:12px;background:#fff;border:1px solid #e9ecef}
		.small-muted{color:#6c757d}
		/* Apply horizontal padding only on large screens (>=992px) */
		@media (min-width: 992px){
			.paddingxy{padding-left:100px;padding-right:100px;}
		}
	</style>
</head>
<body>
	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid paddingxy">
            <a class="navbar-brand d-flex align-items-center" href="<?= site_url(); ?>">
                <img src="assets/logo.png" alt="Kumbh Mela" height="38" class="me-2">
                <span>Kumbh <span class="text-warning">Lost&nbsp;&amp;&nbsp;Found</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('report/missing'); ?>"><i class="bi bi-person-x me-1"></i>Report Missing</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('report/found'); ?>"><i class="bi bi-person-check me-1"></i>Report Found</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('people/search'); ?>"><i class="bi bi-search me-1"></i>Search People</a></li>
                    <li class="nav-item"><a class="nav-link" href="#help-centers"><i class="bi bi-geo-alt me-1"></i>Help Centers</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact"><i class="bi bi-telephone me-1"></i>Contact</a></li>
					<li class="nav-item"><a class="nav-link btn btn-warning btn-sm px-3 fw-semibold" href="<?= site_url('login'); ?>"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
                </ul>
            </div>
        </div>
    </nav>
	<!-- Start here -->
	<?= $this->renderSection('content');?>
	<!-- end here -->
	<footer class="mt-auto py-5 footer-bg">
		<div class="container-fluid paddingxy">
			<div class="row gy-4">
				<div class="col-12 col-md-4">
					<h5 class="mb-3 footer-title">Contact Information</h5>
					<ul class="list-unstyled mb-0">
						<li class="mb-2">
							<strong>Address:</strong>
							<span>Kumbh Lost &amp; Found Help Center, Nashik, Maharashtra</span>
						</li>
						<li class="mb-2">
							<strong>Email:</strong>
							<a class="footer-link" href="mailto:support@kumbhlostandfound.in">support@kumbhlostandfound.in</a>
						</li>
						<li class="mb-2">
							<strong>Phone:</strong>
							<a class="footer-link" href="tel:+919823000000">+91 98230 00000</a>
						</li>
					</ul>
				</div>

				<div class="col-12 col-md-4">
					<h5 class="mb-3 footer-title">Emergency Numbers</h5>
					<ul class="list-unstyled mb-0">
						<li class="mb-2"><i class="bi bi-shield-lock me-2 text-primary"></i><strong>Police:</strong> 112</li>
						<li class="mb-2"><i class="bi bi-truck-front me-2 text-danger"></i><strong>Ambulance:</strong> 108</li>
						<li class="mb-2"><i class="bi bi-headset me-2 text-success"></i><strong>Helpline:</strong> 1920</li>
						<li class="mb-2"><i class="bi bi-fire me-2 text-warning"></i><strong>Fire:</strong> 101</li>
					</ul>
				</div>

				<div class="col-12 col-md-4">
					<h5 class="mb-3 footer-title">Get the App</h5>
					<div class="d-flex align-items-start gap-3">
						<div class="text-center qr-box p-2">
							<img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?= urlencode(site_url('playstore')); ?>" alt="Google Play QR" width="110" height="110">
							<div class="small mt-2 small-muted"><i class="bi bi-google-play me-1"></i>Google Play</div>
						</div>
						<div class="text-center qr-box p-2">
							<img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?= urlencode(site_url('appstore')); ?>" alt="App Store QR" width="110" height="110">
							<div class="small mt-2 small-muted"><i class="bi bi-apple me-1"></i>App Store</div>
						</div>
					</div>
				</div>
			</div>

			<hr class="my-4">

			<div class="row">
				<div class="col-12 text-center">
					<div class="small text-muted">
						<span class="badge badge-soft me-2"><i class="bi bi-calendar-event me-1"></i><script>document.write(new Date().toLocaleDateString())</script></span>
						&copy; <script>document.write(new Date().getFullYear())</script> Kumbh Lost &amp; Found · All rights reserved.
					</div>
				</div>
			</div>
		</div>
	</footer>

	<script type="text/javascript">
		window.SITE_URL = "<?= trim(site_url(), '/'); ?>";
		window.SITE_TOKEN = "<?= csrf_hash(); ?>";
		window.IS_AUTHENTICATED = <?= isset($_user) ? 'true' : 'false'; ?>;
	</script>
	<?= site_scripts($js); ?>
	<?= $this->renderSection('javascripts');?>
</body>
</html>