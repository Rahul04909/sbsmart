# Database Connection Error - Troubleshooting Guide

## Quick Fix Steps

### 1. Upload the Diagnostic Script
1. Upload `db_test.php` to your live server root (same folder as index.php)
2. Access it via: `https://yourdomain.com/db_test.php`
3. Read the detailed error message

### 2. Check Your .env File
Make sure your `.env` file is uploaded to the live server with correct values:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=invest13_sbsmart
DB_USER=invest13_pram
DB_PASS=your_actual_password
DB_CHARSET=utf8mb4
```

### 3. Common Issues & Solutions

#### Issue: "Access denied for user"
**Solution:** Wrong username or password
- Verify `DB_USER` and `DB_PASS` in your .env file
- Get correct credentials from your hosting cPanel
- Usually format: `cpanelusername_dbuser`

#### Issue: "Unknown database"
**Solution:** Database doesn't exist
- Check `DB_NAME` in your .env file
- Create database via cPanel → MySQL Databases
- Database name format: `cpanelusername_dbname`

#### Issue: "Can't connect to MySQL server"
**Solution:** Wrong host
- Most shared hosting uses `localhost`
- Some use `127.0.0.1` or specific hostname
- Check with your hosting provider

#### Issue: ".env file not found"
**Solution:** File not uploaded or wrong location
- Upload .env file to root directory (same level as index.php)
- Check file permissions (should be 644)
- Make sure it's not named `.env.txt`

### 4. Where to Find Credentials

**cPanel Method:**
1. Login to cPanel
2. Go to "MySQL Databases"
3. Database name is shown at top
4. Username is shown in "Current Users" section
5. Create new user or reset password if needed

**Hosting Provider:**
- Check welcome email from hosting provider
- Contact support for database credentials

### 5. After Fixing

1. Test with `db_test.php` - should show "✅ CONNECTION SUCCESSFUL"
2. **DELETE `db_test.php`** for security
3. Clear browser cache and test your website

### 6. Enable Debug Mode (Temporarily)

Add to your `.env` file:
```env
SITE_ENV=development
SITE_DEBUG=true
```

This will show detailed errors on screen. **Remember to disable after fixing!**

## Server Logs Location

Check these files for detailed error messages:
- `/home/username/public_html/error_log`
- `/var/log/apache2/error.log` (if you have access)
- cPanel → Errors → Error Log

## Need More Help?

If the diagnostic script shows an error you can't fix:
1. Take a screenshot of the `db_test.php` output
2. Check your hosting provider's documentation
3. Contact hosting support with the error details
