# ⚡ QUICK REFERENCE CARD

## 🚀 INSTANT SETUP (5 Minutes)

```bash
cd temp-laravel
setup.bat              # Windows automated install
# OR manually:
composer install && npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ServiceSeeder
npm run build
php artisan serve
```

Visit: **http://localhost:8000**

---

## 📋 ESSENTIAL COMMANDS

| Task | Command |
|------|---------|
| Start Server | `php artisan serve` |
| Run Migrations | `php artisan migrate` |
| Seed Services | `php artisan db:seed --class=ServiceSeeder` |
| Build Assets | `npm run build` |
| Watch Assets | `npm run dev` |
| Clear Cache | `php artisan cache:clear` |
| Generate Key | `php artisan key:generate` |

---

## 🔑 ENVIRONMENT SETUP (.env)

### Database
```env
DB_DATABASE=garage_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### API Keys (Optional)
```env
DVLA_API_KEY=your_dvla_key
DVSA_API_KEY=your_dvsa_key
TECDOC_API_KEY=your_tecdoc_key
TECDOC_PROVIDER_ID=your_provider_id
```

---

## 🌐 MAIN URLS

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/` | Main overview |
| Customers | `/customers` | Customer management |
| Vehicles | `/vehicles` | Vehicle database |
| Appointments | `/appointments` | Booking system |
| Job Cards | `/job-cards` | Workshop management |
| Invoices | `/invoices` | Billing system |

---

## 📊 DATABASE TABLES

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| customers | Customer records | email, phone, postcode |
| vehicles | Vehicle database | registration, make, model |
| appointments | Bookings | scheduled_date, status |
| job_cards | Workshop jobs | job_number, status |
| services | Service catalog | name, price, duration |
| parts | Parts inventory | part_number, stock |
| invoices | Billing | invoice_number, total |
| mot_tests | MOT records | test_date, result |

---

## 🎨 STATUS COLORS

| Status | Color | CSS Class |
|--------|-------|-----------|
| Scheduled | Blue | `bg-blue-100 text-blue-700` |
| Confirmed | Green | `bg-green-100 text-green-700` |
| In Progress | Yellow | `bg-yellow-100 text-yellow-700` |
| Completed | Gray | `bg-gray-100 text-gray-700` |
| Overdue | Red | `bg-red-100 text-red-700` |

---

## 🔧 KEY FEATURES CHECKLIST

- [x] Customer Management (CRUD)
- [x] Vehicle Management (DVLA lookup)
- [x] Appointment Scheduling (Calendar)
- [x] Job Card System (Workshop)
- [x] Invoice Generation (PDF ready)
- [x] MOT Integration (DVSA)
- [x] Parts Inventory (Stock management)
- [x] Service Catalog (18 pre-configured)
- [x] Dashboard (Real-time stats)
- [x] Premium UI (Tailwind CSS)
- [x] Mobile Responsive
- [x] API Integrations (DVLA, DVSA, TecDoc)

---

## 📱 API ENDPOINTS

### Vehicles
```php
POST /vehicles/lookup-dvla              // DVLA lookup
POST /vehicles/{vehicle}/refresh-dvla   // Refresh vehicle data
```

### Appointments
```php
GET /appointments/customer/{customer}/vehicles  // Get customer vehicles
POST /appointments/check-availability           // Check time slot
PATCH /appointments/{appointment}/status        // Update status
```

### Job Cards
```php
POST /job-cards/{jobCard}/services     // Add service
POST /job-cards/{jobCard}/parts        // Add part
POST /job-cards/{jobCard}/complete     // Mark complete
```

### Invoices
```php
POST /invoices/from-job-card/{jobCard}  // Create from job card
PATCH /invoices/{invoice}/status        // Update status
POST /invoices/{invoice}/payment        // Record payment
GET /invoices/{invoice}/pdf             // Download PDF
```

---

## 🎯 WORKFLOW QUICK GUIDE

### 1. Book Appointment
Customer → Vehicle → Date/Time → Technician → Save

### 2. Create Job Card
Appointment/Manual → Customer Complaint → Work Required → Save

### 3. Add Work
Job Card → Add Services → Add Parts → Update Status

### 4. Generate Invoice
Job Card → Create Invoice → Review → Send/Print

### 5. Record Payment
Invoice → Record Payment → Method → Amount → Save

---

## 📞 QUICK TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| Can't connect to database | Check `.env` DB settings |
| DVLA lookup not working | Add `DVLA_API_KEY` to `.env` |
| Assets not loading | Run `npm run build` |
| Blank page | Check `storage/logs/laravel.log` |
| Migration error | Run `php artisan migrate:fresh` |
| Permission error | Check folder permissions |

---

## 📚 FILE LOCATIONS

```
Controllers:     app/Http/Controllers/
Models:          app/Models/
Services:        app/Services/
Views:           resources/views/
Migrations:      database/migrations/
Routes:          routes/web.php
Config:          config/services.php
Assets:          resources/css/app.css
```

---

## 🔐 SECURITY CHECKLIST

- [x] CSRF Protection (All forms)
- [x] SQL Injection Prevention (Eloquent ORM)
- [x] XSS Protection (Blade templating)
- [x] Password Hashing (Bcrypt)
- [x] Environment Variables (.env)
- [x] Soft Deletes (Data retention)
- [ ] SSL Certificate (Production)
- [ ] Rate Limiting (Production)
- [ ] Backup System (Production)

---

## 🚀 PRODUCTION DEPLOYMENT

```bash
# 1. Environment
APP_ENV=production
APP_DEBUG=false

# 2. Optimize
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Security
# - Enable SSL
# - Set up firewall
# - Configure backups
# - Set up monitoring
```

---

## 📊 DASHBOARD METRICS

| Metric | Description |
|--------|-------------|
| Total Customers | All active customers |
| Appointments Today | Today's bookings |
| Active Jobs | Open + In Progress |
| Monthly Revenue | Current month paid invoices |
| MOT Due Soon | Due in next 30 days |
| Pending Invoices | Sent + Partially Paid |
| Overdue Invoices | Past due date |

---

## 🎨 UI COMPONENTS

### Buttons
```html
<!-- Primary -->
<button class="bg-blue-600 text-white px-6 py-3 rounded-lg">

<!-- Secondary -->
<button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg">

<!-- Success -->
<button class="bg-green-600 text-white px-4 py-2 rounded-lg">

<!-- Danger -->
<button class="bg-red-600 text-white px-4 py-2 rounded-lg">
```

### Badges
```html
<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">
```

### Cards
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
```

---

## 🔄 GIT WORKFLOW (Optional)

```bash
git init
git add .
git commit -m "Initial commit - Garage Management System"
git branch -M main
git remote add origin <your-repo>
git push -u origin main
```

---

## 📈 PERFORMANCE TIPS

1. **Enable Caching**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Database Indexing**
   - Already indexed: registration_number, job_number, invoice_number

3. **Optimize Images**
   - Use WebP format
   - Compress uploads

4. **CDN for Assets**
   - Consider for production

---

## 🎯 SUPPORT RESOURCES

- **Laravel Docs**: https://laravel.com/docs
- **Tailwind Docs**: https://tailwindcss.com/docs
- **DVLA API**: https://developer-portal.driver-vehicle-licensing.api.gov.uk/
- **DVSA API**: https://documentation.history.mot.api.gov.uk/

---

## ⭐ FEATURE HIGHLIGHTS

### Automation
- ✅ Auto-generate job numbers
- ✅ Auto-generate invoice numbers
- ✅ Auto-populate vehicle data (DVLA)
- ✅ Auto-calculate VAT
- ✅ Auto-update vehicle mileage

### Integrations
- ✅ DVLA (Vehicle lookup)
- ✅ DVSA (MOT history)
- ✅ TecDoc (Parts catalog)
- 🔄 Email (Ready to configure)
- 🔄 SMS (Ready to configure)

### User Experience
- ✅ Mobile responsive
- ✅ Real-time validation
- ✅ Auto-save ready
- ✅ Search & filter
- ✅ Pagination
- ✅ Color-coded status

---

## 💡 PRO TIPS

1. **Start Simple**: Begin with appointments and job cards
2. **Import Data**: Use CSV for bulk customer/vehicle import (future)
3. **Train Staff**: One module at a time
4. **Backup Daily**: Automate database backups
5. **Monitor APIs**: Check rate limits on DVLA/DVSA
6. **Customer Data**: Keep it updated and accurate
7. **Invoice Promptly**: Generate invoices immediately after work

---

## 🎉 YOU'RE READY!

**Everything is built and ready to use!**

Start the server: `php artisan serve`
Open browser: http://localhost:8000
Login and start managing your garage! 🚗

---

**Built with Laravel 11 + Tailwind CSS 4 for UK Garages** 🇬🇧
