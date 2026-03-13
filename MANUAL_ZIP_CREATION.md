# 📦 MANUAL ZIP CREATION GUIDE
## Doyen Auto Services - Production Deployment Package

Since automated ZIP creation encountered file locks, follow these simple steps to create your deployment package manually:

---

## ✅ STEP-BY-STEP INSTRUCTIONS

### 1. Open Windows Explorer
- Navigate to: `C:\xampp\htdocs\garage\garage\`

### 2. Select These Folders (Hold Ctrl and click each):
- ✅ **app** (application code)
- ✅ **bootstrap** (framework bootstrap)
- ✅ **config** (configuration files)
- ✅ **database** (migrations, seeds)
- ✅ **public** (web root with assets)
- ✅ **resources** (views, JS, CSS)
- ✅ **routes** (route definitions)
- ✅ **storage** (uploads, logs, cache)
- ✅ **vendor** (PHP dependencies)

### 3. Select These  Files (Hold Ctrl and click each):
- ✅ **artisan** (Laravel command tool)
- ✅ **composer.json** (PHP dependencies list)
- ✅ **composer.lock** (dependency versions)
- ✅ **package.json** (Node dependencies)
- ✅ **.env.production** (production environment)
- ✅ **LIVE_SERVER_DEPLOYMENT.md** (deployment guide)
- ✅ **DEPLOYMENT_QUICK_REFERENCE.md** (quick reference)

### 4. Create ZIP File
- Right-click on the selected files/folders
- Choose **"Send to"** → **"Compressed (zipped) folder"**
- Name it: **`doyen-auto-production.zip`**

### 5. Verify ZIP Contents (Optional)
- Open the ZIP file
- Confirm all folders and files are present
- Expected size: ~80-150 MB

---

## 📋 CHECKLIST - What to INCLUDE

```
✅ app/                     (Application code)
✅ bootstrap/               (Framework files)
✅ config/                  (All config files)
✅ database/                (Migrations + seeders)
✅ public/                  (Including public/build/ with compiled assets)
✅ resources/               (Views, JS, CSS source)
✅ routes/                  (web.php, api.php, console.php)
✅ storage/                 (With correct structure)
✅ vendor/                  (All PHP packages)
✅ artisan                  (Command line tool)
✅ composer.json            (Dependencies)
✅ composer.lock            (Locked versions)
✅ package.json             (Node packages)
✅ .env.production          (IMPORTANT!)
✅ LIVE_SERVER_DEPLOYMENT.md
✅ DEPLOYMENT_QUICK_REFERENCE.md
```

---

## ❌ What to EXCLUDE (Don't Select These)

```
❌ node_modules/            (Not needed - too large)
❌ .git/                    (Version control)
❌ .env                     (Local environment)
❌ *.log files              (Old logs)
❌ test*.php                (Testing scripts)
❌ *-test.php               (Testing scripts)
❌ *.bat files              (Windows scripts)
❌ comprehensive-test.php
❌ deployment-ready-check.php
❌ phase1-test.php
❌ phase2-test.php
❌ check-vehicles-columns.php
```

---

## 🎯 QUICK ALTERNATIVE METHOD

### Using 7-Zip (if installed):
1. Install 7-Zip if you don't have it: https://www.7-zip.org/
2. Select all required folders/files
3. Right-click → "7-Zip" → "Add to archive..."
4. Set format to "zip"
5. Set compression level to "Normal" or "Maximum"
6. Click OK

---

## 📤 AFTER CREATING ZIP

### Upload to Server:

1. **Via cPanel File Manager:**
   - Login to cPanel
   - Go to File Manager
   - Navigate to your domain's root folder
   - Click "Upload"
   - Select `doyen-auto-production.zip`
   - After upload, click "Extract"

2. **Via FTP (FileZilla):**
   - Connect to your server
   - Upload `doyen-auto-production.zip`
   - Use server's file manager to extract
   - OR extract locally and upload all files (slower)

### Then Follow:
→ **LIVE_SERVER_DEPLOYMENT.md** for complete setup instructions
→ **DEPLOYMENT_QUICK_REFERENCE.md** for quick commands

---

## ✅ VERIFICATION

After creating ZIP, check:
- [ ] ZIP file size is 80-150 MB
- [ ] Can open ZIP and see folders
- [ ] app/ folder is present
- [ ] public/build/ folder exists (compiled assets)
- [ ] vendor/ folder exists  
- [ ] .env.production file is present
- [ ] storage/ folder structure intact

---

## 🚀 DEPLOYMENT SEQUENCE

```
1. Create ZIP (this guide)
2. Upload to server
3. Extract ZIP
4. Rename .env.production to .env  
5. Update database credentials in .env
6. Set permissions: chmod -R 775 storage bootstrap/cache
7. Run: php artisan migrate --force
8. Run: php artisan db:seed --class=RolesAndPermissionsSeeder --force
9. Setup cron job
10. Install SSL
11. Test site!
```

---

## 💡 TIPS

**If ZIP is Too Large (>200 MB):**
- The `vendor/` folder is large but necessary
- `public/build/` contains compiled assets (needed)
- You can upload and extract on server instead of local extraction

**For Faster Upload:**
- Use FTP for better reliability
- Upload during off-peak hours
- Consider server-side Git pull as alternative

**Alternative if You Have SSH Access:**
- Upload files via Git
- Clone repo on server
- Run `composer install --no-dev --optimize-autoloader`
- Run `npm run build` on server

---

## 🆘 NEED HELP?

If you encounter issues:
1. Ensure all assets are compiled: `npm run build`
2. Clear any file locks: Close VS Code, restart
3. Use 7-Zip instead of Windows built-in compression
4. Split upload if file too large (upload folders separately)

---

**Ready to deploy!** Once ZIP is created, follow **LIVE_SERVER_DEPLOYMENT.md** for complete server setup.
