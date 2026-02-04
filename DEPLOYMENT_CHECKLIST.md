# 🚀 Production Deployment Checklist for sbsmart.in

## ✅ Pre-Deployment Steps

### 1. Environment Configuration
Create a `.env` file in the project root with the following content:

```env
# Site Configuration
SITE_NAME=SBSmart
SITE_BASE_URL=https://sbsmart.in
SITE_ASSETS_PATH=/assets
SITE_ENV=production
SITE_DEBUG=false

# Database Configuration
DB_HOST=localhost
DB_NAME=invest13_sbsmart
DB_USER=invest13_pram
DB_PASS=aA1qwerty@@@
DB_CHARSET=utf8mb4

# Mail Configuration
MAIL_SMTP_HOST=mail.sbsmart.in
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=noreply@sbsmart.in
MAIL_SMTP_PASS=aA1qwerty@@@
MAIL_FROM_EMAIL=noreply@sbsmart.in
MAIL_FROM_NAME=SBSmart
MAIL_USE_SMTP=true

# CCAvenue Payment Gateway Configuration
CCAVENUE_MERCHANT_ID=254361
CCAVENUE_ACCESS_CODE=AVY9JHI33CI9S6VYIC
CCAVENUE_WORKING_KEY=20F8642681BB4F3BA1BD8D6B38F727AE0C
CCAVENUE_REDIRECT_URL=https://sbsmart.in/ccavResponseHandler.php
CCAVENUE_CANCEL_URL=https://sbsmart.in/ccavCancel.php
```

### 2. CCAvenue Dashboard Configuration

Login to your CCAvenue merchant dashboard and configure:

**Redirect URL:** `https://sbsmart.in/ccavResponseHandler.php`
**Cancel URL:** `https://sbsmart.in/ccavCancel.php`

⚠️ **Important:** These URLs must be whitelisted in CCAvenue dashboard before going live!

### 3. SSL Certificate
- [ ] Ensure SSL certificate is installed and valid for sbsmart.in
- [ ] Test HTTPS access to all pages
- [ ] Verify no mixed content warnings

### 4. File Permissions
```bash
# Set proper permissions
chmod 644 .env
chmod 755 checkout.php
chmod 755 ccavGenerate.php
chmod 755 ccavResponseHandler.php
chmod 755 ccavCancel.php
chmod 755 order-status.php
```

### 5. Database Verification
- [ ] Verify database connection works
- [ ] Check `orders` table exists with correct schema
- [ ] Check `order_items` table exists with correct schema
- [ ] Test a sample order creation

---

## 🧪 Testing Checklist

### Before Going Live:
- [ ] Test complete checkout flow
- [ ] Test CCAvenue payment with ₹1 test transaction
- [ ] Test successful payment response
- [ ] Test failed payment response
- [ ] Test payment cancellation
- [ ] Test COD flow
- [ ] Verify order status updates correctly
- [ ] Verify cart clears after payment
- [ ] Test email notifications (if configured)
- [ ] Check error logging works
- [ ] Test on mobile devices
- [ ] Test on different browsers

### Test URLs:
- Cart: https://sbsmart.in/cart.php
- Checkout: https://sbsmart.in/checkout.php
- Order Status: https://sbsmart.in/order-status.php?id=1

---

## 🔐 Security Checklist

- [ ] `.env` file is NOT in version control
- [ ] `.env` file has restricted permissions (644)
- [ ] SITE_DEBUG is set to `false`
- [ ] All forms have CSRF protection
- [ ] Database uses prepared statements
- [ ] SSL/HTTPS is enforced
- [ ] Error messages don't expose sensitive info
- [ ] Working key is kept secure

---

## 📊 Monitoring Setup

### Error Logs
Monitor these files for errors:
- Apache error log: `/var/log/apache2/error.log` (or XAMPP equivalent)
- PHP error log: Check `php.ini` for error_log location

### Database Monitoring
```sql
-- Check recent orders
SELECT id, name, email, total, status, created_at 
FROM orders 
ORDER BY created_at DESC 
LIMIT 10;

-- Check payment status distribution
SELECT status, COUNT(*) as count 
FROM orders 
GROUP BY status;

-- Check failed payments
SELECT id, name, email, total, created_at 
FROM orders 
WHERE status = 'failed' 
ORDER BY created_at DESC;
```

---

## 🚨 Rollback Plan

If issues occur after deployment:

1. **Immediate Actions:**
   - Set `SITE_DEBUG=true` temporarily to see errors
   - Check error logs
   - Verify database connection

2. **Payment Issues:**
   - Verify CCAvenue credentials are correct
   - Check redirect URLs are whitelisted
   - Test encryption/decryption manually

3. **Database Issues:**
   - Verify database credentials
   - Check table structure
   - Restore from backup if needed

---

## 📞 Support Contacts

### CCAvenue Support
- **Email:** service@ccavenue.com
- **Phone:** +91-22-4272 1111
- **Dashboard:** https://dashboard.ccavenue.com/

### Technical Support
- **Server Issues:** Contact hosting provider
- **Database Issues:** Check phpMyAdmin
- **Application Issues:** Review error logs

---

## 🎯 Post-Deployment Tasks

After successful deployment:

- [ ] Monitor first 10 transactions closely
- [ ] Set up automated backups
- [ ] Configure email notifications for failed payments
- [ ] Set up order management admin panel
- [ ] Document any custom configurations
- [ ] Train staff on order processing
- [ ] Set up customer support for payment queries

---

## 📝 Quick Commands

### Create .env file on server:
```bash
cd /path/to/sbsnewbackup
nano .env
# Paste the configuration above
# Save with Ctrl+X, Y, Enter
chmod 644 .env
```

### Test CCAvenue connectivity:
```bash
curl -I https://sbsmart.in/ccavResponseHandler.php
curl -I https://sbsmart.in/ccavCancel.php
```

### Check PHP errors:
```bash
tail -f /var/log/apache2/error.log
# or for XAMPP
tail -f C:/xampp/apache/logs/error.log
```

---

## ✅ Final Verification

Before announcing payment gateway is live:

1. ✅ Complete a real ₹1 transaction
2. ✅ Verify money is received in merchant account
3. ✅ Verify order status updates correctly
4. ✅ Verify customer receives confirmation
5. ✅ Test refund process (if applicable)
6. ✅ Document the go-live date and time

---

**Deployment Date:** _____________
**Deployed By:** _____________
**First Transaction Date:** _____________
**Status:** ⬜ Ready for Deployment | ⬜ Deployed | ⬜ Live

---

**Domain:** https://sbsmart.in
**Last Updated:** December 5, 2025
