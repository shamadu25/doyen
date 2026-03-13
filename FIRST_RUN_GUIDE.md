# 🎯 First Run Guide - UK Garage Management System

This guide will help you get the system running for the very first time.

---

## 📋 Pre-Flight Checklist

Before you begin, ensure you have:

- [ ] XAMPP installed and running (Apache + MySQL)
- [ ] PHP 8.2+ installed
- [ ] Composer installed
- [ ] Node.js 18+ and NPM installed
- [ ] A code editor (VS Code recommended)
- [ ] 10-15 minutes of your time

---

## 🚀 Option 1: Automated Setup (EASIEST)

### Step 1: Open Terminal
Navigate to the project directory:
```bash
cd c:\xampp\htdocs\garage\garage\temp-laravel
```

### Step 2: Run Setup Wizard
```bash
php setup-wizard.php
```

The wizard will ask you:
1. **Database name** - Press Enter for default `garage_management`
2. **Database username** - Press Enter for default `root`
3. **Database password** - Press Enter if no password (default XAMPP)
4. **Your garage name** - e.g., "Smith's Auto Repairs"
5. **Your address details**
6. **Contact information**

The wizard will automatically:
- Generate application key
- Create database
- Run migrations
- Seed sample services
- Install NPM packages
- Build frontend assets

### Step 3: Start Server
```bash
php artisan serve
```

### Step 4: Open Browser
Visit: **http://localhost:8000**

**✅ You're done! The system is ready to use.**

---

## 🔧 Option 2: Manual Setup (More Control)

If the automated setup doesn't work, follow these steps:

### 1️⃣ Start XAMPP
- Open XAMPP Control Panel
- Start **Apache**
- Start **MySQL**
- Click **Admin** next to MySQL to open phpMyAdmin

### 2️⃣ Create Database
In phpMyAdmin:
1. Click **New** in the sidebar
2. Database name: `garage_management`
3. Collation: `utf8mb4_unicode_ci`
4. Click **Create**

### 3️⃣ Configure Environment
Open terminal in project folder:
```bash
cd c:\xampp\htdocs\garage\garage\temp-laravel
```

Copy environment file:
```bash
copy .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

### 4️⃣ Edit .env File
Open `.env` in your text editor and update:

```env
# Application
APP_NAME="Your Garage Name"
APP_URL=http://localhost:8000
APP_TIMEZONE=Europe/London

# Database (XAMPP defaults)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=garage_management
DB_USERNAME=root
DB_PASSWORD=

# Your Business Details
GARAGE_NAME="Your Garage Name"
GARAGE_ADDRESS="123 High Street"
GARAGE_CITY="London"
GARAGE_POSTCODE="SW1A 1AA"
GARAGE_PHONE="020 1234 5678"
GARAGE_EMAIL="info@yourgarage.co.uk"
```

Save the file.

### 5️⃣ Install Dependencies
Install PHP packages:
```bash
composer install
```

Install Node packages (this takes a few minutes):
```bash
npm install
```

### 6️⃣ Setup Database
Run migrations to create tables:
```bash
php artisan migrate
```

Seed sample services:
```bash
php artisan db:seed --class=ServiceSeeder
```

### 7️⃣ Build Frontend
Compile CSS and JavaScript:
```bash
npm run build
```

### 8️⃣ Start Server
```bash
php artisan serve
```

You should see:
```
Starting Laravel development server: http://127.0.0.1:8000
```

### 9️⃣ Open Browser
Visit: **http://localhost:8000**

**✅ Success! Your garage system is running.**

---

## 🎨 First Login & Exploration

### What You'll See
1. **Dashboard** - Overview with statistics and quick actions
2. **Sidebar Menu** - Navigation to all features

### Try These Actions First

#### 1. Create a Customer
- Click **Customers** in sidebar
- Click **New Customer** button
- Fill in customer details
- Try both Individual and Business types

#### 2. Add a Vehicle
- Click **Vehicles** in sidebar
- Click **Add Vehicle** button
- Select the customer you just created
- Try the **DVLA Lookup** feature:
  - Enter a UK registration (e.g., `AB12CDE`)
  - Click **Lookup**
  - Watch it auto-populate vehicle details (requires API key)

#### 3. Book an Appointment
- Click **Appointments** in sidebar
- Click **New Appointment**
- Select customer
- Select their vehicle
- Choose date and time
- Check availability

#### 4. Create a Job Card
- Click **Job Cards** in sidebar
- Click **New Job Card**
- Create a service record
- Add services and parts
- Track job progress

#### 5. Generate an Invoice
- From a completed job card
- Click **Create Invoice**
- Review invoice details
- Mark as sent or paid

---

## 🔑 Getting API Keys (Optional but Recommended)

### DVLA API (Vehicle Lookup)
**Purpose:** Automatically fetch vehicle details from UK registration

1. Visit: https://www.gov.uk/guidance/use-the-dvla-vehicle-enquiry-api
2. Register for an API key
3. Add to `.env`:
   ```env
   DVLA_API_KEY=your_key_here
   ```

**Features Unlocked:**
- Auto-populate make, model, color
- Get MOT due date
- Get tax due date
- Fetch engine size and fuel type

### DVSA API (MOT History)
**Purpose:** Get complete MOT test history

1. Visit: https://documentation.history.mot.api.gov.uk/
2. Request API access
3. Add to `.env`:
   ```env
   DVSA_API_KEY=your_key_here
   ```

**Features Unlocked:**
- View MOT test history
- See pass/fail results
- Check advisories and failures
- Track mileage history

### TecDoc API (Parts Catalog)
**Purpose:** Access comprehensive parts database

1. Contact TecDoc provider: https://www.tecdoc.net/
2. Request API credentials
3. Add to `.env`:
   ```env
   TECDOC_API_KEY=your_key_here
   TECDOC_PROVIDER_ID=your_id_here
   ```

**Features Unlocked:**
- Search parts by vehicle
- Get part numbers
- Check part compatibility
- Access service schedules

---

## ⚙️ Common Issues & Solutions

### Issue: "No application encryption key"
**Solution:**
```bash
php artisan key:generate
```

### Issue: Database connection refused
**Check:**
1. Is MySQL running in XAMPP?
2. Is database name correct in `.env`?
3. Is DB_PASSWORD empty if you haven't set one?

**Test connection:**
```bash
mysql -u root -p
```
(Press Enter when asked for password if none set)

### Issue: "Class not found"
**Solution:**
```bash
composer dump-autoload
```

### Issue: NPM errors during install
**Solution:**
```bash
rm -rf node_modules
rm package-lock.json
npm install
```

### Issue: Page not styled (no CSS)
**Solution:**
```bash
npm run build
```

### Issue: "Access denied for user 'root'@'localhost'"
**Solution:**
1. Check your MySQL password
2. In XAMPP, MySQL default password is empty
3. Update `.env` with correct password

---

## 📱 Testing the System

### Quick Test Checklist

Create this test scenario:

1. **Customer**
   - Name: John Smith
   - Email: john@example.com
   - Phone: 07700 900000

2. **Vehicle**
   - Registration: AB12 CDE
   - Make: Ford
   - Model: Focus
   - Year: 2020

3. **Appointment**
   - Date: Tomorrow
   - Time: 10:00 AM
   - Service: Interim Service

4. **Job Card**
   - Add service: Interim Service (£89.99)
   - Add part: Oil Filter (£12.99)
   - Complete the job

5. **Invoice**
   - Generate from job card
   - Check VAT calculation
   - Mark as paid

---

## 🎯 Next Steps

Once everything is working:

### 1. Customize Branding
- Update `GARAGE_NAME` in `.env`
- Change logo in `resources/views/layouts/app.blade.php`
- Customize colors in `tailwind.config.js`

### 2. Configure Email
- Set up SMTP in `.env`
- Test with Mailtrap.io first
- Configure for production (Gmail/Office365)

### 3. Add Real Data
- Import your existing customers
- Add your vehicles
- Set up your services and pricing
- Configure parts inventory

### 4. Set Up Backups
- Configure database backups
- Set up file system backups
- Test restore procedures

### 5. Go Live
- Set `APP_ENV=production` in `.env`
- Set `APP_DEBUG=false`
- Set up SSL certificate (HTTPS)
- Configure proper server (Apache/Nginx)
- Set up domain name

---

## 📚 Learning Resources

### Documentation Files
- **GETTING_STARTED.md** - Detailed installation guide
- **QUICK_REFERENCE.md** - Commands and URLs
- **ENV_CONFIGURATION_GUIDE.md** - Environment variables
- **GARAGE_SYSTEM_README.md** - Feature documentation
- **FEATURE_UPDATE.md** - Latest features

### External Resources
- Laravel Docs: https://laravel.com/docs
- Tailwind CSS: https://tailwindcss.com/docs
- Alpine.js: https://alpinejs.dev/
- DVLA API: https://www.gov.uk/guidance/use-the-dvla-vehicle-enquiry-api

---

## 🆘 Getting Help

If you encounter issues:

1. Check error logs: `storage/logs/laravel.log`
2. Review `.env` configuration
3. Verify database connection
4. Check file permissions
5. Clear cache: `php artisan cache:clear`
6. Restart server

---

## ✅ Success Indicators

You know everything is working when:

- ✅ Dashboard loads with no errors
- ✅ All menu items are accessible
- ✅ You can create customers
- ✅ You can add vehicles
- ✅ Forms submit successfully
- ✅ Database queries work
- ✅ Styling looks correct
- ✅ No console errors in browser

---

## 🎊 Congratulations!

Your **UK Garage Management System** is now ready to manage:

- 👥 Unlimited customers
- 🚗 Unlimited vehicles
- 📅 Appointments & scheduling
- 🔧 Job cards & services
- 💰 Invoices & payments
- 📊 Business analytics

**Welcome to professional garage management! 🚀**

---

*Last Updated: January 27, 2026*
*System Version: 1.0.0*
