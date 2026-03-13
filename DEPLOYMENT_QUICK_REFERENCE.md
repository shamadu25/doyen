# 🚀 QUICK DEPLOYMENT REFERENCE CARD
## Doyen Auto Services → https://doyen.cliqpos.com

---

## 📦 BEFORE UPLOAD

```bash
# Run this script:
prepare-production.bat

# Or manually:
php artisan optimize
npm run build
```

---

## 📤 FILES TO UPLOAD

**Upload ALL except:**
- ❌ node_modules/
- ❌ .git/
- ❌ .env (upload .env.production instead)
- ❌ *.log files
- ❌ test*.php files
- ❌ *.bat files

**MUST upload:**
- ✅ app/
- ✅ bootstrap/
- ✅ config/
- ✅ database/
- ✅ public/ (your document root)
- ✅ resources/
- ✅ routes/
- ✅ storage/
- ✅ vendor/
- ✅ .env.production
- ✅ artisan

---

## 🗄️ SERVER SETUP (5 Minutes)

### 1. Database
```sql
CREATE DATABASE garage;
CREATE USER 'garage_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON garage.* TO 'garage_user'@'localhost';
```

### 2. Environment File
```bash
# Upload .env.production
# Rename to .env
# Edit: Update DB credentials
```

### 3. Key Command Sequence
```bash
cd /path/to/app

# Permissions
chmod -R 775 storage bootstrap/cache

# Initialize
php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ⏰ CRON JOB (CRITICAL!)

**Command:**
```bash
* * * * * cd /home/username/path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

**Via cPanel:**
- Cron Jobs → Add New
- Every minute: `* * * * *`
- Paste command above

---

## 🔐 .ENV SETTINGS FOR PRODUCTION

**Must update on server:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://doyen.cliqpos.com

DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

ASSET_URL=https://doyen.cliqpos.com
SESSION_DOMAIN=.cliqpos.com
```

---

## ✅ POST-DEPLOYMENT TESTS

```bash
# 1. Test database
php artisan migrate:status

# 2. Test email
php artisan tinker
Mail::raw('Test', function($m){ $m->to('your@email.com')->subject('Test'); });

# 3. Test scheduler
php artisan schedule:list

# 4. Test reminders
php artisan appointments:send-reminders --hours=24

# 5. Check logs
tail -f storage/logs/laravel.log
```

---

## 🚨 QUICK TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| 500 Error | `chmod -R 775 storage` + check logs |
| No CSS/JS | Run `npm run build` + check ASSET_URL |
| DB Error | Check .env database credentials |
| Emails fail | Verify MAIL_ settings in .env |
| Cron not running | Check cron path is absolute |

---

## 📊 VERIFY DEPLOYMENT

Visit: **https://doyen.cliqpos.com**

Check:
- [ ] Homepage loads
- [ ] Can login
- [ ] Dashboard works
- [ ] Create customer
- [ ] DVLA lookup works
- [ ] Create appointment
- [ ] Generate invoice
- [ ] Download PDF
- [ ] Email sent
- [ ] Cron job running

---

## 📞 EMERGENCY COMMANDS

```bash
# Clear everything
php artisan optimize:clear

# Rebuild everything
php artisan optimize

# View errors
tail -100 storage/logs/laravel.log

# Check permissions
ls -la storage/
ls -la bootstrap/cache/
```

---

## 🎯 SUCCESS INDICATORS

✅ Site accessible at https://doyen.cliqpos.com
✅ SSL certificate valid (green padlock)
✅ Admin can login
✅ No errors in logs
✅ Email test successful
✅ Cron job shows in `php artisan schedule:list`
✅ Database has 5+ users, 4 roles, 51 permissions

---

**Full Guide:** See LIVE_SERVER_DEPLOYMENT.md

**Need Help?** Check storage/logs/laravel.log for errors
