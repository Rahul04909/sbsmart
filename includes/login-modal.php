<?php
// includes/login-modal.php
// Premium split-screen login modal with background image
?>

<style>
:root {
  --login-primary: #0B4FD6;
  --login-primary-hover: #0941b3;
}

.auth-modal-content {
  border-radius: 0 !important;
  overflow: hidden;
  max-width: 900px;
  width: 100%;
  background: transparent;
  border: none !important;
}

.auth-modal-dialog {
  max-width: 900px;
  margin: 1.75rem auto;
  display: flex;
  align-items: center;
  min-height: calc(100vh - 3.5rem);
}

.auth-split-container {
  display: flex;
  min-height: 550px;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.auth-split-left {
  flex: 1;
  background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
  position: relative;
  overflow: hidden;
  background-image: url('data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"%3e%3crect fill="%231e3a8a" width="1200" height="800"/%3e%3cg opacity="0.3"%3e%3ccircle cx="200" cy="200" r="200" fill="%233b82f6"/%3e%3ccircle cx="1000" cy="600" r="300" fill="%232563eb"/%3e%3c/g%3e%3c/svg%3e');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end;
  padding: 40px;
  min-height: 550px;
}

.auth-split-left::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
}

.auth-branding {
  position: relative;
  z-index: 2;
  color: white;
}

.auth-brand-name {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
  letter-spacing: -0.5px;
}

.auth-split-right {
  flex: 1;
  padding: 35px 35px;
  background: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow-y: auto;
  max-height: 90vh;
}

.auth-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 25px;
  letter-spacing: -0.5px;
}

.auth-form-group {
  margin-bottom: 12px;
}

.auth-form-label {
  font-size: 0.72rem;
  color: #6b7280;
  margin-bottom: 6px;
  display: block;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.auth-input-wrapper {
  position: relative;
}

.auth-input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 1.1rem;
  z-index: 2;
}

.auth-input {
  width: 100%;
  padding: 12px 14px 12px 42px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.92rem;
  transition: all 0.2s;
  background: #f9fafb;
}

.auth-input:focus {
  outline: none;
  border-color: var(--login-primary);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(11, 79, 214, 0.1);
}

.auth-input::placeholder {
  color: #d1d5db;
}

.auth-password-wrapper {
  position: relative;
}

.auth-password-toggle {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  z-index: 3;
  transition: color 0.2s;
}

.auth-password-toggle:hover {
  color: #6b7280;
}

.auth-checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.auth-checkbox {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.auth-checkbox-label {
  font-size: 0.875rem;
  color: #6b7280;
  cursor: pointer;
  margin: 0;
}

.auth-forgot-link {
  color: #6b7280;
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s;
}

.auth-forgot-link:hover {
  color: var(--login-primary);
}

.auth-submit-btn {
  width: 100%;
  padding: 12px;
  background: var(--login-primary);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.auth-submit-btn:hover {
  background: var(--login-primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(11, 79, 214, 0.3);
}

.auth-create-account {
  text-align: center;
  margin-top: 18px;
}

.auth-create-link {
  color: var(--login-primary);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.95rem;
  transition: color 0.2s;
}

.auth-create-link:hover {
  color: var(--login-primary-hover);
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
  .auth-split-container {
    flex-direction: column;
    min-height: auto;
  }
  
  .auth-split-left {
    min-height: 180px;
    padding: 30px;
  }
  
  .auth-brand-name {
    font-size: 1.5rem;
  }
  
  .auth-split-right {
    padding: 35px 25px;
  }
  
  .auth-title {
    font-size: 1.4rem;
  }
  
  .auth-modal-dialog {
    margin: 0.5rem;
  }
}

/* Tab Styling */
.auth-tabs {
  display: flex;
  gap: 0;
  margin-bottom: 30px;
  border-bottom: 2px solid #e5e7eb;
}

.auth-tab {
  flex: 1;
  padding: 12px 20px;
  background: transparent;
  border: none;
  color: #6b7280;
  font-weight: 600;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
}

.auth-tab:hover {
  color: var(--login-primary);
}

.auth-tab.active {
  color: var(--login-primary);
  border-bottom-color: var(--login-primary);
}

.auth-tab-content {
  display: none;
}

.auth-tab-content.active {
  display: block;
}

/* Close button positioning for split layout */
.auth-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 1000;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.auth-close-btn:hover {
  background: #fff;
  transform: scale(1.1);
}

.auth-close-btn i {
  font-size: 1.2rem;
  color: #1a1a1a;
}
</style>

<!-- Login/Signup Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl auth-modal-dialog modal-dialog-centered">
    <div class="modal-content auth-modal-content border-0">
      
      <!-- Close Button -->
      <button type="button" class="auth-close-btn" data-bs-dismiss="modal" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
      
      <div class="auth-split-container">
        
        <!-- Left Side: Background Image -->
        <div class="auth-split-left" style="background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5)), url('assets/images/login-bg.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
          <div class="auth-branding">
            <h2 class="auth-brand-name">SBSmart</h2>
            <p class="mb-0" style="font-size: 0.95rem; opacity: 0.9;">Industrial & Electrical Solutions</p>
          </div>
        </div>
        
        <!-- Right Side: Login Form -->
        <div class="auth-split-right">
          
          <!-- Tabs -->
          <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login">Login</button>
            <button class="auth-tab" data-tab="register">Sign Up</button>
          </div>
          
          <!-- LOGIN TAB -->
          <div id="login-tab-content" class="auth-tab-content active">
            <h2 class="auth-title">LOGIN</h2>
            
            <form action="auth/login.php" method="POST">
              <?php 
                $current_uri = $_SERVER['REQUEST_URI'] ?? '';
                if (strpos($current_uri, 'login.php') !== false || strpos($current_uri, 'register.php') !== false) {
                    $current_uri = 'index.php';
                }
              ?>
              <input type="hidden" name="redirect" value="<?= htmlspecialchars($current_uri) ?>">
              
              <div class="auth-form-group">
                <label class="auth-form-label">User Name</label>
                <div class="auth-input-wrapper">
                  <i class="bi bi-person-fill auth-input-icon"></i>
                  <input type="email" name="email" id="loginEmail" class="auth-input" placeholder="User Name" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
                </div>
              </div>
              
              <div class="auth-form-group">
                <label class="auth-form-label">Password</label>
                <div class="auth-input-wrapper auth-password-wrapper">
                  <i class="bi bi-lock-fill auth-input-icon"></i>
                  <input type="password" name="password" id="loginPassword" class="auth-input" placeholder="Password" required minlength="6" title="Password must be at least 6 characters">
                  <button type="button" class="auth-password-toggle" onclick="togglePassword('loginPassword', this)">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                </div>
              </div>
              
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="auth-checkbox-wrapper">
                  <input type="checkbox" name="remember" id="rememberEmail" class="auth-checkbox">
                  <label for="rememberEmail" class="auth-checkbox-label">Remember Email</label>
                </div>
                <a href="forgot-password.php" class="auth-forgot-link">Forgot Password ?</a>
              </div>
              
              <button type="submit" class="auth-submit-btn">Log In</button>
              
              <div class="auth-create-account">
                <a href="#" class="auth-create-link" onclick="switchTab('register'); return false;">Create My Account</a>
              </div>
            </form>
          </div>
          
          <!-- REGISTER TAB -->
          <div id="register-tab-content" class="auth-tab-content">
            <h2 class="auth-title">SIGN UP</h2>
            
            <form action="auth/register.php" method="POST">
              <input type="text" name="fax" style="display:none;" autocomplete="off">
              
              <!-- Row 1: Name and Email -->
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <div class="auth-form-group">
                    <label class="auth-form-label">Full Name</label>
                    <div class="auth-input-wrapper">
                      <i class="bi bi-person-fill auth-input-icon"></i>
                      <input type="text" name="name" id="registerName" class="auth-input" placeholder="John Doe" required minlength="3" maxlength="100" pattern="[A-Za-z\s.]+" title="Name should only contain letters and spaces">
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="auth-form-group">
                    <label class="auth-form-label">Email Address</label>
                    <div class="auth-input-wrapper">
                      <i class="bi bi-envelope-fill auth-input-icon"></i>
                      <input type="email" name="email" id="registerEmail" class="auth-input" placeholder="name@example.com" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Row 2: Phone and Password -->
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <div class="auth-form-group">
                    <label class="auth-form-label">Phone Number</label>
                    <div class="auth-input-wrapper">
                      <i class="bi bi-telephone-fill auth-input-icon"></i>
                      <input type="tel" name="phone" id="registerPhone" class="auth-input" placeholder="+91 9876543210" pattern="^(\+91[\s])?[6-9]\d{9}$" title="Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9">
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="auth-form-group">
                    <label class="auth-form-label">Password</label>
                    <div class="auth-input-wrapper auth-password-wrapper">
                      <i class="bi bi-lock-fill auth-input-icon"></i>
                      <input type="password" name="password" id="registerPassword" class="auth-input" placeholder="Create a password" required minlength="6" maxlength="50" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$" title="Password must contain at least one uppercase letter, one lowercase letter, and one number">
                      <button type="button" class="auth-password-toggle" onclick="togglePassword('registerPassword', this)">
                        <i class="bi bi-eye-fill"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Password Strength Indicator -->
              <div class="mb-2">
                <small class="text-muted" style="font-size: 0.75rem; margin-top: 4px; display: block;">Must contain uppercase, lowercase, and number (min 6 chars)</small>
                <div id="passwordStrength" style="margin-top: 8px; display: none;">
                  <div style="display: flex; gap: 4px; margin-bottom: 4px;">
                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                    <div class="strength-bar" style="flex: 1; height: 4px; background: #e5e7eb; border-radius: 2px;"></div>
                  </div>
                  <small class="strength-text" style="font-size: 0.7rem; color: #6b7280;">Password strength</small>
                </div>
              </div>
              
              <button type="submit" class="auth-submit-btn">Sign Up</button>
              
              <div class="text-center mt-3">
                <small class="text-muted" style="font-size: 0.75rem;">
                  By signing up, you agree to our <a href="terms-and-conditions.php" class="text-decoration-none">Terms</a> & <a href="privacy-policy.php" class="text-decoration-none">Privacy Policy</a>.
                </small>
              </div>
            </form>
          </div>
          
        </div>
        
      </div>
    </div>
  </div>
</div>

<script>
// Tab switching
document.addEventListener('DOMContentLoaded', function() {
  const tabButtons = document.querySelectorAll('.auth-tab');
  
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      const tabName = this.getAttribute('data-tab');
      switchTab(tabName);
    });
  });
  
  // Add real-time validation
  setupFormValidations();
});

function switchTab(tabName) {
  // Remove active class from all tabs and contents
  document.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
  document.querySelectorAll('.auth-tab-content').forEach(content => content.classList.remove('active'));
  
  // Add active class to selected tab and content
  document.querySelector(`.auth-tab[data-tab="${tabName}"]`).classList.add('active');
  document.getElementById(`${tabName}-tab-content`).classList.add('active');
  
  // Change background image based on tab
  const leftPanel = document.querySelector('.auth-split-left');
  if (leftPanel) {
    if (tabName === 'login') {
      // Login tab - use login-bg (3).png
      leftPanel.style.backgroundImage = "linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5)), url('assets/images/login-bg.jpg')";
    } else if (tabName === 'register') {
      // Sign Up tab - use login-bg.png
      leftPanel.style.backgroundImage = "linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5)), url('assets/images/login-bg.jpg')";
    }
  }
}

// Password toggle
function togglePassword(inputId, button) {
  const input = document.getElementById(inputId);
  const icon = button.querySelector('i');
  
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('bi-eye-fill');
    icon.classList.add('bi-eye-slash-fill');
  } else {
    input.type = 'password';
    icon.classList.remove('bi-eye-slash-fill');
    icon.classList.add('bi-eye-fill');
  }
}

// Form Validations
function setupFormValidations() {
  // Email validation
  const emailInputs = document.querySelectorAll('input[type="email"]');
  emailInputs.forEach(input => {
    if (input) {
      input.addEventListener('blur', function() {
        validateEmail(this);
      });
    }
  });

  // Phone validation
  const phoneInput = document.getElementById('registerPhone');
  if (phoneInput) {
    phoneInput.addEventListener('input', function() {
      formatPhoneNumber(this);
    });
    phoneInput.addEventListener('blur', function() {
      validatePhone(this);
    });
  }

  // Password strength meter
  const registerPassword = document.getElementById('registerPassword');
  const strengthMeter = document.getElementById('passwordStrength');
  if (registerPassword && strengthMeter) {
    registerPassword.addEventListener('input', function() {
      const password = this.value;
      if (password.length > 0) {
        strengthMeter.style.display = 'block';
        updatePasswordStrength(password);
      } else {
        strengthMeter.style.display = 'none';
      }
    });
  }

  // Name validation
  const nameInput = document.getElementById('registerName');
  if (nameInput) {
    nameInput.addEventListener('input', function() {
      // Remove numbers and special characters
      this.value = this.value.replace(/[^A-Za-z\s.]/g, '');
    });
  }
}

// Email validation function
function validateEmail(input) {
  const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
  const isValid = emailPattern.test(input.value.trim());
  
  if (!isValid && input.value.trim() !== '') {
    input.style.borderColor = '#dc3545';
    showValidationMessage(input, 'Please enter a valid email address', 'error');
  } else {
    input.style.borderColor = '#e5e7eb';
    removeValidationMessage(input);
  }
  
  return isValid;
}

// Phone validation and formatting
function formatPhoneNumber(input) {
  // Remove all non-numeric characters except + and spaces
  let value = input.value.replace(/[^\d+\s()-]/g, '');
  input.value = value;
}

function validatePhone(input) {
  const phone = input.value.trim();
  
  // If empty, it's valid (optional field)
  if (phone === '') {
    input.style.borderColor = '#e5e7eb';
    removeValidationMessage(input);
    return true;
  }
  
  // Remove all non-digit characters for validation
  const digitsOnly = phone.replace(/\D/g, '');
  
  let isValid = false;
  let errorMessage = 'Please enter a valid phone number';
  
  // Check for Indian mobile number format
  if (digitsOnly.length === 10) {
    // Indian mobile: must start with 6, 7, 8, or 9
    if (/^[6-9]\d{9}$/.test(digitsOnly)) {
      isValid = true;
    } else {
      errorMessage = 'Mobile number must start with 6, 7, 8, or 9';
    }
  } else if (digitsOnly.length === 12 && digitsOnly.startsWith('91')) {
    // With country code +91
    const mobileNumber = digitsOnly.substring(2);
    if (/^[6-9]\d{9}$/.test(mobileNumber)) {
      isValid = true;
    } else {
      errorMessage = 'Mobile number must start with 6, 7, 8, or 9';
    }
  } else if (digitsOnly.length === 11 && digitsOnly.startsWith('0')) {
    // With leading 0
    const mobileNumber = digitsOnly.substring(1);
    if (/^[6-9]\d{9}$/.test(mobileNumber)) {
      isValid = true;
    } else {
      errorMessage = 'Mobile number must start with 6, 7, 8, or 9';
    }
  } else {
    errorMessage = 'Mobile number must be 10 digits';
  }
  
  if (!isValid) {
    input.style.borderColor = '#dc3545';
    showValidationMessage(input, errorMessage, 'error');
  } else {
    input.style.borderColor = '#e5e7eb';
    removeValidationMessage(input);
  }
  
  return isValid;
}

// Password strength meter
function updatePasswordStrength(password) {
  const strengthBars = document.querySelectorAll('.strength-bar');
  const strengthText = document.querySelector('.strength-text');
  
  let strength = 0;
  let strengthLevel = 'Weak';
  let color = '#ef4444'; // red
  
  // Check password criteria
  if (password.length >= 6) strength++;
  if (password.length >= 10) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^A-Za-z0-9]/.test(password)) strength++;
  
  // Determine strength level
  if (strength <= 2) {
    strengthLevel = 'Weak';
    color = '#ef4444';
  } else if (strength <= 3) {
    strengthLevel = 'Medium';
    color = '#f59e0b';
  } else {
    strengthLevel = 'Strong';
    color = '#10b981';
  }
  
  // Update bars
  strengthBars.forEach((bar, index) => {
    if (index < Math.min(strength, 3)) {
      bar.style.background = color;
    } else {
      bar.style.background = '#e5e7eb';
    }
  });
  
  // Update text
  strengthText.textContent = `Password strength: ${strengthLevel}`;
  strengthText.style.color = color;
}

// Show validation message
function showValidationMessage(input, message, type) {
  removeValidationMessage(input);

  if (!input || !input.parentElement) {
    console.error('Cannot show validation message: input or parent element not found');
    return;
  }

  const msgDiv = document.createElement('small');
  msgDiv.className = 'validation-message';
  msgDiv.style.cssText = 'color: #dc3545; font-size: 0.75rem; margin-top: 4px; display: block;';
  msgDiv.textContent = message;

  input.parentElement.appendChild(msgDiv);
}

// Remove validation message
function removeValidationMessage(input) {
  if (!input || !input.parentElement) {
    return;
  }

  const existingMsg = input.parentElement.querySelector('.validation-message');
  if (existingMsg) {
    existingMsg.remove();
  }
}

// Form submission validation
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    let isValid = true;
    
    // Check all required fields
    const requiredInputs = this.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
      if (!input.value.trim()) {
        input.style.borderColor = '#dc3545';
        isValid = false;
      }
    });
    
    // Validate email fields
    const emailFields = this.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
      if (!validateEmail(field)) {
        isValid = false;
      }
    });
    
    // Validate phone field if present and filled
    const phoneField = this.querySelector('input[type="tel"]');
    if (phoneField && phoneField.value.trim() !== '') {
      if (!validatePhone(phoneField)) {
        isValid = false;
      }
    }
    
    if (!isValid) {
      e.preventDefault();
      alert('Please fill in all required fields correctly.');
    }
  });
});
</script>
