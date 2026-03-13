# 🚨 HTTP 500 ERROR - QUICK FIX GUIDE
## Doyen Auto Services - Live Server Troubleshooting

**Error:** "HTTP ERROR 500" on https://doyen.cliqpos.com

---

## ⚡ IMMEDIATE CHECKS (Do These First)

### 1. Check Error Logs (MOST IMPORTANT!)

**Via cPanel File Manager:**
```
Navigate to: storage/logs/laravel.log
Open and read the LAST error message
```

**Via SSH:**
```bash
cd /path/to/your-app
tail -50 storage/logs/laravel.log
```

**Via FTP:**
Download `storage/logs/laravel.log` and open in text editor

📍 **The error log will tell you EXACTLY what's wrong!**

---

## 🔧 COMMON FIXES (Try in Order)

### Fix #1: Set Storage Permissions

**Via SSH:**
```bash
cd /path/to/your-app
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Via cPanel Terminal:**
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Fix #2: Check .env File Exists

**Via cPanel File Manager:**
1. Go to your app root folder
2. Click "Settings" (top right) → Show Hidden Files
3. Look for `.env` file
4. If missing: Rename `.env.production` to `.env`

**Via SSH:**
```bash
cd /path/to/your-app
ls -la .env
# If not found:
mv .env.production .env
```

### Fix #3: Generate Application Key

**Via SSH/Terminal:**
```bash
cd /path/to/your-app
php artisan key:generate --force
```

This creates the APP_KEY in your .env file.

### Fix #4: Clear All Caches

**Via SSH/Terminal:**
```bash
cd /path/to/your-app
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Fix #5: Optimize Configuration

**Via SSH/Terminal:**
```bash
cd /path/to/your-app
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Fix #6: Check Database Connection

**Edit .env file** and verify these are correct:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_db_username
DB_PASSWORD=your_actual_db_password
```

**Test connection via SSH:**
```bash
mysql -u your_username -p
# Enter password
show databases;
```

### Fix #7: Ensure Vendor Directory Exists

**Check if vendor folder was uploaded:**
```bash
cd /path/to/your-app
ls -la vendor/
```

If vendor/ is missing, you need to run:
```bash
composer install --no-dev --optimize-autoloader
```

### Fix #8: Create Storage Link

```bash
cd /path/to/your-app
php artisan storage:link
```

---

## 🎯 MOST LIKELY CAUSES (90% of 500 errors)

### Cause #1: Wrong Permissions (50%)
**Symptoms:** Can't write to storage or cache
**Fix:** `chmod -R 775 storage bootstrap/cache`

### Cause #2: Missing .env File (20%)
**Symptoms:** No configuration found
**Fix:** Rename `.env.production` to `.env`

### Cause #3: No APP_KEY (15%)
**Symptoms:** "No application encryption key"
**Fix:** `php artisan key:generate --force`

### Cause #4: Database Connection Failed (10%)
**Symptoms:** "SQLSTATE[HY000]" error
**Fix:** Check database credentials in .env

### Cause #5: Missing Dependencies (5%)
**Symptoms:** "Class not found" errors
**Fix:** Ensure vendor/ folder was uploaded

---

## 📋 COMPLETE DIAGNOSTIC CHECKLIST

Run these commands in order:

```bash
# 1. Check current directory
pwd
# Should show: /home/username/doyen or similar

# 2. Check .env exists
ls -la .env
# If not found: mv .env.production .env

# 3. Check .env contents
cat .env | grep APP_KEY
# Should show: APP_KEY=base64:...
# If empty: php artisan key:generate --force

# 4. Check storage permissions
ls -la storage/
# All folders should be writable (rwx)

# 5. Set permissions
chmod -R 775 storage bootstrap/cache

# 6. Check database credentials
cat .env | grep DB_
# Verify all values are correct

# 7. Test database connection
php artisan tinker
DB::connection()->getPdo();
# Should return: PDO object
# Exit tinker: exit

# 8. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 9. Optimize
php artisan config:cache
php artisan route:cache

# 10. Check logs
tail -20 storage/logs/laravel.log
```

---

## 🔍 HOW TO READ ERROR LOGS

**Look for these patterns in storage/logs/laravel.log:**

### Pattern 1: Permission Denied
```
file_put_contents(/path/to/storage/...): failed to open stream: Permission denied
```
**Fix:** `chmod -R 775 storage`

### Pattern 2: No Application Key
```
No application encryption key has been specified
```
**Fix:** `php artisan key:generate --force`

### Pattern 3: Database Connection
```
SQLSTATE[HY000] [2002] Connection refused
SQLSTATE[HY000] [1045] Access denied for user
```
**Fix:** Check .env database credentials

### Pattern 4: Class Not Found
```
Class 'Vendor\Package\ClassName' not found
```
**Fix:** Run `composer install` or ensure vendor/ uploaded

### Pattern 5: Missing File
```
file_get_contents(/path/to/file): failed to open stream: No such file
```
**Fix:** Ensure file exists, check file paths

---

## 🛠️ QUICK SERVER COMMANDS REFERENCE

### Via cPanel Terminal:
```bash
# Navigate to app
cd ~/doyen  # or wherever your app is

# Fix permissions
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan optimize:clear

# Re-optimize
php artisan optimize
```

### Via SSH:
```bash
# Change owner to web server user
sudo chown -R www-data:www-data storage bootstrap/cache

# OR if on shared hosting
chown -R $(whoami):$(whoami) storage bootstrap/cache

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

---

## 📞 IF STILL NOT WORKING

### 1. Enable Debug Mode (TEMPORARILY!)

**Edit .env:**
```env
APP_DEBUG=true
APP_ENV=local
```

**Visit site again** - you'll see detailed error message

**⚠️ IMPORTANT:** Turn debug back OFF after fixing:
```env
APP_DEBUG=false
APP_ENV=production
```

### 2. Check PHP Version

```bash
php -v
# Should be 8.2 or higher
```

If PHP version is too old, update via cPanel → Select PHP Version

### 3. Check Required PHP Extensions

```bash
php -m | grep -E 'pdo|mysql|mbstring|xml|json'
```

All should be listed. If missing, enable via cPanel → PHP Extensions

### 4. Check .htaccess in public folder

**Ensure public/.htaccess exists with:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

---

## ✅ AFTER FIXING

Once site loads:

1. **Turn debug OFF:**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

2. **Clear browser cache** (Ctrl + F5)

3. **Test these features:**
   - Login page loads
   - Can login
   - Dashboard shows
   - No errors in logs

4. **Re-optimize:**
   ```bash
   php artisan optimize
   ```

---

## 🆘 EMERGENCY CONTACTS

**If you need server access details:**
- cPanel URL: Usually `https://doyen.cliqpos.com:2083`
- Check with your hosting provider for:
  - SSH access details
  - Database credentials
  - File manager access

**Ask your hosting provider:**
- What's the correct path to my app?
- What user should own the files?
- Is PHP 8.2+ available?

---

## 📊 DEPLOYMENT VERIFICATION SCRIPT

Create this file on server: `check-server.php`

```php
<?php
echo "PHP Version: " . phpversion() . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo ".env exists: " . (file_exists('.env') ? 'YES' : 'NO') . "\n";
echo "storage/ writable: " . (is_writable('storage') ? 'YES' : 'NO') . "\n";
echo "bootstrap/cache writable: " . (is_writable('bootstrap/cache') ? 'YES' : 'NO') . "\n";

// Test .env loading
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    echo "APP_KEY set: " . (strpos($env, 'APP_KEY=base64:') !== false ? 'YES' : 'NO') . "\n";
    echo "DB_HOST: " . (preg_match('/DB_HOST=(.+)/', $env, $m) ? trim($m[1]) : 'NOT SET') . "\n";
}

phpinfo();
```

Run: `php check-server.php`

---

## 🎯 MOST LIKELY SOLUTION

**90% of 500 errors are fixed by:**

```bash
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan optimize
```

**Then check:** https://doyen.cliqpos.com

---

**Start with checking the ERROR LOG - it will tell you exactly what's wrong!**
