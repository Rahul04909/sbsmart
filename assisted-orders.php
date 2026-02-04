<?php
$page_title = "Assisted Orders - SBSmart";
$meta_description = "Get personalized support for bulk orders, project requirements, and technical consultations with SBSmart Assisted Orders.";
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Page Specific Styles for Premium Feel */
    .assisted-hero {
        background: radial-gradient(circle at top right, #eef2ff 0%, #ffffff 100%);
        padding: 5rem 0 3rem;
        position: relative;
        overflow: hidden;
    }
    
    .blob-bg {
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(13,110,253,0.05) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        z-index: 0;
        pointer-events: none;
    }

    .process-card {
        border: 1px solid rgba(0,0,0,0.05);
        background: #fff;
        padding: 2rem;
        border-radius: 16px;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .process-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        border-color: rgba(13,110,253,0.3);
    }
    .process-icon-wrapper {
        width: 60px;
        height: 60px;
        background: #e7f1ff;
        color: #0d6efd;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .benefit-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .benefit-check {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        background: #198754;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .contact-card-premium {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.03);
        overflow: hidden;
    }
    
    .form-control-lg {
        font-size: 0.95rem;
        padding: 0.8rem 1rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background-color: #fcfcfc;
    }
    .form-control-lg:focus {
        background-color: #fff;
        border-color: #8bb9fe;
        box-shadow: 0 0 0 4px rgba(13,110,253,0.1);
    }
</style>

<!-- Hero Section -->
<section class="assisted-hero text-center text-lg-start">
    <div class="blob-bg"></div>
    <div class="container position-relative z-1">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3 border border-primary border-opacity-25">
                    <i class="bi bi-star-fill me-1"></i> Premium Service
                </span>
                <h1 class="display-4 fw-bold text-dark mb-3">Assisted Orders</h1>
                <p class="lead text-secondary opacity-75 mb-4" style="max-width: 600px;">
                    Experience personalized procurement. From bulk requirements to complex technical specifications, our experts are here to guide you every step of the way.
                </p>
                <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-lg-start">
                    <a href="#inquiryForm" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Get a Quote</a>
                    <a href="#contactDetails" class="btn btn-outline-dark btn-lg rounded-pill px-5">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="assets/images/assisted_orders_hero.png" alt="Assisted Orders" class="img-fluid" style="max-height: 400px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));">
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Section -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row g-5">
            <div class="col-lg-5">
                <h2 class="fw-bold mb-4">Why choose Assisted Orders?</h2>
                <p class="text-secondary mb-4">
                    For standard procurements, our cart is perfect. But when your needs go beyond the click of a button, our Assisted Order service ensures you get the technical accuracy and commercial flexibility you require.
                </p>
                
                <div class="benefit-item">
                    <div class="benefit-check"><i class="bi bi-check-lg"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Bulk Pricing Strategy</h6>
                        <small class="text-muted">Get customized quotes and tiered pricing for large volume orders.</small>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-check"><i class="bi bi-check-lg"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Technical Consultation</h6>
                        <small class="text-muted">Ensure product compatibility with your existing infrastructure.</small>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-check"><i class="bi bi-check-lg"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Project Management</h6>
                        <small class="text-muted">Phased deliveries and scheduled dispatch for long-term projects.</small>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-check"><i class="bi bi-check-lg"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">GST & Credit Support</h6>
                        <small class="text-muted">Seamless B2B billing and credit terms for verified partners.</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <div class="process-card">
                            <div class="process-icon-wrapper"><i class="bi bi-chat-right-text"></i></div>
                            <h5 class="fw-bold">1. Enable</h5>
                            <p class="small text-muted mb-0">Share your requirements via form, email, or a direct call with our engineers.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="process-card">
                            <div class="process-icon-wrapper"><i class="bi bi-search"></i></div>
                            <h5 class="fw-bold">2. Evaluate</h5>
                            <p class="small text-muted mb-0">We analyze technical specs and check availability across our global network.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="process-card">
                            <div class="process-icon-wrapper"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                            <h5 class="fw-bold">3. Estimate</h5>
                            <p class="small text-muted mb-0">Receive a detailed commercial proposal with best-in-class pricing.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="process-card">
                            <div class="process-icon-wrapper"><i class="bi bi-box-seam"></i></div>
                            <h5 class="fw-bold">4. Execute</h5>
                            <p class="small text-muted mb-0">On approval, we process your order with priority handling and tracking.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact & Form Section -->
<section id="inquiryForm" class="py-5" style="background-color: #f8f9fa;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold">Connect With Us</h2>
                <p class="text-muted">Choose how you would like to proceed.</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <!-- Contact Details Card -->
            <div class="col-lg-5" id="contactDetails">
                <div class="contact-card-premium p-4 h-100 bg-white">
                    <h4 class="fw-bold mb-4">Direct Contact</h4>
                    <p class="text-muted mb-4 small">Our sales desk is operational Mon-Fri, 9:30 AM to 6:30 PM IST.</p>
                    
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background: rgba(13,110,253,0.03);">
                        <div class="me-3 text-primary fs-3"><i class="bi bi-envelope-at"></i></div>
                        <div>
                            <div class="small fw-bold text-uppercase text-secondary">Email Support</div>
                            <a href="mailto:marcom.sbsyscon@gmail.com" class="fs-5 fw-bold text-dark text-decoration-none">marcom.sbsyscon@gmail.com</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background: rgba(25,135,84,0.03);">
                        <div class="me-3 text-success fs-3"><i class="bi bi-telephone"></i></div>
                        <div>
                            <div class="small fw-bold text-uppercase text-secondary">Phone Support</div>
                            <div class="fs-5 fw-bold">
                                <a href="tel:+919899598955" class="text-dark text-decoration-none">+91-9899598955</a>
                            </div>
                            <div class="small text-muted mt-1">+91-9899598900 (Alt)</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <span class="small">We typically respond to emails within 24 business hours.</span>
                    </div>
                </div>
            </div>

            <!-- Inquiry Form -->
            <div class="col-lg-7">
                <div class="contact-card-premium p-4 p-md-5 h-100 bg-white">
                    <h4 class="fw-bold mb-4">Submit a Requirement</h4>
                    <form action="contact-submit.php" method="POST">
                        <input type="hidden" name="source" value="assisted_order">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" class="form-control form-control-lg" name="user_name" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Company Name</label>
                                <input type="text" class="form-control form-control-lg" name="user_company" placeholder="ABC Corp">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" class="form-control form-control-lg" name="user_email" placeholder="john@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="tel" class="form-control form-control-lg" name="user_phone" placeholder="+91 98765 43210" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Requirement Details</label>
                                <textarea class="form-control form-control-lg" name="user_msg" rows="4" placeholder="Please describe your project, required products, or specific SKUs..." required></textarea>
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-dark btn-lg w-100 rounded-pill fw-bold">Submit Request <i class="bi bi-arrow-right list-inline-item ms-1"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-primary text-white position-relative overflow-hidden">
     <!-- Decorative circles -->
    <div style="position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; border-radius: 50%; background: rgba(255,255,255,0.1);"></div>
    <div style="position: absolute; bottom: -50px; right: -50px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.1);"></div>
    
    <div class="container text-center position-relative z-1">
        <h2 class="fw-bold mb-3">Ready to Streamline Your Procurement?</h2>
        <p class="lead text-white-50 mb-4">Join hundreds of businesses that trust SB Smart for their industrial supplies.</p>
        <a href="products.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary">Browse Catalog</a>
    </div>
    </div>
</section>

<!-- Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[action="contact-submit.php"]');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const errors = [];
            
            // Get fields
            const messageInput = form.querySelector('textarea[name="user_msg"]');
            const fieldsToCheck = [messageInput];
            
            // Also check other text inputs if they exist
            const inputs = form.querySelectorAll('input[type="text"]');
            inputs.forEach(i => fieldsToCheck.push(i));
            
            // Simple substring check to avoid regex WAF triggers
            const badStrings = ['http:', 'https:', 'www.', '.com', '.net', '.org', '.in', '.co.in', '.xyz'];
            
            fieldsToCheck.forEach(function(field) {
                if(field && field.value) {
                    const val = field.value.toLowerCase();
                    let hasLink = false;
                    for(let i=0; i<badStrings.length; i++) {
                        if(val.includes(badStrings[i])) {
                            hasLink = true;
                            break;
                        }
                    }

                    if (hasLink) {
                       isValid = false;
                       field.style.borderColor = '#dc3545';
                    } else {
                       field.style.borderColor = ''; // reset
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Security: Links/URLs are not allowed in the message or input fields.');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
