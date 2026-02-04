# QUICK FIX GUIDE - Database Connection Error

## The Problem
Your live server is NOT reading the .env file, so it's using wrong credentials:
- Using: `root` with no password ❌
- Should use: Your actual hosting database credentials ✅

## Fix Steps (Do in Order)

### Step 1: Upload check_env.php
1. Upload `check_env.php` to your live server
2. Access: `https://yourdomain.com/check_env.php`
3. This will tell you if .env file exists

### Step 2A: If .env File is Missing
1. **Find your local .env file** (in `c:\xampp\htdocs\account\.env`)
2. **Edit it with your LIVE server credentials:**
   ```
   DB_HOST=localhost
   DB_NAME=invest13_sbsmart
   DB_USER=invest13_pram
   DB_PASS=aA1qwerty@@@
   ```
3. **Upload to live server** (same folder as index.php)
4. **Set permissions to 644** (via FTP or cPanel)

### Step 2B: If .env File Exists But Not Working
The file might be corrupted or have wrong format.

**Create a fresh .env file:**
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=invest13_sbsmart
DB_USER=invest13_pram
DB_PASS=aA1qwerty@@@
DB_CHARSET=utf8mb4

# Site Configuration
SITE_NAME=SBSmart
SITE_BASE_URL=https://sbsmart.in
SITE_ENV=production
SITE_DEBUG=false

# Mail Configuration
MAIL_SMTP_HOST=mail.sbsmart.in
MAIL_SMTP_PORT=587
MAIL_SMTP_USER=noreply@sbsmart.in
MAIL_SMTP_PASS=aA1qwerty@@@
MAIL_FROM_EMAIL=noreply@sbsmart.in
MAIL_FROM_NAME=SBSmart
```

### Step 3: Verify Database Credentials

**Get correct credentials from cPanel:**
1. Login to cPanel
2. Go to "MySQL Databases"
3. Your database name: Usually `cpanelusername_dbname`
4. Your database user: Usually `cpanelusername_dbuser`
5. Password: Use existing or create new

**Common hosting formats:**
- Database Name: `invest13_sbsmart` ✅
- Database User: `invest13_pram` ✅
- Host: `localhost` (99% of shared hosting)

### Step 4: Upload Fixed Files
Upload these files to your live server:
1. `.env` (with correct credentials)
2. `includes/config.php` (updated version)
3. `includes/db.php` (updated version)

### Step 5: Test
1. Access `https://yourdomain.com/check_env.php`
   - Should show ✅ .env file exists and loaded
2. Access `https://yourdomain.com/db_test.php`
   - Should show ✅ CONNECTION SUCCESSFUL

### Step 6: Clean Up
**DELETE these files for security:**
- `check_env.php`
- `db_test.php`

## Still Not Working?

### Check File Permissions
Via FTP or cPanel File Manager:
- `.env` should be `644`
- `includes/` folder should be `755`
- `includes/config.php` should be `644`

### Check PHP Version
Your server needs PHP 7.4 or higher. Check in cPanel → Select PHP Version

### Enable Error Logging
Add to `.env`:
```
SITE_DEBUG=true
```
This will show detailed errors. **Turn off after fixing!**

## Contact Info
If still stuck, contact your hosting provider with this info:
- Error: "Access denied for user 'root'@'localhost'"
- Need: Correct database credentials for invest13_sbsmart
- Ask: How to set up database user and password
