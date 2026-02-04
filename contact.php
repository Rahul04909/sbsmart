<?php
declare(strict_types=1);

// contact.php - Contact Us page

require_once __DIR__ . '/includes/helpers.php';

// Page meta
$page_title = "Contact Us — SBSmart";
$meta_description = "Get in touch with S.B. Syscon Pvt. Ltd. Visit us in Faridabad or contact us via phone, email, or fax.";

// Header
require_once __DIR__ . '/includes/header.php';
?>

<style>
.contact-hero {
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  color: #000;
  padding: 1.5rem 0;
  margin-bottom: 1rem;
}

.contact-hero h1 {
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
}

.contact-hero p {
  font-size: 0.9rem;
}

.contact-form {
  background: white;
  padding: 1.25rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: 100%;
}

.form-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 0.25rem;
}

.form-subtitle {
  color: #666;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.contact-form .form-label {
  font-weight: 500;
  color: #333;
  margin-bottom: 0.35rem;
  font-size: 0.85rem;
}

.contact-form .form-control {
  border-radius: 4px;
  border: 1px solid #ddd;
  padding: 0.5rem 0.75rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  font-size: 0.9rem;
}

.contact-form .form-control:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.15);
}

.contact-form textarea.form-control {
  min-height: 80px;
  resize: vertical;
}

.contact-form .mb-3 {
  margin-bottom: 0.75rem !important;
}

.btn-submit {
  background: #dc3545;
  border: none;
  color: white;
  padding: 0.6rem 1.5rem;
  border-radius: 4px;
  font-weight: 600;
  transition: background 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.85rem;
}

.btn-submit:hover {
  background: #c82333;
  color: white;
}

/* Right Side - Company Info */
.company-info-box {
  background: white;
  padding: 1.25rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: 100%;
}

.company-name {
  font-size: 1.15rem;
  font-weight: 700;
  color: #333;
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 0.75rem;
  margin-bottom: 1rem;
}

.company-name i {
  color: var(--primary);
  font-size: 1rem;
}

.info-list {
  margin-bottom: 0.75rem;
}

.info-item {
  display: flex;
  align-items: start;
  margin-bottom: 0.65rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item i {
  font-size: 1rem;
  color: #333;
  margin-right: 0.75rem;
  margin-top: 0.1rem;
  min-width: 16px;
}

.info-item span,
.info-item a {
  color: #666;
  font-size: 0.85rem;
  line-height: 1.4;
}

.info-item a {
  color: var(--primary);
  text-decoration: none;
  transition: color 0.3s ease;
}

.info-item a:hover {
  color: var(--secondary);
  text-decoration: underline;
}

/* Map Container */
.map-container-small {
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: 200px;
}

.map-container-small iframe {
  width: 100%;
  height: 100%;
  border: 0;
}
</style>

<!-- Hero Section -->
<div class="contact-hero">
  <div class="container">
    <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
    <p class="lead mb-0">We'd love to hear from you. Get in touch with us today!</p>
  </div>
</div>

<!-- Main Content -->
<div class="container pb-3">
  <?php
  // Display success/error popup
  if (isset($_SESSION['contact_success'])) {
      $msg = addslashes($_SESSION['contact_success']);
      echo "<script>alert('$msg');</script>";
      unset($_SESSION['contact_success']);
  }
  
  if (isset($_SESSION['contact_error'])) {
      $msg = addslashes($_SESSION['contact_error']);
      echo "<script>alert('$msg');</script>";
      unset($_SESSION['contact_error']);
  }
  ?>
  
  

  <div class="row g-4 mb-3">
    <!-- Left Side - Contact Form -->
    <div class="col-lg-6">
      <div class="contact-form">
        <h2 class="form-title mb-1">Write to Us</h2>
        <p class="form-subtitle mb-4">We value your inquiries and feedback. Please fill out the form below.</p>
        
        <form method="post" action="contact-submit.php" id="contactForm">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name *</label>
            <input type="text" class="form-control" id="user_name" name="user_name" required minlength="3" maxlength="100" pattern="[A-Za-z\s.]+" title="Name should only contain letters and spaces">
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number *</label>
            <input type="tel" class="form-control" id="user_phone" name="user_phone" required pattern="^(\+91[\s]?)?[6-9]\d{9}$" title="Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9">
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email" class="form-control" id="user_email" name="user_email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
          </div>

          <div class="mb-3">
            <label for="subject" class="form-label">Subject *</label>
             <input type="text" class="form-control" id="user_subject" name="user_subject" required minlength="5" maxlength="200" title="Subject must be between 5 and 200 characters">
          </div>

          <div class="mb-3">
            <label for="message" class="form-label">Your Message *</label>
            <textarea class="form-control" id="user_msg" name="user_msg" rows="5" required minlength="10" maxlength="1000" title="Message must be between 10 and 1000 characters"></textarea>
            <small class="text-muted" id="charCount" style="font-size: 0.75rem; margin-top: 4px; display: block;">0 / 1000 characters</small>
          </div>

          <button type="submit" class="btn btn-submit w-100">
            Submit
          </button>
        </form>
      </div>
    </div>

    <!-- Right Side - Company Info & Map -->
    <div class="col-lg-6">
      <div class="company-info-box">
        <h3 class="company-name mb-4">
          <i class="bi bi-building me-2"></i>S.B. Syscon Pvt. Ltd.
        </h3>
        
        <div class="info-list">
          <div class="info-item">
            <i class="bi bi-geo-alt-fill"></i>
            <span>1D-45A, NIT Faridabad, Haryana, India-121001</span>
          </div>

          <div class="info-item">
            <i class="bi bi-envelope-fill"></i>
            <a href="mailto:marcom.sbsyscon@gmail.com">marcom.sbsyscon@gmail.com</a>
          </div>

          <div class="info-item">
            <i class="bi bi-telephone-fill"></i>
            <a href="tel:+911294150555">+91 129 4150555</a>
          </div>

          <div class="info-item">
            <i class="bi bi-phone-fill"></i>
            <span>
              <a href="tel:+919899598900">+91 9899598900</a>, 
              <a href="tel:+919899598955">+91 9899598955</a>
            </span>
          </div>

          <div class="info-item">
            <i class="bi bi-printer-fill"></i>
            <span>+91 129 4162010</span>
          </div>
        </div>

        <!-- Embedded Map -->
        <div class="map-container-small mt-4">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3508.378649!2d77.29699!3d28.378649!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390cdc3e11e65d63%3A0x955ce753c934f573!2sS%20B%20SYSCON%20PVT%20LTD!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin"
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const contactForm = document.getElementById('contactForm');
  if (!contactForm) return;
  
  const nameInput = contactForm.querySelector('#user_name');
  const phoneInput = contactForm.querySelector('#user_phone');
  const emailInput = contactForm.querySelector('#user_email');
  const subjectInput = contactForm.querySelector('#user_subject');
  const messageInput = contactForm.querySelector('#user_msg');
  const charCount = document.getElementById('charCount');
  
  // Name validation - only letters and spaces
  nameInput.addEventListener('input', function() {
    this.value = this.value.replace(/[^A-Za-z\s.]/g, '');
  });
  
  // Phone validation - format as typing
  phoneInput.addEventListener('input', function() {
    this.value = this.value.replace(/[^\d+\s()-]/g, '');
  });
  
  // Email validation on blur
  emailInput.addEventListener('blur', function() {
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
    if (!emailPattern.test(this.value.trim()) && this.value.trim() !== '') {
      this.style.borderColor = '#dc3545';
      showError(this, 'Please enter a valid email address');
    } else {
      this.style.borderColor = '#ddd';
      removeError(this);
    }
  });
  
  // Character counter for message
  messageInput.addEventListener('input', function() {
    const length = this.value.length;
    const maxLength = 1000;
    charCount.textContent = `${length} / ${maxLength} characters`;
    
    if (length > maxLength) {
      charCount.style.color = '#dc3545';
    } else if (length >= maxLength * 0.9) {
      charCount.style.color = '#f59e0b';
    } else {
      charCount.style.color = '#6b7280';
    }
  });
  
  // Form submission validation
  contactForm.addEventListener('submit', function(e) {
    let isValid = true;
    const errors = [];
    
    // Validate name
    if (nameInput.value.trim().length < 3) {
      isValid = false;
      errors.push('Name must be at least 3 characters long');
      nameInput.style.borderColor = '#dc3545';
    }
    
    
    // Validate phone - Enhanced Indian mobile validation
    const phone = phoneInput.value.trim();
    const digitsOnly = phone.replace(/\D/g, '');
    
    if (digitsOnly.length > 0) {
      let phoneValid = false;
      
      if (digitsOnly.length === 10) {
        // Indian mobile: must start with 6, 7, 8, or 9
        phoneValid = /^[6-9]\d{9}$/.test(digitsOnly);
        if (!phoneValid) {
          errors.push('Mobile number must start with 6, 7, 8, or 9');
        }
      } else if (digitsOnly.length === 12 && digitsOnly.startsWith('91')) {
        // With country code +91
        const mobileNumber = digitsOnly.substring(2);
        phoneValid = /^[6-9]\d{9}$/.test(mobileNumber);
        if (!phoneValid) {
          errors.push('Mobile number must start with 6, 7, 8, or 9');
        }
      } else if (digitsOnly.length === 11 && digitsOnly.startsWith('0')) {
        // With leading 0
        const mobileNumber = digitsOnly.substring(1);
        phoneValid = /^[6-9]\d{9}$/.test(mobileNumber);
        if (!phoneValid) {
          errors.push('Mobile number must start with 6, 7, 8, or 9');
        }
      } else {
        errors.push('Mobile number must be 10 digits');
        phoneValid = false;
      }
      
      if (!phoneValid) {
        isValid = false;
        phoneInput.style.borderColor = '#dc3545';
      }
    }
    
    
    // Validate email
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
    if (!emailPattern.test(emailInput.value.trim())) {
      isValid = false;
      errors.push('Please enter a valid email address');
      emailInput.style.borderColor = '#dc3545';
    }
    
    // Validate subject
    if (subjectInput.value.trim().length < 5) {
      isValid = false;
      errors.push('Subject must be at least 5 characters long');
      subjectInput.style.borderColor = '#dc3545';
    }
    
    // Validate message
    if (messageInput.value.trim().length < 10) {
      isValid = false;
      errors.push('Message must be at least 10 characters long');
      messageInput.style.borderColor = '#dc3545';
    }

    // Security: Check for links in message or subject
    // Simple substring check to avoid regex WAF triggers
    const badStrings = ['http:', 'https:', 'www.', '.com', '.net', '.org', '.in', '.co.in', '.xyz'];
    const sVal = subjectInput.value.toLowerCase();
    const mVal = messageInput.value.toLowerCase();
    
    let hasLink = false;
    for(let i=0; i<badStrings.length; i++) {
        if(sVal.includes(badStrings[i]) || mVal.includes(badStrings[i])) {
            hasLink = true;
            break;
        }
    }

    if (hasLink) {
        isValid = false;
        errors.push('Links/URLs are not allowed in the message or subject.');
        messageInput.style.borderColor = '#dc3545';
        subjectInput.style.borderColor = '#dc3545';
    }
    
    if (!isValid) {
      e.preventDefault();
      alert('Please fix the following errors:\n\n' + errors.join('\n'));
      return false;
    }
  });
  
  // Helper functions
  function showError(input, message) {
    removeError(input);
    const errorDiv = document.createElement('small');
    errorDiv.className = 'error-message';
    errorDiv.style.cssText = 'color: #dc3545; font-size: 0.75rem; margin-top: 4px; display: block;';
    errorDiv.textContent = message;
    input.parentElement.appendChild(errorDiv);
  }
  
  function removeError(input) {
    const error = input.parentElement.querySelector('.error-message');
    if (error) error.remove();
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
