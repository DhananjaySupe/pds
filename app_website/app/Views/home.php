<?php $this->extend('layouts/public'); ?>
<?php $this->section('content'); ?>

<!-- Hero Section -->
<section id="home" class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; min-height: 80vh; display: flex; align-items: center;">
    <div class="container-fluid paddingxy">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="mb-4">
                    <span class="badge bg-light text-dark px-3 py-2 mb-3" style="font-size: 0.9rem;">
                        <i class="bi bi-star-fill text-warning me-1"></i>Professional Inventory Management
                    </span>
                </div>
                <h1 class="display-4 fw-bold mb-4" style="line-height: 1.2;">
                    Streamline Your Stock Management with Ease
                </h1>
                <p class="lead mb-4" style="font-size: 1.25rem; opacity: 0.95;">
                    Take control of your inventory with our powerful, intuitive stock management system. Track products, monitor stock levels, and make data-driven decisions.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= site_url('login'); ?>" class="btn btn-warning btn-lg px-4 py-3 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Get Started
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-4 py-3 fw-semibold">
                        <i class="bi bi-arrow-down-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <div class="position-relative">
                    <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 20px; padding: 3rem; border: 1px solid rgba(255,255,255,0.2);">
                        <i class="bi bi-box-seam" style="font-size: 8rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5" style="background: #f8f9fa;">
    <div class="container-fluid paddingxy py-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Powerful Features</h2>
                <p class="lead text-muted">Everything you need to manage your inventory efficiently</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-clipboard-data text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Real-Time Tracking</h5>
                        <p class="text-muted mb-0">Monitor your stock levels in real-time with instant updates and accurate inventory counts.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-graph-up-arrow text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Analytics & Reports</h5>
                        <p class="text-muted mb-0">Generate comprehensive reports and insights to make informed business decisions.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bell text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Low Stock Alerts</h5>
                        <p class="text-muted mb-0">Get automatic notifications when stock levels are running low to prevent stockouts.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-people text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Multi-User Access</h5>
                        <p class="text-muted mb-0">Collaborate with your team with role-based access control and permissions.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-cloud-arrow-up text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Cloud-Based Storage</h5>
                        <p class="text-muted mb-0">Access your inventory data from anywhere, anytime with secure cloud storage.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-shield-check text-white" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Secure & Reliable</h5>
                        <p class="text-muted mb-0">Your data is protected with enterprise-grade security and regular backups.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5" style="background: white;">
    <div class="container-fluid paddingxy py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h2 class="display-5 fw-bold mb-4">Why Choose Our Inventory System?</h2>
                <div class="mb-4">
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-check-circle text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fw-bold mb-2">Easy to Use</h5>
                            <p class="text-muted mb-0">Intuitive interface designed for users of all technical levels. Get started in minutes.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-speedometer2 text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fw-bold mb-2">Save Time & Money</h5>
                            <p class="text-muted mb-0">Reduce manual errors, eliminate paperwork, and optimize your inventory operations.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-arrow-repeat text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="fw-bold mb-2">Scalable Solution</h5>
                            <p class="text-muted mb-0">Grows with your business. Whether you have 10 or 10,000 products, we've got you covered.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-4" style="background: #f8f9fa; border-radius: 15px;">
                            <div class="display-4 fw-bold text-primary mb-2">99.9%</div>
                            <div class="text-muted">Uptime</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-4" style="background: #f8f9fa; border-radius: 15px;">
                            <div class="display-4 fw-bold text-success mb-2">24/7</div>
                            <div class="text-muted">Support</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-4" style="background: #f8f9fa; border-radius: 15px;">
                            <div class="display-4 fw-bold text-warning mb-2">1000+</div>
                            <div class="text-muted">Happy Clients</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-4" style="background: #f8f9fa; border-radius: 15px;">
                            <div class="display-4 fw-bold text-info mb-2">50K+</div>
                            <div class="text-muted">Products Managed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="container-fluid paddingxy py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Inventory Management?</h2>
                <p class="lead mb-4" style="opacity: 0.95;">
                    Join thousands of businesses that trust our system to manage their stock efficiently.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= site_url('register'); ?>" class="btn btn-warning btn-lg px-5 py-3 fw-semibold">
                        <i class="bi bi-person-plus me-2"></i>Create Free Account
                    </a>
                    <a href="<?= site_url('login'); ?>" class="btn btn-outline-light btn-lg px-5 py-3 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

@media (max-width: 991px) {
    .display-4 {
        font-size: 2rem;
    }
    .display-5 {
        font-size: 1.75rem;
    }
}
</style>

<?php $this->endSection(); ?>
<?php $this->section('javascripts'); ?>
<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
<?php $this->endSection(); ?>