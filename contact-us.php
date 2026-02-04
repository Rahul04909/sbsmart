<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/config.php';

$page_title = "Contact Us - SBSmart";
require __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        flash_set('error', 'Please fill required fields (name, email, message).');
        header('Location: /contact-us.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Invalid email.');
        header('Location: /contact-us.php');
        exit;
    }

    $cfg = require __DIR__ . '/includes/config.php';
    $to = $cfg['site']['contact_email'] ?? ($cfg['mail']['from_email'] ?? 'noreply@sbsmart.in');
    $subject = "Contact form: " . $name;
    $html = "<p><strong>Name:</strong> " . esc($name) . "</p>"
          . "<p><strong>Email:</strong> " . esc($email) . "</p>"
          . "<p><strong>Phone:</strong> " . esc($phone) . "</p>"
          . "<p><strong>Message:</strong><br>" . nl2br(esc($message)) . "</p>";

    $mailer = new Mailer();
    $ok = $mailer->send($to, $subject, $html, strip_tags($html));

    if ($ok) {
        flash_set('success', 'Thanks — we received your message.');
    } else {
        flash_set('error', 'Failed to send message. Please try again later.');
    }
    header('Location: /contact-us.php');
    exit;
}
?>
<div class="container py-5">
  <h1>Contact Us</h1>
  <?php if ($m = flash_get('error')): ?><div class="alert alert-danger"><?= esc($m) ?></div><?php endif; ?>
  <?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= esc($m) ?></div><?php endif; ?>

  <form method="post" action="/contact-us.php" id="contactUsForm">
    <div class="mb-3">
      <label class="form-label">Name *</label>
      <input name="name" id="contactName" class="form-control" required minlength="3" maxlength="100" pattern="[A-Za-z\s.]+" title="Name should only contain letters and spaces">
    </div>
    <div class="mb-3">
      <label class="form-label">Email *</label>
      <input type="email" name="email" id="contactEmail" class="form-control" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
    </div>
    <div class="mb-3">
      <label class="form-label">Phone</label>
      <input name="phone" id="contactPhone" type="tel" class="form-control" pattern="^(\+91[\s]?)?[6-9]\d{9}$" title="Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9">
    </div>
    <div class="mb-3">
      <label class="form-label">Message *</label>
      <textarea name="message" id="contactMessage" class="form-control" rows="6" required minlength="10" maxlength="1000" title="Message must be between 10 and 1000 characters"></textarea>
      <small class="text-muted" id="contactCharCount" style="font-size: 0.75rem; margin-top: 4px; display: block;">0 / 1000 characters</small>
    </div>
    <button class="btn btn-primary" type="submit">Send</button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('contactUsForm');
  if (!form) return;
  
  const nameInput = form.querySelector('#contactName');
  const emailInput = form.querySelector('#contactEmail');
  const phoneInput = form.querySelector('#contactPhone');
  const messageInput = form.querySelector('#contactMessage');
  const charCount = document.getElementById('contactCharCount');
  
  // Name validation - only letters and spaces
  if (nameInput) {
    nameInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^A-Za-z\s.]/g, '');
    });
  }
  
  // Phone validation - format as typing
  if (phoneInput) {
    phoneInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^\d+\s()-]/g, '');
    });
  }
  
  // Email validation on blur
  if (emailInput) {
    emailInput.addEventListener('blur', function() {
      const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
      if (!emailPattern.test(this.value.trim()) && this.value.trim() !== '') {
        this.style.borderColor = '#dc3545';
        showError(this, 'Please enter a valid email address');
      } else {
        this.style.borderColor = '';
        removeError(this);
      }
    });
    
    emailInput.addEventListener('focus', function() {
      this.style.borderColor = '';
      removeError(this);
    });
  }
  
  // Character counter for message
  if (messageInput && charCount) {
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
  }
  
  // Form submission validation
  form.addEventListener('submit', function(e) {
    let isValid = true;
    const errors = [];
    
    // Validate name
    if (nameInput && nameInput.value.trim().length < 3) {
      isValid = false;
      errors.push('Name must be at least 3 characters long');
      nameInput.style.borderColor = '#dc3545';
    }
    
    // Validate email
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
    if (emailInput && !emailPattern.test(emailInput.value.trim())) {
      isValid = false;
      errors.push('Please enter a valid email address');
      emailInput.style.borderColor = '#dc3545';
    }
    
    
    // Validate phone if filled - Enhanced Indian mobile validation
    if (phoneInput && phoneInput.value.trim() !== '') {
      const phone = phoneInput.value.trim();
      const digitsOnly = phone.replace(/\D/g, '');
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
    
    
    // Validate message
    if (messageInput && messageInput.value.trim().length < 10) {
      isValid = false;
      errors.push('Message must be at least 10 characters long');
      messageInput.style.borderColor = '#dc3545';
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
