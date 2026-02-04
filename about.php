<?php
declare(strict_types=1);

// about.php - Modern About Us Page (Bhardwaj Road Carrier)
$page_title = "About Us — Bhardwaj Road Carrier";
$meta_description = "Bhardwaj Road Carrier (BRC) - Pan-India Full Truck Load Logistics Partner. Dedicated FTL movements, door-to-door delivery, and express services.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Custom CSS for About Page -->
<style>
    :root {
        --brand-blue: #0d6efd;
        --brand-dark: #0a2647;
        --brand-light: #f8f9fa;
        --text-muted: #6c757d;
    }

    /* Hero Section */
    .about-hero {
        position: relative;
        padding: 8rem 0 6rem;
        background: radial-gradient(circle at top right, #0a2647 0%, #000000 100%);
        color: white;
        overflow: hidden;
    }
    .about-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: url('assets/images/pattern-grid.png') repeat; /* Fallback */
        opacity: 0.05;
        pointer-events: none;
    }
    .hero-badge {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-size: 0.9rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 1.5rem;
    }

    /* Content Sections */
    .section-padding { padding: 6rem 0; }
    .bg-light-alt { background-color: #fcfcfc; }
    
    .section-tagline {
        color: var(--brand-blue);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1.5px;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .content-card {
        background: white;
        padding: 2.5rem;
        border-radius: 16px;
        border: 1px solid #eee;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.05);
        border-color: var(--brand-blue);
    }
    .icon-box {
        width: 60px; height: 60px;
        background: rgba(13, 110, 253, 0.1);
        color: var(--brand-blue);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
    }

    /* List Styling */
    .check-list li {
        margin-bottom: 0.8rem;
        display: flex;
        align-items: start;
        font-size: 1.05rem;
        color: #495057;
    }
    .check-list i {
        color: var(--brand-blue);
        margin-right: 0.75rem;
        font-size: 1.2rem;
        margin-top: 2px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .section-padding { padding: 4rem 0; }
        .display-3 { font-size: 2.5rem; }
    }
</style>

<!-- Hero Section -->
<section class="about-hero text-center">
    <div class="container position-relative z-1">
        <span class="hero-badge animate__animated animate__fadeInDown">FTL TRANSPORTATION SERVICES</span>
        <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInUp">Bhardwaj Road Carrier</h1>
        <p class="lead text-white-50 mx-auto animate__animated animate__fadeInUp" style="max-width: 700px; animation-delay: 0.2s;">
            Pan-India Full Truck Load Logistics Partner
        </p>
    </div>
</section>

<!-- Who We Are -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <!-- Logistics Image -->
                 <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80&w=800" alt="Logistics & Warehousing" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 start-0 bg-white p-4 rounded-3 shadow m-4 d-none d-md-block" style="border-left: 5px solid var(--brand-blue);">
                        <div class="fw-bold fs-5 text-dark">Speed & Safety</div>
                        <small class="text-muted">Dedicated FTL Solutions</small>
                    </div>
                 </div>
            </div>
            <div class="col-lg-6">
                <span class="section-tagline">Who We Are</span>
                <h2 class="display-6 fw-bold mb-4 text-dark">Ethics Express Pvt. Ltd.</h2>
                <div class="lead text-dark mb-3">
                    A leading logistics company providing dedicated FTL transportation solutions across India.
                </div>
                <p class="text-secondary mb-4" style="line-height: 1.8; font-size: 1.1rem;">
                    We ensure speed, safety, and reliability for your cargo. With a pan-India network and a wide range of vehicle options, we are committed to being your trusted logistics partner.
                </p>
                
                <!-- Promise Box -->
                <div class="bg-light p-4 rounded-3 border border-secondary border-opacity-10">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-quote fs-1 text-primary opacity-25 me-3"></i>
                        <div>
                            <h5 class="fw-bold mb-2 text-dark">Our Promise</h5>
                            <p class="mb-0 text-muted fst-italic fs-5">"On-time delivery. Complete visibility. Trusted partnership."</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Offerings & Value Added Services -->
<section class="section-padding bg-light-alt">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                 <div class="d-flex align-items-center mb-4">
                     <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3"><i class="bi bi-truck fs-3"></i></span>
                     <h3 class="fw-bold mb-0">Our FTL Offerings</h3>
                 </div>
                 <ul class="list-unstyled check-list">
                    <li><i class="bi bi-check-circle-fill"></i> Dedicated Full Truck Load movements</li>
                    <li><i class="bi bi-check-circle-fill"></i> Door-to-door delivery across India</li>
                    <li><i class="bi bi-check-circle-fill"></i> Express & time-bound services</li>
                    <li><i class="bi bi-check-circle-fill"></i> Daily vehicle placement</li>
                    <li><i class="bi bi-check-circle-fill"></i> Real-time tracking & updates</li>
                    <li><i class="bi bi-check-circle-fill"></i> Single point of coordination</li>
                 </ul>
            </div>
            <div class="col-lg-6">
                 <div class="d-flex align-items-center mb-4">
                     <span class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3"><i class="bi bi-gear-wide-connected fs-3"></i></span>
                     <h3 class="fw-bold mb-0">Value-Added Services</h3>
                 </div>
                 <ul class="list-unstyled check-list">
                    <li><i class="bi bi-check-lg" style="color: var(--bs-teal);"></i> Route & load optimization</li>
                    <li><i class="bi bi-check-lg" style="color: var(--bs-teal);"></i> GPS tracking & DSR/MIS reporting</li>
                    <li><i class="bi bi-check-lg" style="color: var(--bs-teal);"></i> POD management</li>
                    <li><i class="bi bi-check-lg" style="color: var(--bs-teal);"></i> Insurance & documentation support</li>
                    <li><i class="bi bi-check-lg" style="color: var(--bs-teal);"></i> Emergency vehicle backup</li>
                 </ul>
            </div>
        </div>
    </div>
</section>

<!-- Fleet Strength -->
<section class="section-padding text-white" style="background: linear-gradient(180deg, #0a2647 0%, #081e39 100%);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-warning text-dark mb-3 px-3 py-2">LOGISTICS ASSETS</span>
            <h2 class="fw-bold display-5">Truck Fleet Strength</h2>
            <p class="opacity-75 lead">LCV / MCV / HCV Trucks Available</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                 <div class="p-4 border border-white border-opacity-10 rounded-4 h-100 bg-white bg-opacity-10 backdrop-blur">
                     <i class="bi bi-truck-flatbed fs-1 mb-3 text-warning"></i>
                     <h4 class="h4 fw-bold">Open & Closed Body</h4>
                     <p class="mb-0 opacity-75 fs-5">17 ft | 19 ft | 22 ft | 24 ft trucks</p>
                 </div>
            </div>
             <div class="col-md-4">
                 <div class="p-4 border border-white border-opacity-10 rounded-4 h-100 bg-white bg-opacity-10 backdrop-blur">
                     <i class="bi bi-box-seam fs-1 mb-3 text-warning"></i>
                     <h4 class="h4 fw-bold">Containers</h4>
                     <p class="mb-0 opacity-75 fs-5">20 ft & 40 ft Containers</p>
                 </div>
            </div>
             <div class="col-md-4">
                 <div class="p-4 border border-white border-opacity-10 rounded-4 h-100 bg-white bg-opacity-10 backdrop-blur">
                     <i class="bi bi-boxes fs-1 mb-3 text-warning"></i>
                     <h4 class="h4 fw-bold">Heavy Cargo</h4>
                     <p class="mb-0 opacity-75 fs-5">Trailers for ODC & heavy cargo</p>
                 </div>
            </div>
        </div>
    </div>
</section>

<!-- Industries Served -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
             <span class="section-tagline">Industries We Serve</span>
             <h2 class="fw-bold">Supporting Diverse Sectors</h2>
        </div>
        <div class="row g-3 justify-content-center">
            <?php 
            $industries = [
                ["name" => "Steel & Infrastructure", "icon" => "bi-building"],
                ["name" => "Electrical Equipment & Cables", "icon" => "bi-lightning-charge"],
                ["name" => "Solar & Renewable Energy", "icon" => "bi-sun"],
                ["name" => "Engineering & Capital Goods", "icon" => "bi-gear"],
                ["name" => "FMCG & Consumer Durables", "icon" => "bi-cart3"],
                ["name" => "Automobile & Auto Components", "icon" => "bi-car-front"]
            ];
            foreach($industries as $ind) {
                echo '<div class="col-md-4 col-sm-6">
                        <div class="p-4 border rounded-4 text-center fw-bold bg-white shadow-sm h-100 transition-all hover-lift">
                            <i class="bi '.$ind['icon'].' fs-2 text-primary d-block mb-3"></i>
                            '.$ind['name'].'
                        </div>
                      </div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Why BRC -->
<section class="section-padding bg-light-alt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tagline">Why Choose Us</span>
            <h2 class="fw-bold">Why BRC?</h2>
        </div>
        <div class="row g-4">
             <?php 
             $reasons = [
                 ["icon"=>"bi-globe", "title"=>"Pan-India network", "desc"=>"Comprehensive coverage across the nation."],
                 ["icon"=>"bi-speedometer2", "title"=>"Faster transit", "desc"=>"Dedicated vehicles for quicker delivery."],
                 ["icon"=>"bi-shield-check-fill", "title"=>"Safe & secure", "desc"=>"Reliable cargo handling and safety protocols."],
                 ["icon"=>"bi-currency-rupee", "title"=>"Competitive pricing", "desc"=>"Best rates in the market for FTL."],
                 ["icon"=>"bi-headset", "title"=>"24×7 operations support", "desc"=>"Round-the-clock operations support."],
                 ["icon"=>"bi-people-fill", "title"=>"Experienced Team", "desc"=>"Logistics professionals dedicated to you."]
             ];
             foreach($reasons as $r): ?>
             <div class="col-md-4">
                <div class="content-card text-center stats-card">
                    <div class="icon-box mx-auto"><i class="bi <?= $r['icon'] ?>"></i></div>
                    <h4 class="h5 fw-bold"><?= $r['title'] ?></h4>
                    <p class="text-muted small mb-0"><?= $r['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="fw-bold mb-3">Ready to Move Your Cargo?</h2>
        <p class="lead mb-4 opacity-75">Contact us for reliable FTL solutions today.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary">Contact Us</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
