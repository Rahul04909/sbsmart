# CCAvenue Payment Gateway - Quick Reference

## 🚀 Quick Start

### 1. Configuration
Edit your `.env` file with CCAvenue credentials:
```env
CCAVENUE_MERCHANT_ID=your_merchant_id
CCAVENUE_ACCESS_CODE=your_access_code
CCAVENUE_WORKING_KEY=your_working_key
CCAVENUE_REDIRECT_URL=https://yourdomain.com/ccavResponseHandler.php
CCAVENUE_CANCEL_URL=https://yourdomain.com/ccavCancel.php
```

### 2. Test the Integration
1. Start your XAMPP server
2. Navigate to: `http://localhost/checkout.php`
3. Add items to cart
4. Fill checkout form
5. Select payment method (CCAvenue or COD)

---

## 📁 File Structure

```
sbsnewbackup/
├── checkout.php                    # Main checkout page
├── ccavGenerate.php                # Payment request generator
├── ccavResponseHandler.php         # Payment response handler
├── ccavCancel.php                  # Cancellation handler
├── order-status.php                # Order status display
├── includes/
│   ├── ccavenue-crypto.php         # Encryption/decryption
│   └── config.php                  # Configuration loader
├── .env                            # Environment variables
└── CCAVENUE_INTEGRATION.md         # Full documentation
```

---

## 🔄 Payment Flow (Simplified)

### Online Payment (CCAvenue)
```
Cart → Checkout Form → Order Created → Payment Selection
  → CCAvenue → Payment → Response Handler → Order Status
```

### Cash On Delivery
```
Cart → Checkout Form → Order Created → Payment Selection
  → COD Confirmation → Order Status
```

---

## 🔑 Key Functions

### Encryption (ccavenue-crypto.php)
```php
encrypt($plainText, $workingKey)    // Encrypt data for CCAvenue
decrypt($encryptedText, $workingKey) // Decrypt CCAvenue response
```

### Database (checkout.php)
```php
// Create order
INSERT INTO orders (name, email, phone, address, total, status)

// Update order status
UPDATE orders SET status = 'paid' WHERE id = ?
```

---

## 🎯 Testing Checklist

- [ ] Cart functionality works
- [ ] Checkout form validation works
- [ ] Order is created in database
- [ ] Payment selection page displays
- [ ] CCAvenue redirect works
- [ ] Payment response is handled correctly
- [ ] Order status updates properly
- [ ] COD confirmation works
- [ ] Cart clears after payment

---

## 🐛 Common Issues & Solutions

### Issue: "Payment response missing"
**Solution:** Check if CCAvenue redirect URL is correctly configured

### Issue: "Order not found"
**Solution:** Verify order_id is being passed correctly in forms

### Issue: "Encryption failed"
**Solution:** Verify CCAVENUE_WORKING_KEY is correct in .env

### Issue: "Database connection failed"
**Solution:** Check database credentials in .env file

---

## 📊 Order Status Values

| Status    | Description                          |
|-----------|--------------------------------------|
| `pending` | Order created, awaiting payment      |
| `paid`    | Payment successful                   |
| `failed`  | Payment failed                       |
| `cancelled` | Order cancelled by user            |

---

## 🔐 Security Features

✅ CSRF token protection on all forms
✅ AES-128-CBC encryption for payment data
✅ Prepared statements for database queries
✅ Input validation and sanitization
✅ Session-based order tracking

---

## 📞 Support Contacts

**CCAvenue Support:**
- Email: service@ccavenue.com
- Phone: +91-22-4272 1111
- Docs: https://www.ccavenue.com/integration_kit.jsp

**For Local Issues:**
- Check error logs: `xampp/apache/logs/error.log`
- Check PHP errors: Enable display_errors in php.ini
- Database: Check phpMyAdmin for order records

---

## 🌐 URLs to Whitelist in CCAvenue

When setting up your CCAvenue account, whitelist these URLs:

**Production (sbsmart.in):**
- Redirect: `https://sbsmart.in/ccavResponseHandler.php`
- Cancel: `https://sbsmart.in/ccavCancel.php`

**Development (localhost):**
- Redirect: `http://localhost/ccavResponseHandler.php`
- Cancel: `http://localhost/ccavCancel.php`

---

## 💡 Pro Tips

1. **Always use HTTPS in production** - CCAvenue requires secure connections
2. **Test with small amounts first** - Use ₹1 for initial testing
3. **Monitor error logs** - Check logs regularly for issues
4. **Keep working key secure** - Never commit .env to version control
5. **Backup database** - Before going live, ensure backups are configured

---

## 📝 Next Steps

1. ✅ CCAvenue integration complete
2. ⏭️ Test with CCAvenue test credentials
3. ⏭️ Configure production credentials
4. ⏭️ Set up email notifications for orders
5. ⏭️ Add order tracking for customers
6. ⏭️ Implement admin panel for order management

---

**Last Updated:** December 2025
**Version:** 1.0
