# 🚀 DEPLOYMENT FIX FOR DOYEN.CLIQPOS.COM

## ✅ Localhost Status: WORKING!

## ❌ Production Server Status: HTTP 500 Error

---

## 🛠️ HOW TO FIX PRODUCTION SERVER

### **Issue:**
The production server has the same database migration errors we fixed on localhost.

### **Solution:**
Upload the fixed migration files and run migrations.

---

## 📋 STEP-BY-STEP DEPLOYMENT FIX

### **Option 1: Via cPanel File Manager** (Easiest)

#### 1. **Login to cPanel**
- URL: Usually `https://doyen.cliqpos.com:2083` or your hosting control panel
- Username: Your hosting username
- Password: Your hosting password

#### 2. **Navigate to File Manager**
- Click **"File Manager"**
- Go to your website root (e.g., `public_html` or `doyen.cliqpos.com` folder)
- Navigate to: `/database/migrations/`

#### 3. **Upload Fixed Migration Files**

**Upload these 2 files** (replacing existing ones):

**From your local:**
```
c:\xampp\htdocs\garage\garage\database\migrations\2026_02_13_142036_create_reminders_table.php
c:\xampp\htdocs\garage\garage\database\migrations\2026_02_13_142232_add_certificate_path_to_mot_tests_table.php
```

**To server:**
```
/database/migrations/
```

#### 4. **Open Terminal in cPanel**
- In cPanel, find **"Terminal"** or **"SSH Access"**
- Click to open the terminal

#### 5. **Run These Commands:**
```bash
cd ~/public_html  # or your site directory (e.g., cd ~/doyen.cliqpos.com)

# Run migrations
php artisan migrate

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

#### 6. **Check the Website**
Visit: `https://doyen.cliqpos.com`

**Should now work!** ✅

---

### **Option 2: Via FTP (FileZilla/WinSCP)**

#### 1. **Connect via FTP**
- **Host:** `doyen.cliqpos.com` or your server IP
- **Username:** Your FTP username
- **Password:** Your FTP password
- **Port:** 21 (FTP) or 22 (SFTP)

#### 2. **Navigate to Migrations Folder**
```
/public_html/database/migrations/
(or wherever your site is installed)
```

#### 3. **Upload 2 Files**
Drag and drop these files from your local PC:
```
2026_02_13_142036_create_reminders_table.php
2026_02_13_142232_add_certificate_path_to_mot_tests_table.php
```

#### 4. **Connect via SSH**
Use PuTTY or cPanel Terminal:
```bash
ssh username@doyen.cliqpos.com
cd ~/public_html
php artisan migrate
php artisan optimize:clear
```

---

### **Option 3: Quick SSH Commands** (If you have SSH access)

```bash
# Connect to server
ssh username@doyen.cliqpos.com

# Go to site directory
cd ~/public_html  # adjust if different

# Run migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check status
php artisan route:list --path=/ | head -5
```

**Done!** ✅

---

## 🔍 VERIFY IT WORKED

### **Check #1: Visit Website**
```
https://doyen.cliqpos.com
```
**Should load without errors!**

### **Check #2: Check Server Error Log**
In cPanel:
- Go to **"Error Logs"** (or **"Metrics" → "Errors"**)
- No new 500 errors should appear

### **Check #3: Test a Page**
Try accessing a subpage:
```
https://doyen.cliqpos.com/book-online
```

---

## ⚠️ IMPORTANT NOTES

### **Database Connection**
Make sure your production `.env` file has correct database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=localhost  # or 127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### **File Permissions**
If you get permission errors after upload:
```bash
chmod -R 775 storage bootstrap/cache
```

### **If Migrations Fail**
Check if MySQL is running:
```bash
# In cPanel Terminal or SSH
php artisan tinker
DB::select('SELECT 1');
exit
```

---

## 🆘 TROUBLESHOOTING

### **Error: "Migration already exists"**
This means migrate already ran. Just clear caches:
```bash
php artisan optimize:clear
```

### **Error: "Table already exists"**
The tables exist but migration failed. Check `migrations` table:
```bash
php artisan tinker
DB::table('migrations')->latest()->take(5)->get(['migration', 'batch']);
exit
```

### **Error: "Database connection refused"**
Check `.env` database credentials and ensure MySQL is running on server.

### **Still Getting HTTP 500?**
Check the Laravel error log:
```bash
tail -f storage/logs/laravel.log
```

Or in cPanel → File Manager:
```
/storage/logs/laravel.log
```

---

## 📞 NEED HELP?

If you're not sure how to:
1. Access cPanel
2. Use FTP
3. Connect via SSH

**Tell me which hosting provider you're using** (e.g., cPanel, Plesk, DigitalOcean, AWS) and I'll give you specific instructions!

---

## ✅ SUMMARY

**What's Wrong:** Production server has database migration errors  
**What to Fix:** Upload 2 fixed migration files and run `php artisan migrate`  
**How Long:** 5-10 minutes  
**Result:** Website will work perfectly! 🚀

---

**Last Updated:** February 20, 2026  
**Status:** Ready to deploy!
