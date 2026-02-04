# 🎯 Production Setup Guide for sbsmart.in

## Quick Setup (5 Minutes)

### Step 1: Create .env File
On your production server, create a `.env` file in the project root:

```bash
cd /path/to/sbsnewbackup
nano .env
```

Paste this content:
```env
SITE_NAME=SBSmart
SITE_BASE_URL=https://sbsmart.in
SITE_ASSETS_PATH=/assets
SITE_ENV=production
SITE_DEBUG=false

DB_HOST=localhost
DB_NAME=invest13_sbsmart
DB_USER=invest13_pram
DB_PASS=aA1qwerty@@@
DB_CHARSET=utf8mb4

MAIL_SMTP_HOST=mail.sbsmart.in
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=noreply@sbsmart.in
MAIL_SMTP_PASS=aA1qwerty@@@
MAIL_FROM_EMAIL=noreply@sbsmart.in
MAIL_FROM_NAME=SBSmart
MAIL_USE_SMTP=true

CCAVENUE_MERCHANT_ID=254361
CCAVENUE_ACCESS_CODE=AVY9JHI33CI9S6VYIC
CCAVENUE_WORKING_KEY=20F8642681BB4F3BA1BD8D6B38F727AE0C
CCAVENUE_REDIRECT_URL=https://sbsmart.in/ccavResponseHandler.php
CCAVENUE_CANCEL_URL=https://sbsmart.in/ccavCancel.php
```

Save and set permissions:
```bash
chmod 644 .env
```

---

### Step 2: Configure CCAvenue Dashboard

1. Login to CCAvenue merchant dashboard: https://dashboard.ccavenue.com/
2. Navigate to **Settings** → **Integration Settings**
3. Add these URLs to whitelist:
   - **Redirect URL:** `https://sbsmart.in/ccavResponseHandler.php`
   - **Cancel URL:** `https://sbsmart.in/ccavCancel.php`
4. Save changes

---

### Step 3: Test the Integration

1. Visit: https://sbsmart.in/checkout.php
2. Add items to cart
3. Fill checkout form
4. Try a ₹1 test transaction
5. Verify payment flow works

---

## 🔍 Verification Commands

### Check if files exist:
```bash
ls -la ccavGenerate.php
ls -la ccavResponseHandler.php
ls -la ccavCancel.php
ls -la order-status.php
ls -la includes/ccavenue-crypto.php
```

### Check .env file:
```bash
cat .env | grep CCAVENUE
```

### Test URLs are accessible:
```bash
curl -I https://sbsmart.in/ccavResponseHandler.php
curl -I https://sbsmart.in/ccavCancel.php
```

---

## 🐛 Troubleshooting

### Issue: "Payment response missing"
**Fix:** Verify redirect URL is whitelisted in CCAvenue dashboard

### Issue: "Database connection failed"
**Fix:** Check database credentials in .env file

### Issue: "Encryption failed"
**Fix:** Verify CCAVENUE_WORKING_KEY is correct

### Issue: "Page not found (404)"
**Fix:** Check file permissions and Apache configuration

---

## 📞 Emergency Contacts

**CCAvenue Support:**
- Phone: +91-22-4272 1111
- Email: service@ccavenue.com

**Check Logs:**
```bash
tail -f /var/log/apache2/error.log
```

---

## ✅ Success Checklist

- [x] CCAvenue integration files in place
- [x] .env file configured for production
- [ ] .env file created on server
- [ ] URLs whitelisted in CCAvenue dashboard
- [ ] SSL certificate active
- [ ] Test transaction completed
- [ ] Ready for live transactions

---

**Your Domain:** https://sbsmart.in
**Status:** Ready for Production ✅
**Next Step:** Create .env file on your server and whitelist URLs in CCAvenue
