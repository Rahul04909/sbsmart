<?php
// includes/footer.php - Application-wide footer, includes closing HTML tags and JavaScript
// Save as UTF-8 WITHOUT BOM.

// Define the current year for the copyright notice
$current_year = date("Y");
?>

<!-- ===========================SITE FOOTER===========================-->
<?php require_once __DIR__ . '/login-modal.php'; ?>

<footer class="site-footer mt-5" aria-labelledby="footer-heading">
  <h2 id="footer-heading" class="visually-hidden">Footer</h2>

  <!-- FOOTER TOP -->
  <div class="footer-top text-light pt-5 pb-4" style="background:#0f0f0f;">
    <div class="container">
      <div class="row gy-4">

        <!-- About (Column 1) -->
        <div class="col-12 col-md-4">
          <a href="/index.php" class="d-inline-block mb-3" aria-label="SBSmart Home">
            <!-- Note: You must ensure /assets/images/logo.png and logo-text.png exist -->
            <img src="/assets/images/logo.png"
                 alt="SBSmart"
                 style="height:50px;max-width:100%;"
                 loading="lazy"
                 onerror="this.src='/assets/images/logo-text.png'">
          </a>

          <p class="small mb-3 text-light">
            SB Smart is the official e-commerce portal of <strong>S.B. Syscon Pvt. Ltd.</strong>, created to offer a seamless, transparent, and efficient digital buying experience for industrial electrical products.
          </p>

          <!-- Social icons -->
          <div class="d-flex gap-3 mt-2" aria-label="Social links">
            <a href="#" class="text-light small" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-light small" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-light small" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="text-light small" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          </div>
        </div>

        <!-- Quick Links (Column 2) -->
        <div class="col-6 col-md-2">
          <h6 class="footer-heading text-warning fw-bold mb-2">Quick Links</h6>
          <ul class="list-unstyled small mb-0">
            <li class="mb-2"><a class="text-light text-decoration-none" href="/index.php">Home</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="/products.php">Products</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="/assisted-orders.php">Assisted Orders</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="/about.php">About Us</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="/contact.php">Contact</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="blog.php">Blog</a></li>
          </ul>
        </div>

        <!-- Customer Service (Column 3) -->
        <div class="col-6 col-md-3">
          <h6 class="footer-heading text-warning fw-bold mb-2">Customer Service</h6>
          <ul class="list-unstyled small mb-0">
            <li class="mb-2"><a class="text-light text-decoration-none" href="faqs.php">FAQs</a></li>
            <!-- Updated policy link paths to match root files -->
            <li class="mb-2"><a class="text-light text-decoration-none" href="shipping-and-delivery-policy.php">Shipping Policy</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="refund-and-cancellation-policy.php">Return &amp; Refund</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="terms-and-conditions.php">Terms &amp; Conditions</a></li>
            <li class="mb-2"><a class="text-light text-decoration-none" href="privacy-policy.php">Privacy Policy</a></li>
          </ul>
        </div>

        <!-- Contact (Column 4) -->
        <div class="col-12 col-md-3">
          <h6 class="footer-heading text-warning fw-bold mb-2">Contact Us</h6>

          <div class="small mb-2">
            <i class="fas fa-map-marker-alt me-2"></i>
            1D-45A, NIT Faridabad, Haryana, India – 121001
          </div>

          <div class="small mb-2">
            <i class="fas fa-phone me-2"></i>
            <a class="text-light text-decoration-none" href="tel:+911294150555">(+91) 129 4150 555</a>
          </div>

          <div class="small mb-2">
            <i class="fas fa-envelope me-2"></i>
            <a class="text-light text-decoration-none" href="mailto:marcom.sbsyscon@gmail.com">marcom.sbsyscon@gmail.com</a>
          </div>

          <div class="small fw-semibold mt-2" style="color:#ffb100;">
            <i class="fas fa-clock me-2"></i>
            Mon - Sat: 9:30am – 6:30pm
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- FOOTER BOTTOM -->
  <div class="footer-bottom text-center text-light py-3"
       style="background:#111;border-top:1px solid rgba(255,255,255,.05);">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
      <div class="small mb-1 mb-md-0">
        &copy; <?= $current_year ?>
        <span class="fw-bold text-warning">S.B. Syscon Pvt. Ltd.</span>.
        All Rights Reserved.
      </div>

      <div class="small">
        Design by
        <a href="https://mineib.com" target="_blank"
           class="fw-bold text-warning text-decoration-none">Mineib</a>
      </div>
    </div>
  </div>

  <!-- FontAwesome for icons like fa-facebook-f, fa-map-marker-alt, etc. -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</footer>

<!-- -------------------------
     JAVASCRIPT INCLUDES
------------------------- -->

<!-- Bootstrap Bundle with Popper (using the version you provided) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

<!-- Custom site-wide JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Simple initialization for any tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Ensure the carousel starts/functions correctly if it exists on the page
        var carouselElement = document.getElementById('homepageCarousel');
        if (carouselElement) {
            new bootstrap.Carousel(carouselElement);
        }

        // --- GLOBAL: Intercept Add to Cart for Guest Users ---
        // We inject the PHP session status here. 
        // Note: verify session is started in your header/init files.
        const IS_LOGGED_IN = <?php echo json_encode(!empty($_SESSION['user']['id'])); ?>;
        
        if (!IS_LOGGED_IN) {
            document.body.addEventListener('submit', function(e) {
                // Check if the submitted form is an add-to-cart form
                if (e.target && e.target.matches('form[action="cart-add.php"]')) {
                    e.preventDefault(); // Stop submission
                    
                    // Show login modal
                    var authModalEl = document.getElementById('authModal');
                    if (authModalEl) {
                        // Use Bootstrap 5 API
                        var modal = bootstrap.Modal.getOrCreateInstance(authModalEl);
                        modal.show();
                        
                        // Default to login tab
                        if (typeof switchTab === 'function') {
                            switchTab('login');
                        }
                    } else {
                        // Fallback if modal missing
                         window.location.href = 'account.php?tab=login';
                    }
                }
            });
        }
    });

    // Simple function to display a custom notification (used instead of alert())
    function showNotification(message, type = 'info') {
        console.log(`Notification (${type}): ${message}`);
        // Implementation for a real toast/modal should go here
    }
</script>

</body>
</html>
