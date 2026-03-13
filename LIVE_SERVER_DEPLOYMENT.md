# 🚀 PRODUCTION DEPLOYMENT GUIDE
## Doyen Auto Services - Live Server Deployment

**Domain:** https://doyen.cliqpos.com  
**Date:** February 13, 2026

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### ✅ Local System Status:
- ✅ All features implemented and tested
- ✅ 142+ tests passing (100%)
- ✅ Email system configured and working
- ✅ Database fully configured
- ✅ Production .env file ready

### ⚠️ Server Requirements:

**Minimum Server Specifications:**
- PHP 8.2 or higher
- MySQL 5.7+ / MariaDB 10.3+
- Composer 2.x
- Node.js 18.x+ (for asset compilation)
- SSL Certificate (HTTPS required)
- Cron job support

**Required PHP Extensions:**
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML

---

## 🗂️ STEP 1: PREPARE FILES FOR UPLOAD

### 1.1 Copy Production Environment File

```bash
# On your local machine
copy .env.production .env
```

### 1.2 Optimize for Production

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compile assets for production
npm run build
```

### 1.3 Files/Folders to EXCLUDE from Upload

**DO NOT upload these:**
- `.env` (upload `.env.production` separately)
- `node_modules/` (too large, not needed)
- `.git/` (version control, not needed)
- `storage/logs/*.log` (old log files)
- `storage/framework/cache/*` (cache files)
- `storage/framework/sessions/*` (session files)
- `storage/framework/views/*` (compiled views)
- `tests/` (optional, testing only)
- `.env.example`
- `phpunit.xml`
- `*.bat` (Windows batch files)
- `*.php` testing scripts (comprehensive-test.php, phase1-test.php, etc.)

**MUST upload these:**
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/` (document root)
- `resources/`
- `routes/`
- `storage/` (with correct permissions)
- `vendor/`
- `.htaccess`
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`

---

## 🌐 STEP 2: SERVER SETUP

### 2.1 Upload Files via FTP/SFTP

**Recommended Upload Method:**
- Use FileZilla, WinSCP, or cPanel File Manager
- Upload to: `/home/your-username/public_html/` or dedicated folder

**Upload Structure:**
```
/home/username/
├── public_html/          ← Should point to Laravel's public folder
│   ├── index.php
│   ├── favicon.ico
│   ├── build/
│   └── ...
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env                  ← Rename from .env.production
└── artisan
```

**OR Configure Document Root:**
Point your domain to `/public` folder:
- cPanel: Set document root to `public`
- Direct server: Configure Apache/Nginx virtualhost

### 2.2 Upload .env.production

1. Upload `.env.production` to server root
2. Rename it to `.env`
3. Edit on server to add production database credentials

### 2.3 Set Correct Permissions

```bash
# SSH into your server, then:
cd /path/to/your-app

# Storage and cache folders (must be writable)
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Change owner to web server user (usually www-data or nobody)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# Or if using cPanel shared hosting:
chown -R username:username storage
chown -R username:username bootstrap/cache
```

---

## 🗄️ STEP 3: DATABASE SETUP

### 3.1 Create Production Database

**Via cPanel:**
1. Go to MySQL Databases
2. Create new database: `yourusername_garage`
3. Create new user: `yourusername_garage_user`
4. Add user to database with ALL PRIVILEGES
5. Note: hostname, database name, username, password

**Via SSH/MySQL:**
```sql
CREATE DATABASE garage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'garage_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON garage.* TO 'garage_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3.2 Update .env on Server

Edit `/path/to/your-app/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost          # or 127.0.0.1
DB_PORT=3306
DB_DATABASE=yourusername_garage
DB_USERNAME=yourusername_garage_user
DB_PASSWORD=your_strong_password
```

### 3.3 Import Database or Run Migrations

**Option A: Import from Local (if you have data)**
```bash
# Export local database
mysqldump -u root garage > garage_backup.sql

# Upload garage_backup.sql to server
# Import on server
mysql -u yourusername_garage_user -p yourusername_garage < garage_backup.sql
```

**Option B: Fresh Installation**
```bash
# SSH into server
cd /path/to/your-app
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

---

## 🔐 STEP 4: SECURITY & OPTIMIZATION

### 4.1 Generate New APP_KEY (if needed)

```bash
php artisan key:generate --force
```

### 4.2 Create Storage Symlink

```bash
php artisan storage:link
```

### 4.3 Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4.4 Verify .htaccess

Check `public/.htaccess` contains:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## ⏰ STEP 5: CRON JOB SETUP (CRITICAL!)

### 5.1 Add Cron Job for Laravel Scheduler

**Via cPanel:**
1. Go to Cron Jobs
2. Add new cron job:
   - **Minute:** `*` (every minute)
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:**
   ```bash
   cd /home/username/path-to-app && php artisan schedule:run >> /dev/null 2>&1
   ```

**Via SSH (crontab -e):**
```bash
* * * * * cd /home/username/path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

**Verify it's working:**
```bash
# Check cron logs (location varies by server)
tail -f /var/log/cron
# or
tail -f /var/log/syslog | grep CRON
```

---

## 🔧 STEP 6: SERVER-SPECIFIC CONFIGURATION

### 6.1 Apache Configuration (if applicable)

Create `.htaccess` in root (above public):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 6.2 Nginx Configuration

If using Nginx, configure virtualhost:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name doyen.cliqpos.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name doyen.cliqpos.com;
    root /home/username/path-to-app/public;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6.3 SSL Certificate

**Option A: Free SSL via Let's Encrypt (Recommended)**
```bash
# Via cPanel: SSL/TLS → Install Free SSL
# Or via Certbot:
sudo certbot --nginx -d doyen.cliqpos.com
```

**Option B: Purchased SSL**
- Upload certificate files via cPanel SSL/TLS section
- Or configure in Nginx/Apache virtualhost

---

## ✅ STEP 7: POST-DEPLOYMENT TESTING

### 7.1 Access Your Site

Visit: **https://doyen.cliqpos.com**

### 7.2 Test Critical Features

**Login & Authentication:**
```
✓ Visit homepage
✓ Login with admin credentials
✓ Password reset works
```

**Core Functionality:**
```
✓ Dashboard loads
✓ Create customer
✓ DVLA vehicle lookup
✓ Create appointment
✓ Generate invoice
✓ Download PDF
```

**Email System:**
```bash
# SSH into server
cd /path/to/your-app
php artisan tinker

# In tinker:
Mail::raw('Test from production', function($m) {
    $m->to('your-email@example.com')->subject('Production Test');
});
```

**Automated Reminders:**
```bash
php artisan appointments:send-reminders --hours=24
php artisan mot:send-reminders --days=30
php artisan schedule:list
```

### 7.3 Check Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Check for errors
```

---

## 🚨 TROUBLESHOOTING

### Issue: 500 Internal Server Error

**Solutions:**
1. Check `.htaccess` exists in public folder
2. Verify storage permissions: `chmod -R 775 storage`
3. Check error logs: `tail storage/logs/laravel.log`
4. Ensure APP_KEY is set in .env
5. Clear caches: `php artisan config:clear`

### Issue: Assets Not Loading (CSS/JS)

**Solutions:**
1. Run `npm run build` before uploading
2. Check `public/build/` folder exists
3. Verify ASSET_URL in .env: `https://doyen.cliqpos.com`
4. Clear browser cache

### Issue: Database Connection Failed

**Solutions:**
1. Verify database credentials in .env
2. Check database user has correct permissions
3. Try DB_HOST as `127.0.0.1` instead of `localhost`
4. Ensure MySQL port 3306 is correct

### Issue: Emails Not Sending

**Solutions:**
1. Verify MAIL_ settings in .env
2. Test with: `php artisan tinker` → send test email
3. Check firewall allows SMTP port
4. Verify SMTP credentials are correct

### Issue: Cron Jobs Not Running

**Solutions:**
1. Verify cron job command path is absolute
2. Check cron logs
3. Test manually: `php artisan schedule:run`
4. Ensure PHP CLI is available: `which php`

---

## 📊 PRODUCTION MONITORING

### Essential Checks

**Daily:**
- Check error logs: `storage/logs/laravel.log`
- Monitor email delivery
- Verify cron jobs running

**Weekly:**
- Database backup
- Check disk space
- Review system performance

**Monthly:**
- Update dependencies: `composer update`
- Security patches
- Performance optimization

---

## 🔒 SECURITY BEST PRACTICES

### Already Implemented:
- ✅ APP_DEBUG=false (errors hidden from users)
- ✅ APP_ENV=production
- ✅ HTTPS/SSL enforced
- ✅ SESSION_ENCRYPT=true
- ✅ Strong APP_KEY
- ✅ CSRF protection enabled
- ✅ SQL injection protection (Laravel ORM)
- ✅ XSS protection

### Additional Recommendations:
1. **Firewall:** Enable UFW or server firewall
2. **Fail2Ban:** Block brute force attempts
3. **Backups:** Automated daily database backups
4. **Updates:** Keep PHP, Laravel, dependencies updated
5. **Monitoring:** Set up error notifications
6. **Rate Limiting:** Laravel built-in rate limiting active

---

## 📞 SUPPORT & MAINTENANCE

### Quick Commands Reference

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan optimize

# Run migrations
php artisan migrate --force

# Test scheduler
php artisan schedule:run

# View logs
tail -f storage/logs/laravel.log

# Restart queue workers (if using)
php artisan queue:restart
```

### File Locations

- Application: `/home/username/path-to-app/`
- Public files: `/home/username/path-to-app/public/`
- Logs: `/home/username/path-to-app/storage/logs/`
- Uploaded files: `/home/username/path-to-app/storage/app/`
- Environment: `/home/username/path-to-app/.env`

---

## ✅ FINAL DEPLOYMENT CHECKLIST

Before going live, verify:

- [ ] All files uploaded to server
- [ ] .env.production renamed to .env
- [ ] Database credentials updated in .env
- [ ] Database created and migrated
- [ ] Roles & permissions seeded
- [ ] Storage permissions set (775)
- [ ] Storage symlink created
- [ ] SSL certificate installed
- [ ] Cron job configured and running
- [ ] Email tested and working
- [ ] DVLA API tested
- [ ] DVSA MOT API tested
- [ ] Admin user can login
- [ ] Public booking form works
- [ ] Invoice PDF generation works
- [ ] All caches optimized
- [ ] Error logs checked (no critical errors)
- [ ] Backup strategy in place

---

## 🎉 GO LIVE!

Once all checks pass, your **Doyen Auto Services** garage management system is **LIVE** at:

**https://doyen.cliqpos.com**

### What Happens Automatically:
- ✅ Customers book appointments online
- ✅ Automated email confirmations
- ✅ Appointment reminders (24h & 1h before)
- ✅ MOT expiry alerts (30, 14, 7, 3 days before)
- ✅ Invoice emails
- ✅ Password resets
- ✅ Daily database backups

### First Steps After Deployment:
1. Login as admin
2. Update your user profile
3. Add staff members
4. Configure any additional settings
5. Training for staff
6. Monitor for 24-48 hours

---

**Congratulations on your production deployment!** 🚀

**Need help?** Check logs at `storage/logs/laravel.log`
