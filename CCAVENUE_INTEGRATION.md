# CCAvenue Payment Gateway Integration Guide

## Overview
This document explains the CCAvenue payment gateway integration in your SBSmart e-commerce application.

## Files Involved

### 1. Core Integration Files
- **`includes/ccavenue-crypto.php`** - Encryption/decryption functions for CCAvenue
- **`ccavGenerate.php`** - Generates encrypted payment request and redirects to CCAvenue
- **`ccavResponseHandler.php`** - Handles payment response from CCAvenue
- **`ccavCancel.php`** - Handles payment cancellation
- **`order-status.php`** - Displays order status after payment

### 2. Configuration
- **`includes/config.php`** - Loads CCAvenue credentials from environment variables
- **`.env`** - Contains CCAvenue credentials (merchant_id, access_code, working_key)

### 3. Integration Points
- **`checkout.php`** - Main checkout page with payment method selection

## Payment Flow

### Step 1: Order Creation
1. User fills out shipping details on `checkout.php`
2. Form submits with `place_order=1`
3. Order is created in database with status `pending`
4. Order ID is stored in session (`$_SESSION['checkout_order_id']`)

### Step 2: Payment Method Selection
After order creation, user sees two options:
- **Pay Online with CCAvenue** - Redirects to CCAvenue payment gateway
- **Cash On Delivery (COD)** - Confirms order without online payment

### Step 3A: CCAvenue Payment Flow
1. User clicks "Pay Online with CCAvenue"
2. Form POSTs to `ccavGenerate.php` with order_id
3. `ccavGenerate.php`:
   - Fetches order details from database
   - Prepares CCAvenue parameters (merchant_id, order_id, amount, billing details)
   - Encrypts parameters using working_key
   - Auto-submits form to CCAvenue's transaction URL
4. User completes payment on CCAvenue
5. CCAvenue redirects to `ccavResponseHandler.php` with encrypted response
6. `ccavResponseHandler.php`:
   - Decrypts response
   - Updates order status to `paid` (if successful) or `failed`
   - Clears cart
   - Redirects to `order-status.php`

### Step 3B: Cash On Delivery Flow
1. User clicks "Cash On Delivery (COD)"
2. Form POSTs to `checkout.php` with `confirm_cod=1`
3. Order is updated with payment_id = 'COD'
4. Cart is cleared
5. User is redirected to confirmation page

### Step 4: Order Confirmation
- `order-status.php` displays order details and payment status
- User can continue shopping

## Configuration

### Environment Variables (.env file)
```env
# CCAvenue Configuration
CCAVENUE_MERCHANT_ID=your_merchant_id
CCAVENUE_ACCESS_CODE=your_access_code
CCAVENUE_WORKING_KEY=your_working_key
CCAVENUE_REDIRECT_URL=https://yourdomain.com/ccavResponseHandler.php
CCAVENUE_CANCEL_URL=https://yourdomain.com/ccavCancel.php
```

### Getting CCAvenue Credentials
1. Sign up for CCAvenue merchant account at https://www.ccavenue.com/
2. After approval, login to CCAvenue dashboard
3. Navigate to Settings > Generate Working Key
4. Copy your:
   - Merchant ID
   - Access Code
   - Working Key
5. Set redirect URL to: `https://yourdomain.com/ccavResponseHandler.php`
6. Set cancel URL to: `https://yourdomain.com/ccavCancel.php`

## Database Schema

### Orders Table
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
    razorpay_payment_id VARCHAR(255),  -- Used for CCAvenue tracking_id
    razorpay_order_id VARCHAR(255),    -- Used for CCAvenue bank_ref_no
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Order Items Table
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

## Testing

### Test Mode
CCAvenue provides a test environment. To use it:
1. Use test credentials provided by CCAvenue
2. Change the action URL in `ccavGenerate.php` to test URL (if different)
3. Use test card details provided by CCAvenue

### Test Card Details (provided by CCAvenue)
- Card Number: 4111111111111111
- CVV: 123
- Expiry: Any future date
- Name: Test User

### Local Testing
For local testing, you need to:
1. Use ngrok or similar tool to expose your localhost
2. Update `CCAVENUE_REDIRECT_URL` and `CCAVENUE_CANCEL_URL` to use the public URL
3. Ensure your CCAvenue account has these URLs whitelisted

Example with ngrok:
```bash
ngrok http 80
# Use the generated URL (e.g., https://abc123.ngrok.io)
# Update .env:
CCAVENUE_REDIRECT_URL=https://abc123.ngrok.io/ccavResponseHandler.php
CCAVENUE_CANCEL_URL=https://abc123.ngrok.io/ccavCancel.php
```

## Security Features

1. **CSRF Protection**: All forms use CSRF tokens
2. **Encryption**: All payment data is encrypted using AES-128-CBC
3. **Session Management**: Order IDs stored in session to prevent tampering
4. **Input Validation**: All user inputs are validated and sanitized
5. **Prepared Statements**: All database queries use prepared statements

## Troubleshooting

### Payment Not Processing
- Check if CCAvenue credentials are correct in `.env`
- Verify redirect URLs are whitelisted in CCAvenue dashboard
- Check error logs for encryption/decryption errors

### Order Status Not Updating
- Verify `ccavResponseHandler.php` is accessible
- Check database connection
- Review error logs for database errors

### Redirect Issues
- Ensure `CCAVENUE_REDIRECT_URL` matches exactly what's configured in CCAvenue
- Check for trailing slashes
- Verify SSL certificate is valid (CCAvenue requires HTTPS in production)

## Production Checklist

- [ ] Update `.env` with production CCAvenue credentials
- [ ] Set `SITE_ENV=production` in `.env`
- [ ] Set `SITE_DEBUG=false` in `.env`
- [ ] Ensure `CCAVENUE_REDIRECT_URL` uses HTTPS
- [ ] Whitelist redirect URLs in CCAvenue dashboard
- [ ] Test complete payment flow with real transaction
- [ ] Set up proper error logging and monitoring
- [ ] Configure email notifications for failed payments
- [ ] Backup database regularly

## Support

For CCAvenue-specific issues:
- Email: service@ccavenue.com
- Phone: +91-22-4272 1111
- Documentation: https://www.ccavenue.com/integration_kit.jsp

For application issues:
- Check error logs in your server
- Review database for order status
- Contact your development team
